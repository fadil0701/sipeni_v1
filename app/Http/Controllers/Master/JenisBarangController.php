<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterJenisBarang;
use App\Models\MasterKategoriBarang;

class JenisBarangController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $idKategoriBarang = $request->query('id_kategori_barang');

        $query = MasterJenisBarang::with('kategoriBarang.kodeBarang');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_jenis_barang', 'like', "%{$search}%")
                    ->orWhere('nama_jenis_barang', 'like', "%{$search}%")
                    ->orWhereHas('kategoriBarang', function ($kategoriQ) use ($search) {
                        $kategoriQ->where('nama_kategori_barang', 'like', "%{$search}%");
                    });
            });
        }
        if (!empty($idKategoriBarang)) {
            $query->where('id_kategori_barang', $idKategoriBarang);
        }

        $kategoriBarangs = MasterKategoriBarang::query()
            ->orderBy('nama_kategori_barang')
            ->get(['id_kategori_barang', 'nama_kategori_barang']);
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $jenisBarangs = $query->latest()->paginate($perPage)->appends($request->query());
        return view('master-data.jenis-barang.index', compact('jenisBarangs', 'kategoriBarangs'));
    }

    public function create()
    {
        $kategoriBarangs = MasterKategoriBarang::with('kodeBarang')->get();
        return view('master-data.jenis-barang.create', compact('kategoriBarangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kategori_barang' => 'required|exists:master_kategori_barang,id_kategori_barang',
            'kode_jenis_barang' => 'required|string|max:255',
            'nama_jenis_barang' => 'required|string|max:255',
        ]);

        MasterJenisBarang::create($validated);

        return redirect()->route('master-data.jenis-barang.index')
            ->with('success', 'Jenis Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $jenisBarang = MasterJenisBarang::with('kategoriBarang.kodeBarang')->findOrFail($id);
        return view('master-data.jenis-barang.show', compact('jenisBarang'));
    }

    public function edit($id)
    {
        $jenisBarang = MasterJenisBarang::findOrFail($id);
        $kategoriBarangs = MasterKategoriBarang::with('kodeBarang')->get();
        return view('master-data.jenis-barang.edit', compact('jenisBarang', 'kategoriBarangs'));
    }

    public function update(Request $request, $id)
    {
        $jenisBarang = MasterJenisBarang::findOrFail($id);

        $validated = $request->validate([
            'id_kategori_barang' => 'required|exists:master_kategori_barang,id_kategori_barang',
            'kode_jenis_barang' => 'required|string|max:255',
            'nama_jenis_barang' => 'required|string|max:255',
        ]);

        $jenisBarang->update($validated);

        return redirect()->route('master-data.jenis-barang.index')
            ->with('success', 'Jenis Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jenisBarang = MasterJenisBarang::findOrFail($id);
        $jenisBarang->delete();

        return redirect()->route('master-data.jenis-barang.index')
            ->with('success', 'Jenis Barang berhasil dihapus.');
    }
}
