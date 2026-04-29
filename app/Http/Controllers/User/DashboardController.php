<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\PermintaanBarang;
use App\Models\DataStock;
use App\Models\DataInventory;
use App\Models\TransaksiDistribusi;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\DraftDetailDistribusi;
use App\Models\PenerimaanBarang;
use App\Models\ReturBarang;
use App\Models\PemakaianBarang;
use App\Enums\PermintaanBarangStatus;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sessionScopeKey = 'dashboard.workspace_scope';
        $allowedScopes = ['all', 'my'];

        if ($request->has('workspace_scope') && in_array($request->query('workspace_scope'), $allowedScopes, true)) {
            $workspaceScope = $request->query('workspace_scope');
            $request->session()->put($sessionScopeKey, $workspaceScope);
        } else {
            $workspaceScope = $request->session()->get($sessionScopeKey, 'all');
            if (!in_array($workspaceScope, $allowedScopes, true)) {
                $workspaceScope = 'all';
            }
        }
        $pegawaiId = optional($user->pegawai)->id;
        $isPengurusBarangWorkspace = $user->hasAnyRole([
            'admin',
            'admin_gudang',
            'admin_gudang_aset',
            'admin_gudang_persediaan',
            'admin_gudang_farmasi',
            'admin_gudang_unit',
        ]);
        
        // Get statistics
        $totalAssets = InventoryItem::whereHas('inventory', function ($query) {
            $query->where('jenis_inventory', 'ASET');
        })->count();

        $totalAssetValue = (float) DataInventory::query()
            ->where('jenis_inventory', 'ASET')
            ->sum('total_harga');
        $totalPersediaanValue = (float) DataInventory::query()
            ->where('jenis_inventory', 'PERSEDIAAN')
            ->sum('total_harga');
        $totalFarmasiValue = (float) DataInventory::query()
            ->where('jenis_inventory', 'FARMASI')
            ->sum('total_harga');

        $activeRequests = PermintaanBarang::whereNotIn('status', [
            PermintaanBarangStatus::Draft->value,
            PermintaanBarangStatus::Ditolak->value,
            PermintaanBarangStatus::Selesai->value,
        ])
            ->count();

        // Get latest requests - sesuai ERD: permintaan_barang dengan id_pemohon
        $latestRequests = PermintaanBarang::latest('tanggal_permintaan')
            ->limit(5)
            ->with('pemohon')
            ->get();

        // Tracking permintaan -> approval (tahap saat ini)
        $trackingItems = collect();
        $trackingStepMax = (int) (ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->max('step_order') ?? 4);

        if ($latestRequests->isNotEmpty()) {
            $requestIds = $latestRequests->pluck('id_permintaan');
            $approvalLogs = ApprovalLog::query()
                ->with('approvalFlow')
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->whereIn('id_referensi', $requestIds)
                ->orderBy('id_referensi')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('id_referensi');

            $trackingItems = $latestRequests->map(function ($req) use ($approvalLogs, $trackingStepMax) {
                $logs = $approvalLogs->get($req->id_permintaan, collect());
                $current = $logs->firstWhere('status', 'MENUNGGU') ?? $logs->first();

                $requestStatus = $req->status instanceof PermintaanBarangStatus
                    ? $req->status
                    : PermintaanBarangStatus::normalizeStored((string) $req->status);

                // Jika status permintaan sudah melewati approval, tampilkan progress approval 100%.
                $isApprovalCompleted = in_array($requestStatus, [
                    PermintaanBarangStatus::Diverifikasi,
                    PermintaanBarangStatus::MenungguPengadaan,
                    PermintaanBarangStatus::ProsesPengadaan,
                    PermintaanBarangStatus::BarangTersedia,
                    PermintaanBarangStatus::ProsesDistribusi,
                    PermintaanBarangStatus::Dikirim,
                    PermintaanBarangStatus::Diterima,
                    PermintaanBarangStatus::Selesai,
                ], true);
                $isRejected = $requestStatus === PermintaanBarangStatus::Ditolak;

                if ($isApprovalCompleted || $isRejected) {
                    $currentStep = max(1, $trackingStepMax);
                    $progressPercent = 100;
                    $displayStatus = $isRejected ? 'DITOLAK' : 'DISETUJUI';
                    $displayStepName = $isRejected ? 'Ditolak' : 'Selesai Approval';
                } else {
                    $currentStep = (int) ($current?->approvalFlow?->step_order ?? 1);
                    $progressPercent = (int) round(($currentStep / max(1, $trackingStepMax)) * 100);
                    $displayStatus = $current?->status
                        ?? strtoupper((string) ($requestStatus->value ?? '-'));
                    $displayStepName = $current?->approvalFlow?->nama_step ?? 'Pengajuan';
                }

                return [
                    'no_permintaan' => $req->no_permintaan,
                    'pemohon' => $req->pemohon->nama_pegawai ?? '-',
                    'tanggal' => $req->tanggal_permintaan,
                    'status' => $displayStatus,
                    'step_name' => $displayStepName,
                    'step_order' => $currentStep,
                    'progress_percent' => max(8, min(100, $progressPercent)),
                ];
            });
        }

        // Get latest assets - sesuai ERD: inventory_item -> data_inventory -> master_data_barang
        $latestAssets = InventoryItem::whereHas('inventory', function ($query) {
            $query->where('jenis_inventory', 'ASET');
        })
        ->with(['inventory.dataBarang', 'ruangan'])
        ->latest('created_at')
        ->limit(5)
        ->get();

        // Get latest transactions - dari transaksi_distribusi dan penerimaan_barang
        $latestDistribusi = TransaksiDistribusi::latest('tanggal_distribusi')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->no_sbbk,
                    'jenis' => 'Distribusi (SBBK)',
                    'tanggal' => $item->tanggal_distribusi,
                ];
            });

        $latestPenerimaan = \App\Models\PenerimaanBarang::latest('tanggal_penerimaan')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->no_penerimaan,
                    'jenis' => 'Penerimaan',
                    'tanggal' => $item->tanggal_penerimaan,
                ];
            });

        $latestTransactions = $latestDistribusi->concat($latestPenerimaan)
            ->sortByDesc('tanggal')
            ->take(5)
            ->values();

        $inventoryCategoryData = [
            'aset' => DataInventory::query()->where('jenis_inventory', 'ASET')->count(),
            'persediaan' => DataInventory::query()->where('jenis_inventory', 'PERSEDIAAN')->count(),
            'farmasi' => DataInventory::query()->where('jenis_inventory', 'FARMASI')->count(),
        ];

        $latestDistribusiTracking = TransaksiDistribusi::query()
            ->with(['gudangTujuan'])
            ->latest('tanggal_distribusi')
            ->limit(5)
            ->get()
            ->map(function ($distribusi) {
                $status = strtolower((string) ($distribusi->status_distribusi?->value ?? $distribusi->status_distribusi ?? 'draft'));
                $progressPercent = match ($status) {
                    'selesai' => 100,
                    'dikirim' => 75,
                    'diproses' => 50,
                    default => 25,
                };

                return [
                    'no_sbbk' => $distribusi->no_sbbk,
                    'tujuan' => $distribusi->gudangTujuan->nama_gudang ?? '-',
                    'tanggal' => $distribusi->tanggal_distribusi,
                    'status' => strtoupper($status),
                    'progress_percent' => $progressPercent,
                ];
            });

        // Get request status data for chart
        $requestStatusData = [
            'diajukan' => PermintaanBarang::where('status', PermintaanBarangStatus::Diajukan->value)->count(),
            'disetujui' => PermintaanBarang::where('status', PermintaanBarangStatus::Diverifikasi->value)->count(),
            'dikirim' => PermintaanBarang::where('status', PermintaanBarangStatus::Dikirim->value)->count(),
            'ditolak' => PermintaanBarang::where('status', PermintaanBarangStatus::Ditolak->value)->count(),
        ];

        $workspaceStats = [
            'approval_perlu_diproses' => 0,
            'draft_distribusi' => 0,
            'distribusi_perlu_proses' => 0,
            'distribusi_perlu_diterima' => 0,
            'retur_diajukan' => 0,
            'pemakaian_diajukan' => 0,
        ];
        $urgentQueue = collect();
        $workspaceSla = [
            'approval_over_sla' => 0,
            'distribusi_over_sla' => 0,
            'retur_over_sla' => 0,
            'pemakaian_over_sla' => 0,
        ];

        if ($isPengurusBarangWorkspace) {
            $roleIds = $user->roles->pluck('id');

            $workspaceStats['approval_perlu_diproses'] = ApprovalLog::query()
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('status', 'MENUNGGU')
                ->whereIn('role_id', $roleIds)
                ->when($workspaceScope === 'my', function ($q) use ($user) {
                    // Untuk approval, item personal didefinisikan sebagai log yang pernah diproses user ini.
                    $q->where('user_id', $user->id);
                })
                ->count();

            $workspaceStats['draft_distribusi'] = DraftDetailDistribusi::query()
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereIn('status', ['MENUNGGU', 'DRAFT']);
                })
                ->when($workspaceScope === 'my', function ($q) use ($user) {
                    $q->where('created_by', $user->id);
                })
                ->count();

            $workspaceStats['distribusi_perlu_proses'] = TransaksiDistribusi::query()
                ->whereIn('status_distribusi', ['draft', 'diproses'])
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pengirim', $pegawaiId);
                })
                ->count();

            $workspaceStats['distribusi_perlu_diterima'] = TransaksiDistribusi::query()
                ->where('status_distribusi', 'dikirim')
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pengirim', $pegawaiId);
                })
                ->count();

            $workspaceStats['retur_diajukan'] = ReturBarang::query()
                ->where('status_retur', 'DIAJUKAN')
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pengirim', $pegawaiId);
                })
                ->count();

            $workspaceStats['pemakaian_diajukan'] = PemakaianBarang::query()
                ->where('status_pemakaian', 'DIAJUKAN')
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pemakai', $pegawaiId);
                })
                ->count();

            // Queue prioritas (oldest pending first, lalu dihitung skor urgensi)
            $approvalQueue = ApprovalLog::query()
                ->with(['approvalFlow', 'permintaan'])
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('status', 'MENUNGGU')
                ->whereIn('role_id', $roleIds)
                ->when($workspaceScope === 'my', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orderBy('created_at')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $ageHours = (int) $item->created_at->diffInHours(now());
                    return [
                        'source' => 'approval',
                        'title' => $item->permintaan->no_permintaan ?? ('Approval #' . $item->id),
                        'subtitle' => $item->approvalFlow->nama_step ?? 'Step Approval',
                        'status' => 'MENUNGGU',
                        'age_hours' => $ageHours,
                        'priority_score' => 300 + $ageHours,
                        'url' => route('transaction.approval.show', $item->id),
                    ];
                });

            $distribusiQueue = TransaksiDistribusi::query()
                ->whereIn('status_distribusi', ['draft', 'diproses', 'dikirim'])
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pengirim', $pegawaiId);
                })
                ->orderBy('tanggal_distribusi')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $ageHours = (int) optional($item->tanggal_distribusi)->diffInHours(now());
                    $status = strtoupper((string) ($item->status_distribusi?->value ?? $item->status_distribusi));
                    $base = $status === 'DIKIRIM' ? 280 : 220;
                    return [
                        'source' => 'distribusi',
                        'title' => $item->no_sbbk,
                        'subtitle' => 'Distribusi Barang',
                        'status' => $status,
                        'age_hours' => $ageHours,
                        'priority_score' => $base + $ageHours,
                        'url' => route('transaction.distribusi.show', $item->id_distribusi),
                    ];
                });

            $returQueue = ReturBarang::query()
                ->where('status_retur', 'DIAJUKAN')
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pengirim', $pegawaiId);
                })
                ->orderBy('tanggal_retur')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $ageHours = (int) optional($item->tanggal_retur)->diffInHours(now());
                    return [
                        'source' => 'retur',
                        'title' => $item->no_retur,
                        'subtitle' => 'Retur Barang',
                        'status' => 'DIAJUKAN',
                        'age_hours' => $ageHours,
                        'priority_score' => 200 + $ageHours,
                        'url' => route('transaction.retur-barang.show', $item->id_retur),
                    ];
                });

            $pemakaianQueue = PemakaianBarang::query()
                ->where('status_pemakaian', 'DIAJUKAN')
                ->when($workspaceScope === 'my' && $pegawaiId, function ($q) use ($pegawaiId) {
                    $q->where('id_pegawai_pemakai', $pegawaiId);
                })
                ->orderBy('tanggal_pemakaian')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $ageHours = (int) optional($item->tanggal_pemakaian)->diffInHours(now());
                    return [
                        'source' => 'pemakaian',
                        'title' => $item->no_pemakaian,
                        'subtitle' => 'Pemakaian Barang',
                        'status' => 'DIAJUKAN',
                        'age_hours' => $ageHours,
                        'priority_score' => 180 + $ageHours,
                        'url' => route('transaction.pemakaian-barang.show', $item->id_pemakaian),
                    ];
                });

            $urgentQueue = $approvalQueue
                ->concat($distribusiQueue)
                ->concat($returQueue)
                ->concat($pemakaianQueue)
                ->sortByDesc('priority_score')
                ->take(5)
                ->values();

            // SLA sederhana (jam)
            $workspaceSla['approval_over_sla'] = $approvalQueue->where('age_hours', '>', 24)->count();
            $workspaceSla['distribusi_over_sla'] = $distribusiQueue->where('age_hours', '>', 48)->count();
            $workspaceSla['retur_over_sla'] = $returQueue->where('age_hours', '>', 72)->count();
            $workspaceSla['pemakaian_over_sla'] = $pemakaianQueue->where('age_hours', '>', 48)->count();
        }

        return view('user.dashboard', compact(
            'totalAssets',
            'totalAssetValue',
            'totalPersediaanValue',
            'totalFarmasiValue',
            'activeRequests',
            'latestRequests',
            'trackingItems',
            'trackingStepMax',
            'latestDistribusiTracking',
            'inventoryCategoryData',
            'latestAssets',
            'latestTransactions',
            'requestStatusData',
            'isPengurusBarangWorkspace',
            'workspaceStats',
            'urgentQueue',
            'workspaceSla',
            'workspaceScope'
        ));
    }
}
