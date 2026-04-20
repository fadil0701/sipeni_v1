<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterSumberAnggaran;

class SumberAnggaranController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $sumberAnggarans = MasterSumberAnggaran::latest()->paginate($perPage)->appends($request->query());
        return view('master-data.sumber-anggaran.index', compact('sumberAnggarans'));
    }

    public function create()
    {
        return view('master-data.sumber-anggaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_anggaran' => 'required|string|max:255|unique:master_sumber_anggaran,nama_anggaran',
        ]);

        MasterSumberAnggaran::create($validated);

        return redirect()->route('master-data.sumber-anggaran.index')
            ->with('success', 'Sumber Anggaran berhasil ditambahkan.');
    }

    public function show($id)
    {
        $sumberAnggaran = MasterSumberAnggaran::findOrFail($id);
        return view('master-data.sumber-anggaran.show', compact('sumberAnggaran'));
    }

    public function edit($id)
    {
        $sumberAnggaran = MasterSumberAnggaran::findOrFail($id);
        return view('master-data.sumber-anggaran.edit', compact('sumberAnggaran'));
    }

    public function update(Request $request, $id)
    {
        $sumberAnggaran = MasterSumberAnggaran::findOrFail($id);

        $validated = $request->validate([
            'nama_anggaran' => 'required|string|max:255|unique:master_sumber_anggaran,nama_anggaran,' . $id . ',id_anggaran',
        ]);

        $sumberAnggaran->update($validated);

        return redirect()->route('master-data.sumber-anggaran.index')
            ->with('success', 'Sumber Anggaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $sumberAnggaran = MasterSumberAnggaran::findOrFail($id);
        $sumberAnggaran->delete();

        return redirect()->route('master-data.sumber-anggaran.index')
            ->with('success', 'Sumber Anggaran berhasil dihapus.');
    }
}
