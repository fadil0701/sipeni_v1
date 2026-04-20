<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataStock;
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

    public function exportStockGudang(Request $request)
    {
        // Export implementation using maatwebsite/excel
    }
}

