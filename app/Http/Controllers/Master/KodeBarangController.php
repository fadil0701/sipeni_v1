<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterKodeBarang;
use App\Models\MasterAset;

class KodeBarangController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $idAset = $request->query('id_aset');

        $query = MasterKodeBarang::with('aset');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_kode_barang', 'like', "%{$search}%")
                    ->orWhereHas('aset', function ($asetQ) use ($search) {
                        $asetQ->where('nama_aset', 'like', "%{$search}%");
                    });
            });
        }
        if (!empty($idAset)) {
            $query->where('id_aset', $idAset);
        }

        $asets = MasterAset::query()->orderBy('nama_aset')->get(['id_aset', 'nama_aset']);
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $kodeBarangs = $query->latest()->paginate($perPage)->appends($request->query());
        return view('master-data.kode-barang.index', compact('kodeBarangs', 'asets'));
    }

    public function create()
    {
        $asets = MasterAset::all();
        return view('master-data.kode-barang.create', compact('asets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_aset' => 'required|exists:master_aset,id_aset',
            'kode_barang' => 'required|string|max:255|unique:master_kode_barang,kode_barang',
            'nama_kode_barang' => 'required|string|max:255',
        ]);

        MasterKodeBarang::create($validated);

        return redirect()->route('master-data.kode-barang.index')
            ->with('success', 'Kode Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $kodeBarang = MasterKodeBarang::with('aset')->findOrFail($id);
        return view('master-data.kode-barang.show', compact('kodeBarang'));
    }

    public function edit($id)
    {
        $kodeBarang = MasterKodeBarang::findOrFail($id);
        $asets = MasterAset::all();
        return view('master-data.kode-barang.edit', compact('kodeBarang', 'asets'));
    }

    public function update(Request $request, $id)
    {
        $kodeBarang = MasterKodeBarang::findOrFail($id);

        $validated = $request->validate([
            'id_aset' => 'required|exists:master_aset,id_aset',
            'kode_barang' => 'required|string|max:255|unique:master_kode_barang,kode_barang,' . $id . ',id_kode_barang',
            'nama_kode_barang' => 'required|string|max:255',
        ]);

        $kodeBarang->update($validated);

        return redirect()->route('master-data.kode-barang.index')
            ->with('success', 'Kode Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kodeBarang = MasterKodeBarang::findOrFail($id);
        $kodeBarang->delete();

        return redirect()->route('master-data.kode-barang.index')
            ->with('success', 'Kode Barang berhasil dihapus.');
    }
}
