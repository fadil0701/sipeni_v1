<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataStock;
use App\Models\MutasiAset;
use App\Models\PemakaianBarang;
use App\Models\ReturBarang;
use App\Models\TransaksiDistribusi;
use App\Models\RegisterAset;
use App\Models\MasterGudang;
use App\Models\MasterUnitKerja;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function buildMaintenanceReportData(Request $request): array
    {
        $from = $request->date('tanggal_awal');
        $to = $request->date('tanggal_akhir');
        $unitKerjaId = $request->filled('unit_kerja') ? (int) $request->unit_kerja : null;

        $base = DB::table('riwayat_pemeliharaan as rp')
            ->join('register_aset as ra', 'ra.id_register_aset', '=', 'rp.id_register_aset')
            ->join('master_unit_kerja as muk', 'muk.id_unit_kerja', '=', 'ra.id_unit_kerja')
            ->when($from, fn ($q) => $q->whereDate('rp.tanggal_pemeliharaan', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('rp.tanggal_pemeliharaan', '<=', $to))
            ->when($unitKerjaId, fn ($q) => $q->where('ra.id_unit_kerja', $unitKerjaId));

        $summary = (clone $base)
            ->selectRaw('COUNT(*) as total_aktivitas')
            ->selectRaw("SUM(CASE WHEN rp.status = 'SELESAI' THEN 1 ELSE 0 END) as total_selesai")
            ->selectRaw("SUM(CASE WHEN rp.status = 'GAGAL' THEN 1 ELSE 0 END) as total_gagal")
            ->selectRaw("SUM(CASE WHEN rp.status = 'DIBATALKAN' THEN 1 ELSE 0 END) as total_dibatalkan")
            ->first();

        $serviceCostByUnit = DB::table('service_report as sr')
            ->join('register_aset as ra', 'ra.id_register_aset', '=', 'sr.id_register_aset')
            ->when($from, fn ($q) => $q->whereDate('sr.tanggal_service', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sr.tanggal_service', '<=', $to))
            ->when($unitKerjaId, fn ($q) => $q->where('ra.id_unit_kerja', $unitKerjaId))
            ->groupBy('ra.id_unit_kerja')
            ->selectRaw('ra.id_unit_kerja as id_unit_kerja, SUM(sr.total_biaya) as total_biaya_service')
            ->pluck('total_biaya_service', 'id_unit_kerja');

        $kalibrasiCostByUnit = DB::table('kalibrasi_aset as ka')
            ->join('register_aset as ra', 'ra.id_register_aset', '=', 'ka.id_register_aset')
            ->when($from, fn ($q) => $q->whereDate('ka.tanggal_kalibrasi', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('ka.tanggal_kalibrasi', '<=', $to))
            ->when($unitKerjaId, fn ($q) => $q->where('ra.id_unit_kerja', $unitKerjaId))
            ->groupBy('ra.id_unit_kerja')
            ->selectRaw('ra.id_unit_kerja as id_unit_kerja, SUM(ka.biaya_kalibrasi) as total_biaya_kalibrasi')
            ->pluck('total_biaya_kalibrasi', 'id_unit_kerja');

        $rows = (clone $base)
            ->groupBy('ra.id_unit_kerja', 'muk.kode_unit_kerja', 'muk.nama_unit_kerja')
            ->selectRaw('ra.id_unit_kerja, muk.kode_unit_kerja, muk.nama_unit_kerja')
            ->selectRaw('COUNT(*) as total_aktivitas')
            ->selectRaw("SUM(CASE WHEN rp.status = 'SELESAI' THEN 1 ELSE 0 END) as total_selesai")
            ->selectRaw("SUM(CASE WHEN rp.status = 'GAGAL' THEN 1 ELSE 0 END) as total_gagal")
            ->selectRaw("SUM(CASE WHEN rp.status = 'DIBATALKAN' THEN 1 ELSE 0 END) as total_dibatalkan")
            ->orderBy('muk.nama_unit_kerja')
            ->get()
            ->map(function ($row) use ($serviceCostByUnit, $kalibrasiCostByUnit) {
                $service = (float) ($serviceCostByUnit[$row->id_unit_kerja] ?? 0);
                $kalibrasi = (float) ($kalibrasiCostByUnit[$row->id_unit_kerja] ?? 0);
                $row->total_biaya = $service + $kalibrasi;
                return $row;
            });

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    public function index()
    {
        return view('report.index');
    }

    public function stockGudang(Request $request)
    {
        $query = DataStock::with(['dataBarang', 'gudang', 'satuan']);

        // Filters
        if ($request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
        }

        if ($request->filled('sub_kategori')) {
            // Filter implementation
        }

        if ($request->filled('sub_kegiatan')) {
            // Filter implementation
        }

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('last_updated', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dataBarang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $stocks = $query->latest('last_updated')->paginate($perPage)->appends($request->query());
        $gudangs = MasterGudang::all();

        return view('report.stock-gudang', compact('stocks', 'gudangs'));
    }

    public function kartuStok(Request $request)
    {
        $query = DataStock::with(['dataBarang', 'gudang', 'satuan']);

        if ($request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dataBarang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_data_barang', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $stocks = $query->latest('last_updated')->paginate($perPage)->appends($request->query());
        $gudangs = MasterGudang::all();

        // Qty awal di DB sering 0 untuk baris yang tercipta dari pemasukan pertama; tampilkan saldo awal implisit:
        // qty_akhir = qty_awal + qty_masuk - qty_keluar  =>  qty_awal_implied = qty_akhir - qty_masuk + qty_keluar
        $stocks->getCollection()->transform(function ($stock) {
            $qa = (float) $stock->qty_awal;
            $qm = (float) $stock->qty_masuk;
            $qk = (float) $stock->qty_keluar;
            $qak = (float) $stock->qty_akhir;
            $implied = $qak - $qm + $qk;
            $stock->qty_awal_laporan = ($qa > 0.00001) ? $qa : max(0, $implied);
            $stock->qty_awal_terderivasi = ($qa <= 0.00001) && ($qm > 0.00001 || $qk > 0.00001);

            return $stock;
        });

        $stockMetaMap = collect();
        $stockRows = $stocks->getCollection();
        if ($stockRows->isNotEmpty()) {
            $dataBarangIds = $stockRows->pluck('id_data_barang')->filter()->unique()->values();
            $gudangIds = $stockRows->pluck('id_gudang')->filter()->unique()->values();

            $latestInventories = \App\Models\DataInventory::query()
                ->whereIn('id_data_barang', $dataBarangIds)
                ->whereIn('id_gudang', $gudangIds)
                ->orderByDesc('updated_at')
                ->orderByDesc('id_inventory')
                ->get(['id_data_barang', 'id_gudang', 'merk', 'no_batch', 'tanggal_kedaluwarsa'])
                ->unique(function ($row) {
                    return $row->id_data_barang . '_' . $row->id_gudang;
                });

            $stockMetaMap = $latestInventories->keyBy(function ($row) {
                return $row->id_data_barang . '_' . $row->id_gudang;
            });
        }

        return view('report.kartu-stok', compact('stocks', 'gudangs', 'stockMetaMap'));
    }

    public function exportStockGudang(Request $request)
    {
        $rows = DataStock::with(['dataBarang', 'gudang', 'satuan'])
            ->when($request->filled('gudang'), fn ($q) => $q->where('id_gudang', $request->gudang))
            ->latest('last_updated')
            ->get();

        $filename = 'laporan-stock-gudang-'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rows): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['Nama Barang', 'Gudang', 'Qty Akhir', 'Satuan', 'Last Updated']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->dataBarang->nama_barang ?? '-',
                    $row->gudang->nama_gudang ?? '-',
                    (float) $row->qty_akhir,
                    $row->satuan->nama_satuan ?? '-',
                    optional($row->last_updated)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function transaksiSummary(Request $request)
    {
        $from = $request->date('tanggal_awal');
        $to = $request->date('tanggal_akhir');

        $distribusi = TransaksiDistribusi::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_distribusi', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_distribusi', '<=', $to))
            ->count();
        $pemakaian = PemakaianBarang::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_pemakaian', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_pemakaian', '<=', $to))
            ->count();
        $retur = ReturBarang::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_retur', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_retur', '<=', $to))
            ->count();

        return view('report.transaksi-summary', compact('distribusi', 'pemakaian', 'retur'));
    }

    public function asetSummary(Request $request)
    {
        $from = $request->date('tanggal_awal');
        $to = $request->date('tanggal_akhir');

        $asetAktif = RegisterAset::query()->where('status_aset', 'AKTIF')->count();
        $asetNonaktif = RegisterAset::query()->where('status_aset', 'NONAKTIF')->count();
        $mutasiCount = MutasiAset::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_mutasi', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_mutasi', '<=', $to))
            ->count();

        return view('report.aset-summary', compact('asetAktif', 'asetNonaktif', 'mutasiCount'));
    }

    public function maintenanceSummary(Request $request)
    {
        $report = $this->buildMaintenanceReportData($request);
        $rows = $report['rows'];
        $summary = $report['summary'];

        $unitKerjas = MasterUnitKerja::query()
            ->orderBy('nama_unit_kerja')
            ->get(['id_unit_kerja', 'nama_unit_kerja', 'kode_unit_kerja']);

        return view('report.maintenance-summary', [
            'rows' => $rows,
            'unitKerjas' => $unitKerjas,
            'summary' => $summary,
        ]);
    }

    public function exportMaintenanceSummary(Request $request)
    {
        $report = $this->buildMaintenanceReportData($request);
        $rows = $report['rows'];

        $filename = 'rekap-maintenance-' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rows): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['Kode Unit', 'Nama Unit Kerja', 'Total Aktivitas', 'Selesai', 'Gagal', 'Dibatalkan', 'Total Biaya']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    (string) $row->kode_unit_kerja,
                    (string) $row->nama_unit_kerja,
                    (int) $row->total_aktivitas,
                    (int) $row->total_selesai,
                    (int) $row->total_gagal,
                    (int) $row->total_dibatalkan,
                    (float) $row->total_biaya,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

