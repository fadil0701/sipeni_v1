<?php

namespace App\Http\Controllers\Transaction;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Enums\PermintaanBarangStatus;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\PermintaanPemeliharaan;
use App\Models\Role;
use App\Services\ApprovalPermintaanService;
use App\Services\ApprovalService;
use App\Services\PengadaanService;
use App\Services\PermintaanBarangStatusService;
use App\Support\PermintaanBarangStock;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApprovalPermintaanController extends Controller
{
    public function __construct(
        private readonly PermintaanBarangStatusService $permintaanBarangStatus,
        private readonly ApprovalService $approvalService,
        private readonly PengadaanService $pengadaanService,
        private readonly ApprovalPermintaanService $approvalPermintaanService
    ) {}

    /**
     * Menampilkan daftar approval yang perlu diproses oleh user saat ini
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $viewType = $request->query('view_type', 'aktif');
        if (! in_array($viewType, ['aktif', 'riwayat'], true)) {
            $viewType = 'aktif';
        }

        // Pastikan setiap permintaan berstatus diajukan memiliki log approval awal (step 2).
        // Ini menangani data lama yang status-nya sudah diajukan tetapi belum punya approval_log.
        $this->syncInitialApprovalLogsForSubmittedRequests();
        $this->syncInitialApprovalLogsForSubmittedPemeliharaan();

        // Pastikan roles ter-load
        if (! $user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $userRoles = $user->roles->pluck('id')->toArray();

        // Ambil flow definition yang sesuai dengan role user saat ini (barang + pemeliharaan)
        $flowDefinitions = ApprovalFlowDefinition::whereIn('modul_approval', ['PERMINTAAN_BARANG', 'PERMINTAAN_PEMELIHARAAN'])
            ->whereIn('role_id', $userRoles)
            ->pluck('id');

        // Aktif: masih dalam alur approval. Riwayat: sudah selesai/ditolak/diproses disposisi.
        $logStatuses = $viewType === 'riwayat'
            ? ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DIDISPOSISIKAN', 'DISETUJUI', 'DIPROSES', 'SELESAI', 'DITOLAK']
            : ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DIDISPOSISIKAN'];

        // Ambil approval log yang menunggu persetujuan
        // Jika user adalah admin, tampilkan semua approval log
        // Jika tidak, tampilkan hanya yang sesuai dengan role user
        if (UserScope::canViewCrossUnitData($user)) {
            $query = ApprovalLog::with(['approvalFlow.role', 'user', 'permintaan'])
                ->whereIn('modul_approval', ['PERMINTAAN_BARANG', 'PERMINTAAN_PEMELIHARAAN'])
                ->whereIn('status', $logStatuses);
        } else {
            // Ambil approval log yang menunggu persetujuan untuk role user saat ini
            // Gunakan whereIn untuk id_approval_flow yang sesuai dengan role user
            if ($flowDefinitions->isEmpty()) {
                // Jika tidak ada flow definition yang sesuai, tidak tampilkan apa-apa
                $query = ApprovalLog::with(['approvalFlow.role', 'user', 'permintaan'])
                    ->whereIn('modul_approval', ['PERMINTAAN_BARANG', 'PERMINTAAN_PEMELIHARAAN'])
                    ->whereRaw('1 = 0'); // Tidak tampilkan apa-apa
            } else {
                $query = ApprovalLog::with(['approvalFlow.role', 'user', 'permintaan'])
                    ->whereIn('modul_approval', ['PERMINTAAN_BARANG', 'PERMINTAAN_PEMELIHARAAN'])
                    ->whereIn('id_approval_flow', $flowDefinitions)
                    ->whereIn('status', $logStatuses);
            }
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter hanya yang menunggu
        if ($request->filled('menunggu')) {
            $query->where('status', 'MENUNGGU');
        }

        // Filter berdasarkan tanggal mulai (berdasarkan tanggal permintaan)
        if ($request->filled('tanggal_mulai')) {
            $tanggalMulai = $request->tanggal_mulai;
            $query->where(function ($q) use ($tanggalMulai) {
                $q->where(function ($qq) use ($tanggalMulai) {
                    $qq->where('modul_approval', 'PERMINTAAN_BARANG')
                        ->whereHas('permintaan', fn ($p) => $p->whereDate('tanggal_permintaan', '>=', $tanggalMulai));
                })->orWhere(function ($qq) use ($tanggalMulai) {
                    $qq->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
                        ->whereIn('id_referensi', PermintaanPemeliharaan::query()
                            ->whereDate('tanggal_permintaan', '>=', $tanggalMulai)
                            ->pluck('id_permintaan_pemeliharaan'));
                });
            });
        }

        // Filter berdasarkan tanggal akhir (berdasarkan tanggal permintaan)
        if ($request->filled('tanggal_akhir')) {
            $tanggalAkhir = $request->tanggal_akhir;
            $query->where(function ($q) use ($tanggalAkhir) {
                $q->where(function ($qq) use ($tanggalAkhir) {
                    $qq->where('modul_approval', 'PERMINTAAN_BARANG')
                        ->whereHas('permintaan', fn ($p) => $p->whereDate('tanggal_permintaan', '<=', $tanggalAkhir));
                })->orWhere(function ($qq) use ($tanggalAkhir) {
                    $qq->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
                        ->whereIn('id_referensi', PermintaanPemeliharaan::query()
                            ->whereDate('tanggal_permintaan', '<=', $tanggalAkhir)
                            ->pluck('id_permintaan_pemeliharaan'));
                });
            });
        }

        // Ambil semua approval log untuk menentukan status per permintaan
        $allApprovals = $query->with(['approvalFlow' => function ($q) {
            $q->with('role');
        }, 'permintaan'])->get();

        // Kelompokkan berdasarkan modul + id_referensi (hindari bentrok ID antar tabel)
        $permintaanGroups = [];
        foreach ($allApprovals as $approval) {
            $groupKey = $approval->modul_approval.'|'.$approval->id_referensi;
            if (! isset($permintaanGroups[$groupKey])) {
                $permintaanGroups[$groupKey] = [
                    'modul_approval' => $approval->modul_approval,
                    'permintaan_id' => $approval->id_referensi,
                    'approvals' => [],
                    'current_step' => null,
                    'current_status' => null,
                    'latest_approval' => null,
                ];
            }
            $permintaanGroups[$groupKey]['approvals'][] = $approval;

            // Tentukan approval terakhir berdasarkan created_at
            if (! $permintaanGroups[$groupKey]['latest_approval'] ||
                $approval->created_at > $permintaanGroups[$groupKey]['latest_approval']->created_at) {
                $permintaanGroups[$groupKey]['latest_approval'] = $approval;
            }
        }

        // Tentukan status dan step untuk setiap permintaan berdasarkan progress approval
        foreach ($permintaanGroups as $idReferensi => &$group) {
            // Urutkan approvals berdasarkan step_order
            usort($group['approvals'], function ($a, $b) {
                $stepA = $a->approvalFlow->step_order ?? 999;
                $stepB = $b->approvalFlow->step_order ?? 999;

                return $stepA <=> $stepB;
            });

            // Tentukan step terakhir yang sudah diselesaikan
            $lastCompletedStep = null;
            $currentStep = null;
            $currentStatus = 'MENUNGGU';
            $maxCompletedStep = 0;
            $rejectedApproval = null;

            // PRIORITAS 1: Cek apakah ada yang ditolak - jika ada, status harus DITOLAK
            foreach ($group['approvals'] as $approval) {
                if ($approval->status === 'DITOLAK') {
                    $rejectedApproval = $approval;
                    $currentStatus = 'DITOLAK';
                    $currentStep = $approval;
                    break; // Setelah ditemukan DITOLAK, langsung keluar
                }
            }

            // Jika tidak ada yang ditolak, lanjutkan pengecekan normal
            if (! $rejectedApproval) {
                // Urutkan approvals berdasarkan step_order untuk memastikan urutan yang benar
                usort($group['approvals'], function ($a, $b) {
                    $stepA = $a->approvalFlow->step_order ?? 999;
                    $stepB = $b->approvalFlow->step_order ?? 999;

                    return $stepA <=> $stepB;
                });

                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;

                    // Jika status sudah diselesaikan (bukan MENUNGGU), update last completed step
                    if (in_array($approval->status, ['DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DIDISPOSISIKAN', 'DIPROSES', 'SELESAI'], true)) {
                        if ($stepOrder > $maxCompletedStep) {
                            $maxCompletedStep = $stepOrder;
                            $lastCompletedStep = $stepOrder;
                        }
                    }
                }

                // Pemeliharaan: tampilkan step MENUNGGU tertinggi apa adanya (jangan remap ke DIDISPOSISIKAN).
                // Remap lama untuk step 4 membuat disposisi Pengurus masuk tab Riwayat.
                if (($group['modul_approval'] ?? '') === 'PERMINTAAN_PEMELIHARAAN') {
                    $pendingPemeliharaan = null;
                    foreach ($group['approvals'] as $approval) {
                        if ($approval->status === 'MENUNGGU') {
                            $pendingPemeliharaan = $approval;
                        }
                    }
                    if ($pendingPemeliharaan) {
                        $currentStep = $pendingPemeliharaan;
                        $currentStatus = 'MENUNGGU';
                    } else {
                        $currentStep = $group['latest_approval'];
                        $currentStatus = $currentStep?->status ?? 'MENUNGGU';
                    }
                } else {
                // Cari current step berdasarkan urutan step_order (prioritas step yang lebih tinggi)
                // Step 4 (disposisi) harus ditampilkan sebagai DIDISPOSISIKAN meskipun status approval lognya MENUNGGU

                // Cek apakah step 3 sudah diverifikasi
                $step3Verified = false;
                $step3Approval = null;
                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;
                    if ($stepOrder == 3 && $approval->status === 'DIVERIFIKASI') {
                        $step3Verified = true;
                        $step3Approval = $approval;
                        break;
                    }
                }

                // Cek apakah ada step 4 (disposisi / kepala pusat / pengadaan)
                $step4Approval = null;
                $kepalaPusatPending = null;
                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;
                    if ($stepOrder != 4) {
                        continue;
                    }
                    $roleName = $approval->approvalFlow?->role?->name;
                    if ($roleName === 'kepala_pusat' && $approval->status === 'MENUNGGU') {
                        $kepalaPusatPending = $approval;
                    }
                    if (! $step4Approval) {
                        $step4Approval = $approval;
                    }
                }

                // Prioritas 0: Menunggu persetujuan Kepala Pusat (jalur pengadaan)
                if ($kepalaPusatPending) {
                    $currentStep = $kepalaPusatPending;
                    $currentStatus = 'MENUNGGU';
                }
                // Prioritas 1: Cek step 4 (disposisi) dulu - ini adalah step terpenting untuk ditampilkan
                elseif ($step4Approval) {
                    if ($step4Approval->status === 'MENUNGGU') {
                        $currentStep = $step4Approval;
                        // Jika step 3 sudah diverifikasi, status = DISETUJUI (karena sudah diverifikasi dan didisposisikan)
                        // Jika step 3 belum diverifikasi, status = DIDISPOSISIKAN
                        $currentStatus = $step3Verified ? 'DISETUJUI' : 'DIDISPOSISIKAN';
                    } elseif ($step4Approval->status === 'DIPROSES') {
                        $currentStep = $step4Approval;
                        $currentStatus = 'DIPROSES';
                    } elseif ($step4Approval->status === 'SELESAI') {
                        $currentStep = $step4Approval;
                        $currentStatus = 'SELESAI';
                    }
                }

                // Prioritas 2: Jika belum ada step 4, cek step 3 (verifikasi Kasubbag TU)
                if (! $currentStep) {
                    foreach ($group['approvals'] as $approval) {
                        $stepOrder = $approval->approvalFlow->step_order ?? 999;
                        if ($stepOrder == 3) {
                            if ($approval->status === 'MENUNGGU') {
                                $currentStep = $approval;
                                $currentStatus = 'MENUNGGU'; // Masih menunggu verifikasi
                            } elseif ($approval->status === 'DIVERIFIKASI') {
                                // Step 3 sudah diverifikasi -> status DISETUJUI
                                $currentStep = $approval;
                                $currentStatus = 'DISETUJUI'; // Status ditampilkan sebagai DISETUJUI karena sudah diverifikasi
                            }
                            break;
                        }
                    }
                }

                // Prioritas 3: Jika belum ada step 3, cek step 2 (mengetahui Kepala Unit)
                if (! $currentStep) {
                    foreach ($group['approvals'] as $approval) {
                        $stepOrder = $approval->approvalFlow->step_order ?? 999;
                        if ($stepOrder == 2 && $approval->status === 'MENUNGGU') {
                            $currentStep = $approval;
                            $currentStatus = 'MENUNGGU'; // Masih menunggu diketahui
                            break;
                        }
                    }
                }

                // Jika masih belum ada yang menunggu, gunakan approval terakhir
                if (! $currentStep) {
                    $currentStep = $group['latest_approval'];
                    if ($currentStep) {
                        // Jika approval terakhir adalah DIVERIFIKASI dan sudah ada step 4, status = DISETUJUI
                        if ($currentStep->status === 'DIVERIFIKASI' && $step4Approval) {
                            $currentStatus = 'DISETUJUI';
                            $currentStep = $step4Approval;
                        } else {
                            $currentStatus = $currentStep->status;
                        }
                    }
                }
                } // end PERMINTAAN_BARANG branch
            } else {
                // Jika ada yang ditolak, tetap hitung last completed step untuk display
                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;
                    if (in_array($approval->status, ['DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DIDISPOSISIKAN', 'DIPROSES', 'SELESAI'], true)) {
                        if ($stepOrder > $maxCompletedStep) {
                            $maxCompletedStep = $stepOrder;
                            $lastCompletedStep = $stepOrder;
                        }
                    }
                }
            }

            $group['current_step'] = $currentStep;
            $group['current_status'] = $currentStatus;
            $group['last_completed_step'] = $lastCompletedStep;
        }

        // Ambil data permintaan untuk setiap group
        $barangIds = collect($permintaanGroups)
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->pluck('permintaan_id')
            ->unique()
            ->values();
        $pemeliharaanIds = collect($permintaanGroups)
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->pluck('permintaan_id')
            ->unique()
            ->values();

        $permintaansBarang = PermintaanBarang::with([
            'unitKerja.gudang',
            'pemohon.jabatan',
            'detailPermintaan.dataBarang',
        ])
            ->whereIn('id_permintaan', $barangIds)
            ->get()
            ->keyBy('id_permintaan');

        $permintaansPemeliharaan = PermintaanPemeliharaan::with([
            'unitKerja',
            'pemohon',
            'registerAset.inventory.dataBarang',
        ])
            ->whereIn('id_permintaan_pemeliharaan', $pemeliharaanIds)
            ->get()
            ->keyBy('id_permintaan_pemeliharaan');

        // Convert ke collection untuk pagination
        $permintaanList = collect($permintaanGroups)->map(function ($group) use ($permintaansBarang, $permintaansPemeliharaan) {
            $modul = $group['modul_approval'] ?? 'PERMINTAAN_BARANG';
            $permintaan = $modul === 'PERMINTAAN_PEMELIHARAAN'
                ? ($permintaansPemeliharaan[$group['permintaan_id']] ?? null)
                : ($permintaansBarang[$group['permintaan_id']] ?? null);

            return [
                'modul_approval' => $modul,
                'permintaan' => $permintaan,
                'current_step' => $group['current_step'],
                'current_status' => $group['current_status'],
                'last_completed_step' => $group['last_completed_step'],
                'approvals' => $group['approvals'],
            ];
        })->filter(function ($item) {
            return $item['permintaan'] !== null;
        });

        // Tab Aktif vs Riwayat berdasarkan status tampilan approval.
        $riwayatDisplayStatuses = ['DISETUJUI', 'DIDISPOSISIKAN', 'DIPROSES', 'SELESAI', 'DITOLAK'];
        $permintaanList = $permintaanList->filter(function ($item) use ($viewType, $riwayatDisplayStatuses) {
            $isRiwayat = in_array($item['current_status'], $riwayatDisplayStatuses, true);

            return $viewType === 'riwayat' ? $isRiwayat : ! $isRiwayat;
        })->values();

        // Pagination manual
        $page = $request->get('page', 1);
        $perPage = PaginationHelper::getPerPage($request, 10);
        $total = $permintaanList->count();
        $items = $permintaanList->slice(($page - 1) * $perPage, $perPage)->values();

        // Buat paginator manual
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('transaction.approval.index', compact('paginator', 'viewType'));
    }

    private function syncInitialApprovalLogsForSubmittedPemeliharaan(): void
    {
        $flowStep2 = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->where('step_order', 2)
            ->whereNotNull('role_id')
            ->first();

        if (! $flowStep2) {
            return;
        }

        $submittedIds = PermintaanPemeliharaan::query()
            ->where('status_permintaan', 'DIAJUKAN')
            ->pluck('id_permintaan_pemeliharaan');

        if ($submittedIds->isEmpty()) {
            return;
        }

        $existingIds = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->where('id_approval_flow', $flowStep2->id)
            ->whereIn('id_referensi', $submittedIds)
            ->pluck('id_referensi')
            ->all();

        $missingIds = $submittedIds
            ->reject(fn ($id) => in_array($id, $existingIds, true))
            ->values();

        if ($missingIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $missingIds->map(fn ($idPermintaan) => [
            'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
            'id_referensi' => $idPermintaan,
            'id_approval_flow' => $flowStep2->id,
            'user_id' => null,
            'role_id' => $flowStep2->role_id,
            'status' => 'MENUNGGU',
            'catatan' => null,
            'approved_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        ApprovalLog::query()->insert($rows);
    }

    private function syncInitialApprovalLogsForSubmittedRequests(): void
    {
        $flowStep2 = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 2)
            ->first();

        if (! $flowStep2) {
            return;
        }

        $submittedIds = PermintaanBarang::query()
            ->whereIn('status', [
                PermintaanBarangStatus::Diajukan->value,
                'DIAJUKAN',
            ])
            ->pluck('id_permintaan');

        if ($submittedIds->isEmpty()) {
            return;
        }

        $existingIds = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_approval_flow', $flowStep2->id)
            ->whereIn('id_referensi', $submittedIds)
            ->pluck('id_referensi')
            ->all();

        $missingIds = $submittedIds
            ->reject(fn ($id) => in_array($id, $existingIds, true))
            ->values();

        if ($missingIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $missingIds->map(fn ($idPermintaan) => [
            'modul_approval' => 'PERMINTAAN_BARANG',
            'id_referensi' => $idPermintaan,
            'id_approval_flow' => $flowStep2->id,
            'user_id' => null,
            'role_id' => $flowStep2->role_id,
            'status' => 'MENUNGGU',
            'catatan' => null,
            'approved_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        ApprovalLog::query()->insert($rows);
    }

    /**
     * Menampilkan detail approval
     */
    public function show($id)
    {
        $approval = ApprovalLog::with(['approvalFlow.role', 'user', 'role'])
            ->findOrFail($id);

        // Pastikan user yang login memiliki hak akses untuk approval ini
        $user = Auth::user();

        // Pastikan roles ter-load
        if (! $user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $userRoles = $user->roles->pluck('id')->toArray();
        $modul = $approval->modul_approval;

        // Admin bisa melihat semua approval
        if (! UserScope::canViewCrossUnitData($user)) {
            $allowedFlowIds = ApprovalFlowDefinition::where('modul_approval', $modul)
                ->whereIn('role_id', $userRoles)
                ->pluck('id')
                ->toArray();

            if (! in_array($approval->id_approval_flow, $allowedFlowIds)) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat approval ini.');
            }
        }

        if ($modul === 'PERMINTAAN_PEMELIHARAAN') {
            $permintaan = PermintaanPemeliharaan::with([
                'unitKerja',
                'pemohon',
                'pegawaiPelaksana',
                'registerAset.inventory.dataBarang',
                'serviceReport',
            ])->find($approval->id_referensi);

            if (! $permintaan) {
                abort(404, 'Permintaan pemeliharaan tidak ditemukan.');
            }

            $approvalHistory = $this->approvalService->history('PERMINTAAN_PEMELIHARAAN', (int) $approval->id_referensi);
            $rejectedApproval = ApprovalLog::where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
                ->where('id_referensi', $approval->id_referensi)
                ->where('status', 'DITOLAK')
                ->first();
            $displayStatus = $rejectedApproval ? 'DITOLAK' : $approval->status;
            $currentFlow = $approval->approvalFlow;
            $nextFlow = $currentFlow ? $currentFlow->getNextStep() : null;
            $pegawaiPelaksanaOptions = \App\Models\MasterPegawai::query()
                ->orderBy('nama_pegawai')
                ->limit(500)
                ->get(['id', 'nama_pegawai']);

            return view('transaction.approval.show-pemeliharaan', compact(
                'approval',
                'permintaan',
                'approvalHistory',
                'currentFlow',
                'nextFlow',
                'displayStatus',
                'rejectedApproval',
                'pegawaiPelaksanaOptions'
            ));
        }

        // Load permintaan barang
        $permintaan = PermintaanBarang::with([
            'unitKerja',
            'pemohon.jabatan',
            'detailPermintaan.dataBarang',
            'detailPermintaan.satuan',
        ])->find($approval->id_referensi);

        if (! $permintaan) {
            abort(404, 'Permintaan tidak ditemukan.');
        }

        $stockData = PermintaanBarangStock::stockDataForDetails($permintaan);

        // Load approval history from centralized service
        $approvalHistory = $this->approvalService->history('PERMINTAAN_BARANG', (int) $approval->id_referensi);

        // Cek apakah ada approval yang ditolak untuk permintaan ini
        $rejectedApproval = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $approval->id_referensi)
            ->where('status', 'DITOLAK')
            ->first();

        // Jika ada yang ditolak, gunakan status DITOLAK untuk display
        $displayStatus = $rejectedApproval ? 'DITOLAK' : $approval->status;

        // Load current flow definition
        $currentFlow = $approval->approvalFlow;
        $nextFlow = $currentFlow ? $currentFlow->getNextStep() : null;

        // Cek apakah step 3 (Kasubbag TU) sudah diverifikasi untuk menentukan apakah bisa disposisi
        $step3Verified = false;
        $step3Flow = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 3)
            ->first();
        if ($step3Flow) {
            $step3Log = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('id_referensi', $approval->id_referensi)
                ->where('id_approval_flow', $step3Flow->id)
                ->first();
            $step3Verified = $step3Log && $step3Log->status === 'DIVERIFIKASI';
        }

        return view('transaction.approval.show', compact('approval', 'permintaan', 'approvalHistory', 'currentFlow', 'nextFlow', 'step3Verified', 'displayStatus', 'rejectedApproval', 'stockData'));
    }

    /**
     * Kepala Unit - Mengetahui permintaan
     */
    public function mengetahui(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'mengetahui', [
            'catatan' => 'nullable|string',
        ], 'Permintaan telah diketahui.');
    }

    /**
     * Kasubbag TU - Verifikasi permintaan
     */
    public function verifikasi(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'verifikasi', [
            'catatan' => 'nullable|string',
            'koreksi_qty' => 'nullable|array',
            'koreksi_qty.*' => 'nullable|numeric|min:0.01',
        ], 'Permintaan telah diverifikasi. Jika stok tersedia diteruskan ke disposisi gudang; jika stok kosong menunggu persetujuan Kepala Pusat.');
    }

    /**
     * Kasubbag TU - Kembalikan permintaan
     */
    public function kembalikan(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'kembalikan', [
            'catatan' => 'required|string|min:10',
        ], 'Permintaan telah dikembalikan.', 'transaction.approval.index', [
            'catatan.required' => 'Catatan pengembalian wajib diisi.',
            'catatan.min' => 'Catatan pengembalian minimal 10 karakter.',
        ]);
    }

    /**
     * Kepala Pusat - Approve permintaan
     */
    public function approve(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'approve', [
            'catatan' => 'nullable|string',
        ], 'Permintaan berhasil disetujui.');
    }

    /**
     * Kepala Pusat - Reject permintaan
     */
    public function reject(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'reject', [
            'catatan' => 'required|string|min:10',
        ], 'Permintaan ditolak.', 'transaction.approval.index', [
            'catatan.required' => 'Catatan penolakan wajib diisi.',
            'catatan.min' => 'Catatan penolakan minimal 10 karakter.',
        ]);
    }

    /**
     * Admin Gudang/Pengurus Barang - Disposisi ke admin gudang kategori sesuai item permintaan
     */
    public function disposisi(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'disposisi', [], 'Permintaan telah didisposisikan.');
    }

    public function disposisiPemeliharaan(Request $request, $id)
    {
        return $this->handleAction($request, (int) $id, 'disposisi_pemeliharaan', [
            'jenis_pelaksana' => 'required|in:TEKNISI_ATEM,TEKNISI_IT,KONTRAK_SERVICE,VENDOR',
            'id_pegawai_pelaksana' => 'nullable|exists:master_pegawai,id',
            'nama_vendor' => 'nullable|string|max:255',
            'disposisi_catatan' => 'nullable|string',
        ], 'Permintaan pemeliharaan telah didisposisikan ke pelaksana.');
    }

    private function handleAction(
        Request $request,
        int $id,
        string $action,
        array $rules = [],
        string $successMessage = 'Aksi berhasil diproses.',
        string $successRoute = 'transaction.approval.show',
        array $messages = []
    ) {
        $validated = ! empty($rules) ? $request->validate($rules, $messages) : [];
        $user = Auth::user();

        try {
            $targetId = $this->approvalPermintaanService->processAction($action, $id, $user, $validated);
            $routeId = $successRoute === 'transaction.approval.show' ? $targetId : null;

            return $routeId !== null
                ? redirect()->route($successRoute, $routeId)->with('success', $successMessage)
                : redirect()->route($successRoute)->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error("Error {$action} approval: ".$e->getMessage());

            return redirect()->route('transaction.approval.show', $id)
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
