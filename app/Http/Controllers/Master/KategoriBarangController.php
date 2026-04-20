<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterKategoriBarang;
use App\Models\MasterKodeBarang;

class KategoriBarangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $kategoriBarangs = MasterKategoriBarang::with('kodeBarang.aset')->latest()->paginate($perPage)->appends($request->query());
        return view('master-data.kategori-barang.index', compact('kategoriBarangs'));
    }

    public function create()
    {
        $kodeBarangs = MasterKodeBarang::with('aset')->get();
        return view('master-data.kategori-barang.create', compact('kodeBarangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kode_barang' => 'required|exists:master_kode_barang,id_kode_barang',
            'kode_kategori_barang' => 'required|string|max:255',
            'nama_kategori_barang' => 'required|string|max:255',
        ]);

        MasterKategoriBarang::create($validated);

        return redirect()->route('master-data.kategori-barang.index')
            ->with('success', 'Kategori Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $kategoriBarang = MasterKategoriBarang::with('kodeBarang.aset')->findOrFail($id);
        return view('master-data.kategori-barang.show', compact('kategoriBarang'));
    }

    public function edit($id)
    {
        $kategoriBarang = MasterKategoriBarang::findOrFail($id);
        $kodeBarangs = MasterKodeBarang::with('aset')->get();
        return view('master-data.kategori-barang.edit', compact('kategoriBarang', 'kodeBarangs'));
    }

    public function update(Request $request, $id)
    {
        $kategoriBarang = MasterKategoriBarang::findOrFail($id);

        $validated = $request->validate([
            'id_kode_barang' => 'required|exists:master_kode_barang,id_kode_barang',
            'kode_kategori_barang' => 'required|string|max:255',
            'nama_kategori_barang' => 'required|string|max:255',
        ]);

        $kategoriBarang->update($validated);

        return redirect()->route('master-data.kategori-barang.index')
            ->with('success', 'Kategori Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategoriBarang = MasterKategoriBarang::findOrFail($id);
        $kategoriBarang->delete();

        return redirect()->route('master-data.kategori-barang.index')
            ->with('success', 'Kategori Barang berhasil dihapus.');
    }
}
