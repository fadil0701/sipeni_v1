<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterSatuan;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $satuans = MasterSatuan::latest()->paginate($perPage)->appends($request->query());
        return view('master-data.satuan.index', compact('satuans'));
    }

    public function create()
    {
        return view('master-data.satuan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:255|unique:master_satuan,nama_satuan',
        ]);

        MasterSatuan::create($validated);

        return redirect()->route('master-data.satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $satuan = MasterSatuan::findOrFail($id);
        return view('master-data.satuan.show', compact('satuan'));
    }

    public function edit($id)
    {
        $satuan = MasterSatuan::findOrFail($id);
        return view('master-data.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, $id)
    {
        $satuan = MasterSatuan::findOrFail($id);

        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:255|unique:master_satuan,nama_satuan,' . $id . ',id_satuan',
        ]);

        $satuan->update($validated);

        return redirect()->route('master-data.satuan.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $satuan = MasterSatuan::findOrFail($id);
        $satuan->delete();

        return redirect()->route('master-data.satuan.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}
