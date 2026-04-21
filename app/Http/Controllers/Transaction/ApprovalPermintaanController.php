<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\DetailPermintaanBarang;
use App\Models\MasterPegawai;
use App\Models\Role;
use App\Models\DataInventory;
use App\Models\DataStock;
use App\Enums\PermintaanBarangStatus;
use App\Services\ApprovalService;
use App\Services\ApprovalPermintaanService;
use App\Services\PengadaanService;
use App\Services\PermintaanBarangStatusService;

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

        // Pastikan setiap permintaan berstatus diajukan memiliki log approval awal (step 2).
        // Ini menangani data lama yang status-nya sudah diajukan tetapi belum punya approval_log.
        $this->syncInitialApprovalLogsForSubmittedRequests();
        
        // Pastikan roles ter-load
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        
        $userRoles = $user->roles->pluck('id')->toArray();
        
        // Ambil flow definition yang sesuai dengan role user saat ini
        $flowDefinitions = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
            ->whereIn('role_id', $userRoles)
            ->pluck('id');
        
        // Ambil approval log yang menunggu persetujuan
        // Jika user adalah admin, tampilkan semua approval log
        // Jika tidak, tampilkan hanya yang sesuai dengan role user
        if ($user->hasRole('admin')) {
            $query = ApprovalLog::with(['approvalFlow.role', 'user', 'permintaan'])
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->whereIn('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DIDISPOSISIKAN']);
        } else {
            // Ambil approval log yang menunggu persetujuan untuk role user saat ini
            // Gunakan whereIn untuk id_approval_flow yang sesuai dengan role user
            if ($flowDefinitions->isEmpty()) {
                // Jika tidak ada flow definition yang sesuai, tidak tampilkan apa-apa
                $query = ApprovalLog::with(['approvalFlow.role', 'user', 'permintaan'])
                    ->where('modul_approval', 'PERMINTAAN_BARANG')
                    ->whereRaw('1 = 0'); // Tidak tampilkan apa-apa
            } else {
                $query = ApprovalLog::with(['approvalFlow.role', 'user', 'permintaan'])
                    ->where('modul_approval', 'PERMINTAAN_BARANG')
                    ->whereIn('id_approval_flow', $flowDefinitions)
                    ->whereIn('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DIDISPOSISIKAN']);
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
            $query->whereHas('permintaan', function($q) use ($request) {
                $q->whereDate('tanggal_permintaan', '>=', $request->tanggal_mulai);
            });
        }
        
        // Filter berdasarkan tanggal akhir (berdasarkan tanggal permintaan)
        if ($request->filled('tanggal_akhir')) {
            $query->whereHas('permintaan', function($q) use ($request) {
                $q->whereDate('tanggal_permintaan', '<=', $request->tanggal_akhir);
            });
        }
        
        // Ambil semua approval log untuk menentukan status per permintaan
        $allApprovals = $query->with(['approvalFlow' => function($q) {
            $q->with('role');
        }, 'permintaan'])->get();
        
        // Kelompokkan berdasarkan id_referensi (permintaan)
        $permintaanGroups = [];
        foreach ($allApprovals as $approval) {
            $idReferensi = $approval->id_referensi;
            if (!isset($permintaanGroups[$idReferensi])) {
                $permintaanGroups[$idReferensi] = [
                    'permintaan_id' => $idReferensi,
                    'approvals' => [],
                    'current_step' => null,
                    'current_status' => null,
                    'latest_approval' => null,
                ];
            }
            $permintaanGroups[$idReferensi]['approvals'][] = $approval;
            
            // Tentukan approval terakhir berdasarkan created_at
            if (!$permintaanGroups[$idReferensi]['latest_approval'] || 
                $approval->created_at > $permintaanGroups[$idReferensi]['latest_approval']->created_at) {
                $permintaanGroups[$idReferensi]['latest_approval'] = $approval;
            }
        }
        
        // Tentukan status dan step untuk setiap permintaan berdasarkan progress approval
        foreach ($permintaanGroups as $idReferensi => &$group) {
            // Urutkan approvals berdasarkan step_order
            usort($group['approvals'], function($a, $b) {
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
            if (!$rejectedApproval) {
                // Urutkan approvals berdasarkan step_order untuk memastikan urutan yang benar
                usort($group['approvals'], function($a, $b) {
                    $stepA = $a->approvalFlow->step_order ?? 999;
                    $stepB = $b->approvalFlow->step_order ?? 999;
                    return $stepA <=> $stepB;
                });
                
                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;
                    
                    // Jika status sudah diselesaikan (bukan MENUNGGU), update last completed step
                    if (in_array($approval->status, ['DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DIDISPOSISIKAN', 'DIPROSES'])) {
                        if ($stepOrder > $maxCompletedStep) {
                            $maxCompletedStep = $stepOrder;
                            $lastCompletedStep = $stepOrder;
                        }
                    }
                }
                
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
                
                // Cek apakah ada step 4 (disposisi)
                $step4Approval = null;
                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;
                    if ($stepOrder == 4) {
                        $step4Approval = $approval;
                        break;
                    }
                }
                
                // Prioritas 1: Cek step 4 (disposisi) dulu - ini adalah step terpenting untuk ditampilkan
                if ($step4Approval) {
                    if ($step4Approval->status === 'MENUNGGU') {
                        $currentStep = $step4Approval;
                        // Jika step 3 sudah diverifikasi, status = DISETUJUI (karena sudah diverifikasi dan didisposisikan)
                        // Jika step 3 belum diverifikasi, status = DIDISPOSISIKAN
                        $currentStatus = $step3Verified ? 'DISETUJUI' : 'DIDISPOSISIKAN';
                    } elseif ($step4Approval->status === 'DIPROSES') {
                        $currentStep = $step4Approval;
                        $currentStatus = 'DIPROSES';
                    }
                }
                
                // Prioritas 2: Jika belum ada step 4, cek step 3 (verifikasi Kasubbag TU)
                if (!$currentStep) {
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
                if (!$currentStep) {
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
                if (!$currentStep) {
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
            } else {
                // Jika ada yang ditolak, tetap hitung last completed step untuk display
                foreach ($group['approvals'] as $approval) {
                    $stepOrder = $approval->approvalFlow->step_order ?? 999;
                    if (in_array($approval->status, ['DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DIDISPOSISIKAN', 'DIPROSES'])) {
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
        $permintaanIds = array_keys($permintaanGroups);
        $permintaans = PermintaanBarang::with([
            'unitKerja.gudang', // Load gudang unit melalui unit kerja
            'pemohon.jabatan', // Load jabatan pemohon
            'detailPermintaan.dataBarang'
        ])
            ->whereIn('id_permintaan', $permintaanIds)
            ->get()
            ->keyBy('id_permintaan');
        
        // Convert ke collection untuk pagination
        $permintaanList = collect($permintaanGroups)->map(function($group) use ($permintaans) {
            return [
                'permintaan' => $permintaans[$group['permintaan_id']] ?? null,
                'current_step' => $group['current_step'],
                'current_status' => $group['current_status'],
                'last_completed_step' => $group['last_completed_step'],
                'approvals' => $group['approvals'],
            ];
        })->filter(function($item) {
            return $item['permintaan'] !== null;
        });
        
        // Pagination manual
        $page = $request->get('page', 1);
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $total = $permintaanList->count();
        $items = $permintaanList->slice(($page - 1) * $perPage, $perPage)->values();
        
        // Buat paginator manual
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('transaction.approval.index', compact('paginator', 'permintaans'));
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
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        
        $userRoles = $user->roles->pluck('id')->toArray();
        
        // Admin bisa melihat semua approval
        if (!$user->hasRole('admin')) {
            $allowedFlowIds = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
                ->whereIn('role_id', $userRoles)
                ->pluck('id')
                ->toArray();
            
            if (!in_array($approval->id_approval_flow, $allowedFlowIds)) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat approval ini.');
            }
        }
        
        // Load permintaan
        $permintaan = PermintaanBarang::with([
            'unitKerja', 
            'pemohon.jabatan', 
            'detailPermintaan.dataBarang', 
            'detailPermintaan.satuan'
        ])->find($approval->id_referensi);
        
        if (!$permintaan) {
            abort(404, 'Permintaan tidak ditemukan.');
        }
        
        // Get stock data hanya gudang pusat (untuk detail yang dari master). Permintaan lainnya tidak punya stock.
        $stockData = [];
        foreach ($permintaan->detailPermintaan as $detail) {
            if ($detail->id_data_barang) {
                $perGudangPusat = DataStock::getStockPerGudangPusat($detail->id_data_barang);
                $stockData[$detail->id_detail_permintaan] = [
                    'total' => $perGudangPusat->sum('qty_akhir'),
                    'per_gudang' => $perGudangPusat,
                ];
            } else {
                $stockData[$detail->id_detail_permintaan] = ['total' => 0, 'per_gudang' => collect()];
            }
        }
        
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
        ], 'Permintaan telah diverifikasi, disetujui, dan didisposisikan ke Admin Gudang/Pengurus Barang.');
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

    private function handleAction(
        Request $request,
        int $id,
        string $action,
        array $rules = [],
        string $successMessage = 'Aksi berhasil diproses.',
        string $successRoute = 'transaction.approval.show',
        array $messages = []
    ) {
        $validated = !empty($rules) ? $request->validate($rules, $messages) : [];
        $user = Auth::user();

        try {
            $targetId = $this->approvalPermintaanService->processAction($action, $id, $user, $validated);
            $routeId = $successRoute === 'transaction.approval.show' ? $targetId : null;

            return $routeId !== null
                ? redirect()->route($successRoute, $routeId)->with('success', $successMessage)
                : redirect()->route($successRoute)->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error("Error {$action} approval: " . $e->getMessage());
            return redirect()->route('transaction.approval.show', $id)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}