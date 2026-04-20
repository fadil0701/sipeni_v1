<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterSubKegiatan;
use App\Models\MasterKegiatan;

class SubKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $subKegiatans = MasterSubKegiatan::with('kegiatan.program')->latest()->paginate($perPage)->appends($request->query());
        return view('master.sub-kegiatan.index', compact('subKegiatans'));
    }

    public function create()
    {
        $kegiatans = MasterKegiatan::with('program')->get();
        return view('master.sub-kegiatan.create', compact('kegiatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kegiatan' => 'required|exists:master_kegiatan,id_kegiatan',
            'kode_sub_kegiatan' => 'required|string|max:255',
            'nama_sub_kegiatan' => 'required|string|max:255',
        ]);

        MasterSubKegiatan::create($validated);

        return redirect()->route('master.sub-kegiatan.index')
            ->with('success', 'Sub Kegiatan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $subKegiatan = MasterSubKegiatan::with('kegiatan.program')->findOrFail($id);
        return view('master.sub-kegiatan.show', compact('subKegiatan'));
    }

    public function edit($id)
    {
        $subKegiatan = MasterSubKegiatan::findOrFail($id);
        $kegiatans = MasterKegiatan::with('program')->get();
        return view('master.sub-kegiatan.edit', compact('subKegiatan', 'kegiatans'));
    }

    public function update(Request $request, $id)
    {
        $subKegiatan = MasterSubKegiatan::findOrFail($id);

        $validated = $request->validate([
            'id_kegiatan' => 'required|exists:master_kegiatan,id_kegiatan',
            'kode_sub_kegiatan' => 'required|string|max:255',
            'nama_sub_kegiatan' => 'required|string|max:255',
        ]);

        $subKegiatan->update($validated);

        return redirect()->route('master.sub-kegiatan.index')
            ->with('success', 'Sub Kegiatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $subKegiatan = MasterSubKegiatan::findOrFail($id);
        $subKegiatan->delete();

        return redirect()->route('master.sub-kegiatan.index')
            ->with('success', 'Sub Kegiatan berhasil dihapus.');
    }
}
