<?php

namespace App\Http\Controllers\Report;

use App\Helpers\PaginationHelper;
use App\Helpers\StockWarehouseSummaryViewHelper;
use App\Http\Controllers\Controller;
use App\Models\DataStock;
use App\Models\MasterGudang;
use App\Models\MasterUnitKerja;
use App\Models\MutasiAset;
use App\Models\PemakaianBarang;
use App\Models\RegisterAset;
use App\Models\ReturBarang;
use App\Models\TransaksiDistribusi;
use App\Services\StockMerkBreakdownService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $gudangs = MasterGudang::query()->orderBy('nama_gudang')->get();
        $showWarehouseSummaryCards = StockWarehouseSummaryViewHelper::canAccessSummaryCards($user);
        $tampilan = $this->resolveStockReportTampilan($request, $user);

        $baseAgg = $this->newStockGudangReportQuery(false);
        $this->applyReportDataStockUnitPersediaanFarmasiScope($baseAgg, $user);
        $this->applyStockGudangReportFilters($baseAgg, $request, false);
        $gudangCards = $this->aggregateDataStockRowsPerGudang($baseAgg, $gudangs);

        $perPage = PaginationHelper::getPerPage($request, 10);
        $appendQuery = array_merge($request->query(), ['tampilan' => $tampilan]);

        if ($tampilan === 'cards') {
            $stocks = new LengthAwarePaginator(
                collect(),
                0,
                max(1, (int) $perPage),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
            $stocks->appends($appendQuery);
        } else {
            $query = $this->newStockGudangReportQuery(true);
            $this->applyReportDataStockUnitPersediaanFarmasiScope($query, $user);
            $this->applyStockGudangReportFilters($query, $request, true);
            $stocks = $query->latest('last_updated')->paginate($perPage)->appends($appendQuery);
        }

        return view('report.stock-gudang', compact('stocks', 'gudangs', 'gudangCards', 'tampilan', 'showWarehouseSummaryCards'));
    }

    public function kartuStok(Request $request)
    {
        $user = Auth::user();
        $gudangs = MasterGudang::query()->orderBy('nama_gudang')->get();
        $showWarehouseSummaryCards = StockWarehouseSummaryViewHelper::canAccessSummaryCards($user);
        $tampilan = $this->resolveStockReportTampilan($request, $user);

        $baseAgg = $this->newKartuStokReportQuery(false);
        $this->applyReportDataStockUnitPersediaanFarmasiScope($baseAgg, $user);
        $this->applyKartuStokReportFilters($baseAgg, $request, false);
        $gudangCards = $this->aggregateDataStockRowsPerGudang($baseAgg, $gudangs);

        $perPage = PaginationHelper::getPerPage($request, 10);
        $appendQuery = array_merge($request->query(), ['tampilan' => $tampilan]);

        if ($tampilan === 'cards') {
            $stocks = new LengthAwarePaginator(
                collect(),
                0,
                max(1, (int) $perPage),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
            $stocks->appends($appendQuery);
        } else {
            $query = $this->newKartuStokReportQuery(true);
            $this->applyReportDataStockUnitPersediaanFarmasiScope($query, $user);
            $this->applyKartuStokReportFilters($query, $request, true);
            $stocks = $query->latest('last_updated')->paginate($perPage)->appends($appendQuery);

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
        }

        return view('report.kartu-stok', compact('stocks', 'gudangs', 'gudangCards', 'tampilan', 'showWarehouseSummaryCards'));
    }

    /**
     * Rincian stok per merk untuk satu barang + gudang (kartu stok agregat).
     */
    public function kartuStokMerkBreakdown(Request $request)
    {
        $validated = $request->validate([
            'id_data_barang' => 'required|integer|exists:master_data_barang,id_data_barang',
            'id_gudang' => 'required|integer|exists:master_gudang,id_gudang',
        ]);

        $stock = DataStock::with(['dataBarang', 'gudang', 'satuan'])
            ->where('id_data_barang', $validated['id_data_barang'])
            ->where('id_gudang', $validated['id_gudang'])
            ->first();

        if (! $stock) {
            abort(404, 'Data stok tidak ditemukan untuk kombinasi barang dan gudang ini.');
        }

        $breakdownRows = StockMerkBreakdownService::breakdownByMerk(
            (int) $validated['id_data_barang'],
            (int) $validated['id_gudang']
        );
        $sumInventory = StockMerkBreakdownService::sumBreakdownQty($breakdownRows);

        $backUrl = route('reports.kartu-stok', $request->only(['gudang', 'search', 'tampilan']));

        return view('report.kartu-stok-merk-breakdown', compact(
            'stock',
            'breakdownRows',
            'sumInventory',
            'backUrl'
        ));
    }

    public function exportStockGudang(Request $request)
    {
        $user = Auth::user();
        $query = DataStock::with(['dataBarang', 'gudang', 'satuan']);
        $this->applyReportDataStockUnitPersediaanFarmasiScope($query, $user);
        $this->applyStockGudangReportFilters($query, $request, true);
        $rows = $query->latest('last_updated')->get();

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
        $retur = ReturBarang::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_retur', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_retur', '<=', $to))
            ->count();

        return view('report.transaksi-summary', compact('distribusi', 'retur'));
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

        $filename = 'rekap-maintenance-'.now()->format('Ymd_His').'.csv';
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

    private function resolveStockReportTampilan(Request $request, $user = null): string
    {
        $t = $request->get('tampilan', 'tabel');
        $t = in_array($t, ['tabel', 'cards'], true) ? $t : 'tabel';
        if (! StockWarehouseSummaryViewHelper::canAccessSummaryCards($user)) {
            return 'tabel';
        }

        return $t;
    }

    private function applyReportDataStockUnitPersediaanFarmasiScope(Builder $query, $user): void
    {
        if (! StockWarehouseSummaryViewHelper::shouldLimitStockViewsToPersediaanFarmasiForUnit($user)) {
            return;
        }

        $query->whereHas('dataBarang', function ($b) {
            $b->whereHas('dataInventory', function ($inv) {
                StockMerkBreakdownService::applyStockEligibleInventoryFilter($inv);
                $inv->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI']);
            });
        });
    }

    private function newStockGudangReportQuery(bool $withRelations): Builder
    {
        $q = DataStock::query();
        if ($withRelations) {
            $q->with(['dataBarang', 'gudang', 'satuan']);
        }

        return $q;
    }

    private function applyStockGudangReportFilters(Builder $query, Request $request, bool $applyGudangFromRequest): void
    {
        if ($applyGudangFromRequest && $request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
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
    }

    private function newKartuStokReportQuery(bool $withRelations): Builder
    {
        $q = DataStock::query();
        if ($withRelations) {
            $q->with(['dataBarang', 'gudang', 'satuan']);
        }

        return $q;
    }

    private function applyKartuStokReportFilters(Builder $query, Request $request, bool $applyGudangFromRequest): void
    {
        if ($applyGudangFromRequest && $request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dataBarang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_data_barang', 'like', "%{$search}%");
            });
        }
    }

    /**
     * @param  Collection<int, MasterGudang>  $gudangs
     * @return Collection<int, array{gudang: MasterGudang, sku_count: int, qty_sum: float}>
     */
    private function aggregateDataStockRowsPerGudang(Builder $queryNoGudangFromRequest, $gudangs)
    {
        $ids = $gudangs->pluck('id_gudang')->filter()->values();
        $aggregatesByGudang = collect();
        if ($ids->isNotEmpty()) {
            $aggregatesByGudang = (clone $queryNoGudangFromRequest)
                ->whereIn('id_gudang', $ids->all())
                ->selectRaw('id_gudang, COUNT(*) as sku_count, COALESCE(SUM(qty_akhir), 0) as qty_sum')
                ->groupBy('id_gudang')
                ->get()
                ->keyBy('id_gudang');
        }

        return $gudangs->map(function ($gudang) use ($aggregatesByGudang) {
            $row = $aggregatesByGudang->get($gudang->id_gudang);

            return [
                'gudang' => $gudang,
                'sku_count' => $row ? (int) $row->sku_count : 0,
                'qty_sum' => $row ? (float) $row->qty_sum : 0.0,
            ];
        });
    }

    public function exportKartuStok(Request $request)
    {
        $user = Auth::user();
        $query = $this->newStockGudangReportQuery(true);
        $this->applyReportDataStockUnitPersediaanFarmasiScope($query, $user);
        $this->applyStockGudangReportFilters($query, $request, true);
        $rows = $query->latest('last_updated')->get();

        return $this->streamCsv(
            'laporan-kartu-stok-'.now()->format('Ymd_His').'.csv',
            ['Nama Barang', 'Gudang', 'Qty Awal', 'Qty Masuk', 'Qty Keluar', 'Qty Akhir', 'Satuan', 'Last Updated'],
            $rows,
            fn ($row) => [
                $row->dataBarang->nama_barang ?? '-',
                $row->gudang->nama_gudang ?? '-',
                (float) $row->qty_awal,
                (float) $row->qty_masuk,
                (float) $row->qty_keluar,
                (float) $row->qty_akhir,
                $row->satuan->nama_satuan ?? '-',
                optional($row->last_updated)->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function exportTransaksiSummary(Request $request)
    {
        $from = $request->date('tanggal_awal');
        $to = $request->date('tanggal_akhir');

        $distribusi = TransaksiDistribusi::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_distribusi', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_distribusi', '<=', $to))
            ->count();
        $retur = ReturBarang::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_retur', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_retur', '<=', $to))
            ->count();

        return $this->streamCsv(
            'laporan-transaksi-'.now()->format('Ymd_His').'.csv',
            ['Jenis', 'Jumlah', 'Tanggal Awal', 'Tanggal Akhir'],
            [
                ['Distribusi', $distribusi, $from?->format('Y-m-d') ?? '-', $to?->format('Y-m-d') ?? '-'],
                ['Retur', $retur, $from?->format('Y-m-d') ?? '-', $to?->format('Y-m-d') ?? '-'],
            ],
            fn ($row) => $row
        );
    }

    public function exportAsetSummary(Request $request)
    {
        $from = $request->date('tanggal_awal');
        $to = $request->date('tanggal_akhir');

        $asetAktif = RegisterAset::query()->where('status_aset', 'AKTIF')->count();
        $asetNonaktif = RegisterAset::query()->where('status_aset', 'NONAKTIF')->count();
        $mutasiCount = MutasiAset::query()
            ->when($from, fn ($q) => $q->whereDate('tanggal_mutasi', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('tanggal_mutasi', '<=', $to))
            ->count();

        return $this->streamCsv(
            'laporan-aset-'.now()->format('Ymd_His').'.csv',
            ['Metrik', 'Nilai', 'Tanggal Awal', 'Tanggal Akhir'],
            [
                ['Aset Aktif', $asetAktif, $from?->format('Y-m-d') ?? '-', $to?->format('Y-m-d') ?? '-'],
                ['Aset Nonaktif', $asetNonaktif, $from?->format('Y-m-d') ?? '-', $to?->format('Y-m-d') ?? '-'],
                ['Mutasi (periode)', $mutasiCount, $from?->format('Y-m-d') ?? '-', $to?->format('Y-m-d') ?? '-'],
            ],
            fn ($row) => $row
        );
    }

    /**
     * @param  iterable<int, mixed>  $rows
     */
    private function streamCsv(string $filename, array $header, iterable $rows, callable $mapper)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($header, $rows, $mapper): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $mapper($row));
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
