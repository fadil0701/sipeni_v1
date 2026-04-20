<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterRuangan;
use App\Models\MasterUnitKerja;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $ruangans = MasterRuangan::with('unitKerja')->latest()->paginate($perPage)->appends($request->query());
        return view('master.ruangan.index', compact('ruangans'));
    }

    public function create()
    {
        $unitKerjas = MasterUnitKerja::all();
        return view('master.ruangan.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'kode_ruangan' => 'required|string|max:255',
            'nama_ruangan' => 'required|string|max:255',
        ]);

        MasterRuangan::create($validated);

        return redirect()->route('master.ruangan.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $ruangan = MasterRuangan::with('unitKerja')->findOrFail($id);
        return view('master.ruangan.show', compact('ruangan'));
    }

    public function edit($id)
    {
        $ruangan = MasterRuangan::findOrFail($id);
        $unitKerjas = MasterUnitKerja::all();
        return view('master.ruangan.edit', compact('ruangan', 'unitKerjas'));
    }

    public function update(Request $request, $id)
    {
        $ruangan = MasterRuangan::findOrFail($id);

        $validated = $request->validate([
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'kode_ruangan' => 'required|string|max:255',
            'nama_ruangan' => 'required|string|max:255',
        ]);

        $ruangan->update($validated);

        return redirect()->route('master.ruangan.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $ruangan = MasterRuangan::findOrFail($id);
        $ruangan->delete();

        return redirect()->route('master.ruangan.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
