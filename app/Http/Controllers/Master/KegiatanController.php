<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterKegiatan;
use App\Models\MasterProgram;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $kegiatans = MasterKegiatan::with('program')->orderBy('kode_kegiatan')->orderBy('nama_kegiatan')->paginate($perPage)->appends($request->query());
        return view('master.kegiatan.index', compact('kegiatans'));
    }

    public function create()
    {
        $programs = MasterProgram::orderBy('kode_program')->orderBy('nama_program')->get();
        return view('master.kegiatan.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_program' => 'required|exists:master_program,id_program',
            'kode_kegiatan' => 'required|string|max:100|unique:master_kegiatan,kode_kegiatan',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        MasterKegiatan::create($validated);

        return redirect()->route('master.kegiatan.index')
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $kegiatan = MasterKegiatan::with('program')->findOrFail($id);
        return view('master.kegiatan.show', compact('kegiatan'));
    }

    public function edit($id)
    {
        $kegiatan = MasterKegiatan::findOrFail($id);
        $programs = MasterProgram::orderBy('kode_program')->orderBy('nama_program')->get();
        return view('master.kegiatan.edit', compact('kegiatan', 'programs'));
    }

    public function update(Request $request, $id)
    {
        $kegiatan = MasterKegiatan::findOrFail($id);

        $validated = $request->validate([
            'id_program' => 'required|exists:master_program,id_program',
            'kode_kegiatan' => 'required|string|max:100|unique:master_kegiatan,kode_kegiatan,' . $id . ',id_kegiatan',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $kegiatan->update($validated);

        return redirect()->route('master.kegiatan.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kegiatan = MasterKegiatan::findOrFail($id);
        $kegiatan->delete();

        return redirect()->route('master.kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }
}
