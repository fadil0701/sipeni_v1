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

class ReportController extends Controller
{
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

        return view('report.kartu-stok', compact('stocks', 'gudangs'));
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
}

