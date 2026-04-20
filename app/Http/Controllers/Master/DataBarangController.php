<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MasterDataBarang;
use App\Models\MasterSubjenisBarang;
use App\Models\MasterSatuan;

class DataBarangController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterDataBarang::with(['subjenisBarang', 'satuan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_data_barang', 'like', "%{$search}%");
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $dataBarangs = $query->latest()->paginate($perPage)->appends($request->query());
        return view('master-data.data-barang.index', compact('dataBarangs'));
    }

    public function create()
    {
        $subjenisBarangs = MasterSubjenisBarang::with('jenisBarang.kategoriBarang')->get();
        $satuans = MasterSatuan::all();
        return view('master-data.data-barang.create', compact('subjenisBarangs', 'satuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_subjenis_barang' => 'required|exists:master_subjenis_barang,id_subjenis_barang',
            'id_satuan' => 'required|exists:master_satuan,id_satuan',
            'kode_data_barang' => 'required|string|max:255|unique:master_data_barang,kode_data_barang',
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'upload_foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'foto_barang' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('upload_foto')) {
            $validated['upload_foto'] = $request->file('upload_foto')->store('foto-barang', 'public');
        }

        MasterDataBarang::create($validated);

        return redirect()->route('master-data.data-barang.index')
            ->with('success', 'Data Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $dataBarang = MasterDataBarang::with(['subjenisBarang.jenisBarang.kategoriBarang', 'satuan'])->findOrFail($id);
        return view('master-data.data-barang.show', compact('dataBarang'));
    }

    public function edit($id)
    {
        $dataBarang = MasterDataBarang::findOrFail($id);
        $subjenisBarangs = MasterSubjenisBarang::with('jenisBarang.kategoriBarang')->get();
        $satuans = MasterSatuan::all();
        return view('master-data.data-barang.edit', compact('dataBarang', 'subjenisBarangs', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $dataBarang = MasterDataBarang::findOrFail($id);

        $validated = $request->validate([
            'id_subjenis_barang' => 'required|exists:master_subjenis_barang,id_subjenis_barang',
            'id_satuan' => 'required|exists:master_satuan,id_satuan',
            'kode_data_barang' => 'required|string|max:255|unique:master_data_barang,kode_data_barang,' . $id . ',id_data_barang',
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'upload_foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'foto_barang' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('upload_foto')) {
            // Hapus foto lama jika ada
            if ($dataBarang->upload_foto) {
                Storage::disk('public')->delete($dataBarang->upload_foto);
            }
            $validated['upload_foto'] = $request->file('upload_foto')->store('foto-barang', 'public');
        }

        $dataBarang->update($validated);

        return redirect()->route('master-data.data-barang.index')
            ->with('success', 'Data Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataBarang = MasterDataBarang::findOrFail($id);
        $dataBarang->delete();

        return redirect()->route('master-data.data-barang.index')
            ->with('success', 'Data Barang berhasil dihapus.');
    }
}
