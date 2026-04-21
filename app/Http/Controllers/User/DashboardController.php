<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\PermintaanBarang;
use App\Models\DataStock;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Enums\PermintaanBarangStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get statistics
        $totalAssets = InventoryItem::whereHas('inventory', function ($query) {
            $query->where('jenis_inventory', 'ASET');
        })->count();

        $totalStock = DataStock::sum('qty_akhir');

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

                $currentStep = (int) ($current?->approvalFlow?->step_order ?? 1);
                $progressPercent = (int) round(($currentStep / max(1, $trackingStepMax)) * 100);

                $displayStatus = $current?->status
                    ?? strtoupper((string) ($req->status?->value ?? $req->status ?? '-'));
                $displayStepName = $current?->approvalFlow?->nama_step ?? 'Pengajuan';

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
        $latestDistribusi = \App\Models\TransaksiDistribusi::latest('tanggal_distribusi')
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

        // Get request status data for chart
        $requestStatusData = [
            'diajukan' => PermintaanBarang::where('status', PermintaanBarangStatus::Diajukan->value)->count(),
            'disetujui' => PermintaanBarang::where('status', PermintaanBarangStatus::Diverifikasi->value)->count(),
            'dikirim' => PermintaanBarang::where('status', PermintaanBarangStatus::Dikirim->value)->count(),
            'ditolak' => PermintaanBarang::where('status', PermintaanBarangStatus::Ditolak->value)->count(),
        ];

        return view('user.dashboard', compact(
            'totalAssets',
            'totalStock',
            'activeRequests',
            'latestRequests',
            'trackingItems',
            'trackingStepMax',
            'latestAssets',
            'latestTransactions',
            'requestStatusData'
        ));
    }
}
