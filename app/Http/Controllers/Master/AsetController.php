<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterAset;

class AsetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $asets = MasterAset::latest()->paginate($perPage)->appends($request->query());
        return view('master-data.aset.index', compact('asets'));
    }

    public function create()
    {
        return view('master-data.aset.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_aset' => 'required|string|max:255|unique:master_aset,nama_aset',
        ]);

        MasterAset::create($validated);

        return redirect()->route('master-data.aset.index')
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show($id)
    {
        $aset = MasterAset::findOrFail($id);
        return view('master-data.aset.show', compact('aset'));
    }

    public function edit($id)
    {
        $aset = MasterAset::findOrFail($id);
        return view('master-data.aset.edit', compact('aset'));
    }

    public function update(Request $request, $id)
    {
        $aset = MasterAset::findOrFail($id);

        $validated = $request->validate([
            'nama_aset' => 'required|string|max:255|unique:master_aset,nama_aset,' . $id . ',id_aset',
        ]);

        $aset->update($validated);

        return redirect()->route('master-data.aset.index')
            ->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $aset = MasterAset::findOrFail($id);
        $aset->delete();

        return redirect()->route('master-data.aset.index')
            ->with('success', 'Aset berhasil dihapus.');
    }
}
