<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterUnitKerja;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $unitKerjas = MasterUnitKerja::latest()->paginate($perPage)->appends($request->query());
        return view('master.unit-kerja.index', compact('unitKerjas'));
    }

    public function create()
    {
        return view('master.unit-kerja.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_unit_kerja' => 'required|string|max:255|unique:master_unit_kerja,kode_unit_kerja',
            'nama_unit_kerja' => 'required|string|max:255',
        ]);

        MasterUnitKerja::create($validated);

        return redirect()->route('master.unit-kerja.index')
            ->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    public function show($id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);
        return view('master.unit-kerja.show', compact('unitKerja'));
    }

    public function edit($id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);
        return view('master.unit-kerja.edit', compact('unitKerja'));
    }

    public function update(Request $request, $id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);

        $validated = $request->validate([
            'kode_unit_kerja' => 'required|string|max:255|unique:master_unit_kerja,kode_unit_kerja,' . $id . ',id_unit_kerja',
            'nama_unit_kerja' => 'required|string|max:255',
        ]);

        $unitKerja->update($validated);

        return redirect()->route('master.unit-kerja.index')
            ->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);
        $unitKerja->delete();

        return redirect()->route('master.unit-kerja.index')
            ->with('success', 'Unit Kerja berhasil dihapus.');
    }
}

