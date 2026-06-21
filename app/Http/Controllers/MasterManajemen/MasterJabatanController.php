<?php

namespace App\Http\Controllers\MasterManajemen;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use Illuminate\Http\Request;

class MasterJabatanController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterJabatan::withCount('pegawai')->orderBy('urutan', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_jabatan', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $perPage = PaginationHelper::getPerPage($request, 10);
        $jabatans = $query->paginate($perPage)->appends($request->query());

        return view('master-manajemen.master-jabatan.index', compact('jabatans'));
    }

    public function create()
    {
        return view('master-manajemen.master-jabatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            if (empty($validated['urutan'])) {
                $validated['urutan'] = ((int) MasterJabatan::query()->max('urutan')) + 1;
            }

            MasterJabatan::create([
                'nama_jabatan' => $validated['nama_jabatan'],
                'urutan' => $validated['urutan'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('success', 'Jabatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan jabatan: '.$e->getMessage());
        }
    }

    public function show(string $id)
    {
        $jabatan = MasterJabatan::with(['pegawai.unitKerja', 'pegawai.user.roles'])->findOrFail($id);

        return view('master-manajemen.master-jabatan.show', compact('jabatan'));
    }

    public function edit(string $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);

        return view('master-manajemen.master-jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, string $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);

        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $jabatan->update([
                'nama_jabatan' => $validated['nama_jabatan'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jabatan: '.$e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);

        if ($jabatan->pegawai()->count() > 0) {
            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh pegawai.');
        }

        try {
            $jabatan->delete();

            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('error', 'Gagal menghapus jabatan: '.$e->getMessage());
        }
    }
}
