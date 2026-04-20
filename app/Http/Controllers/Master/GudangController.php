<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterGudang;
use App\Models\MasterUnitKerja;

class GudangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $gudangs = MasterGudang::with('unitKerja')->latest()->paginate($perPage)->appends($request->query());
        return view('master.gudang.index', compact('gudangs'));
    }

    public function create()
    {
        $unitKerjas = MasterUnitKerja::all();
        return view('master.gudang.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $rules = [
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'nama_gudang' => 'required|string|max:255',
            'jenis_gudang' => 'required|in:PUSAT,UNIT',
        ];

        // Jika jenis gudang adalah PUSAT, kategori wajib diisi
        if ($request->jenis_gudang === 'PUSAT') {
            $rules['kategori_gudang'] = 'required|in:ASET,PERSEDIAAN,FARMASI';
        }

        $validated = $request->validate($rules);

        // Jika UNIT, set kategori menjadi null
        if ($validated['jenis_gudang'] === 'UNIT') {
            $validated['kategori_gudang'] = null;
        }

        MasterGudang::create($validated);

        return redirect()->route('master.gudang.index')
            ->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $gudang = MasterGudang::with('unitKerja')->findOrFail($id);
        return view('master.gudang.show', compact('gudang'));
    }

    public function edit($id)
    {
        $gudang = MasterGudang::findOrFail($id);
        $unitKerjas = MasterUnitKerja::all();
        return view('master.gudang.edit', compact('gudang', 'unitKerjas'));
    }

    public function update(Request $request, $id)
    {
        $gudang = MasterGudang::findOrFail($id);

        $rules = [
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'nama_gudang' => 'required|string|max:255',
            'jenis_gudang' => 'required|in:PUSAT,UNIT',
        ];

        // Jika jenis gudang adalah PUSAT, kategori wajib diisi
        if ($request->jenis_gudang === 'PUSAT') {
            $rules['kategori_gudang'] = 'required|in:ASET,PERSEDIAAN,FARMASI';
        }

        $validated = $request->validate($rules);

        // Jika UNIT, set kategori menjadi null
        if ($validated['jenis_gudang'] === 'UNIT') {
            $validated['kategori_gudang'] = null;
        }

        $gudang->update($validated);

        return redirect()->route('master.gudang.index')
            ->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gudang = MasterGudang::findOrFail($id);
        $gudang->delete();

        return redirect()->route('master.gudang.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}
