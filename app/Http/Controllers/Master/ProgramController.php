<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterProgram;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $programs = MasterProgram::orderBy('kode_program')->orderBy('nama_program')->paginate($perPage)->appends($request->query());
        return view('master.program.index', compact('programs'));
    }

    public function create()
    {
        return view('master.program.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_program' => 'required|string|max:100|unique:master_program,kode_program',
            'nama_program' => 'required|string|max:255|unique:master_program,nama_program',
        ]);

        MasterProgram::create($validated);

        return redirect()->route('master.program.index')
            ->with('success', 'Program berhasil ditambahkan.');
    }

    public function show($id)
    {
        $program = MasterProgram::findOrFail($id);
        return view('master.program.show', compact('program'));
    }

    public function edit($id)
    {
        $program = MasterProgram::findOrFail($id);
        return view('master.program.edit', compact('program'));
    }

    public function update(Request $request, $id)
    {
        $program = MasterProgram::findOrFail($id);

        $validated = $request->validate([
            'kode_program' => 'required|string|max:100|unique:master_program,kode_program,' . $id . ',id_program',
            'nama_program' => 'required|string|max:255|unique:master_program,nama_program,' . $id . ',id_program',
        ]);

        $program->update($validated);

        return redirect()->route('master.program.index')
            ->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $program = MasterProgram::findOrFail($id);
        $program->delete();

        return redirect()->route('master.program.index')
            ->with('success', 'Program berhasil dihapus.');
    }
}
