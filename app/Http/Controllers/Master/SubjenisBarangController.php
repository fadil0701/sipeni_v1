<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterSubjenisBarang;
use App\Models\MasterJenisBarang;

class SubjenisBarangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $subjenisBarangs = MasterSubjenisBarang::with('jenisBarang.kategoriBarang')->latest()->paginate($perPage)->appends($request->query());
        return view('master-data.subjenis-barang.index', compact('subjenisBarangs'));
    }

    public function create()
    {
        $jenisBarangs = MasterJenisBarang::with('kategoriBarang')->get();
        return view('master-data.subjenis-barang.create', compact('jenisBarangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_jenis_barang' => 'required|exists:master_jenis_barang,id_jenis_barang',
            'kode_subjenis_barang' => 'required|string|max:255',
            'nama_subjenis_barang' => 'required|string|max:255',
        ]);

        MasterSubjenisBarang::create($validated);

        return redirect()->route('master-data.subjenis-barang.index')
            ->with('success', 'Subjenis Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $subjenisBarang = MasterSubjenisBarang::with('jenisBarang.kategoriBarang')->findOrFail($id);
        return view('master-data.subjenis-barang.show', compact('subjenisBarang'));
    }

    public function edit($id)
    {
        $subjenisBarang = MasterSubjenisBarang::findOrFail($id);
        $jenisBarangs = MasterJenisBarang::with('kategoriBarang')->get();
        return view('master-data.subjenis-barang.edit', compact('subjenisBarang', 'jenisBarangs'));
    }

    public function update(Request $request, $id)
    {
        $subjenisBarang = MasterSubjenisBarang::findOrFail($id);

        $validated = $request->validate([
            'id_jenis_barang' => 'required|exists:master_jenis_barang,id_jenis_barang',
            'kode_subjenis_barang' => 'required|string|max:255',
            'nama_subjenis_barang' => 'required|string|max:255',
        ]);

        $subjenisBarang->update($validated);

        return redirect()->route('master-data.subjenis-barang.index')
            ->with('success', 'Subjenis Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $subjenisBarang = MasterSubjenisBarang::findOrFail($id);
        $subjenisBarang->delete();

        return redirect()->route('master-data.subjenis-barang.index')
            ->with('success', 'Subjenis Barang berhasil dihapus.');
    }
}
