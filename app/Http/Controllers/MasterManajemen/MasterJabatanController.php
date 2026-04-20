<?php

namespace App\Http\Controllers\MasterManajemen;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterJabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MasterJabatan::with('role')->withCount('pegawai')->orderBy('urutan', 'asc');

        // Filter berdasarkan role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_jabatan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $jabatans = $query->paginate($perPage)->appends($request->query());
        $roles = Role::orderBy('name')->get();

        return view('master-manajemen.master-jabatan.index', compact('jabatans', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('master-manajemen.master-jabatan.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'urutan' => 'required|integer|min:1',
            'role_id' => 'nullable|exists:roles,id',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            MasterJabatan::create($validated);
            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('success', 'Jabatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jabatan = MasterJabatan::with('role', 'pegawai.unitKerja')->findOrFail($id);
        return view('master-manajemen.master-jabatan.show', compact('jabatan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);
        $roles = Role::orderBy('name')->get();
        return view('master-manajemen.master-jabatan.edit', compact('jabatan', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);

        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'urutan' => 'required|integer|min:1',
            'role_id' => 'nullable|exists:roles,id',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $jabatan->update($validated);
            return redirect()->route('master-manajemen.master-jabatan.index')
                ->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);

        // Cek apakah ada pegawai yang menggunakan jabatan ini
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
                ->with('error', 'Gagal menghapus jabatan: ' . $e->getMessage());
        }
    }
}
