<?php

namespace App\Http\Controllers\MasterManajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\MasterJabatan;
use App\Models\User;
use App\Models\Role;

class MasterPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterPegawai::with(['unitKerja', 'jabatan', 'user.roles']);

        // Filters
        if ($request->filled('unit_kerja')) {
            $query->where('id_unit_kerja', $request->unit_kerja);
        }

        if ($request->filled('jabatan')) {
            $query->where('id_jabatan', $request->jabatan);
        }

        if ($request->filled('has_user')) {
            if ($request->has_user == 'yes') {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nip_pegawai', 'like', "%{$search}%")
                  ->orWhere('nama_pegawai', 'like', "%{$search}%")
                  ->orWhere('email_pegawai', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $pegawais = $query->latest()->paginate($perPage)->appends($request->query());
        $unitKerjas = MasterUnitKerja::all();
        $jabatans = MasterJabatan::all();

        return view('master-manajemen.master-pegawai.index', compact('pegawais', 'unitKerjas', 'jabatans'));
    }

    public function create()
    {
        $unitKerjas = MasterUnitKerja::all();
        $jabatans = MasterJabatan::with('role')->orderBy('urutan')->get();
        $users = User::whereDoesntHave('pegawai')->get(); // Users yang belum punya pegawai

        // Prepare jabatan data for JavaScript
        $jabatanData = $jabatans->mapWithKeys(function($jabatan) {
            return [$jabatan->id_jabatan => [
                'nama' => $jabatan->nama_jabatan,
                'role_id' => $jabatan->role_id,
                'role_name' => $jabatan->role ? $jabatan->role->display_name : null,
                'role_description' => $jabatan->role ? $jabatan->role->description : null
            ]];
        });

        return view('master-manajemen.master-pegawai.create', compact('unitKerjas', 'jabatans', 'users', 'jabatanData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip_pegawai' => 'required|string|max:50|unique:master_pegawai,nip_pegawai',
            'nama_pegawai' => 'required|string|max:255',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_jabatan' => 'required|exists:master_jabatan,id_jabatan',
            'email_pegawai' => 'nullable|email|max:255|unique:master_pegawai,email_pegawai',
            'no_telp' => 'nullable|string|max:20',
            'create_user' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'user_name' => 'nullable|string|max:255|required_if:create_user,1',
            'user_email' => 'nullable|email|max:255|required_if:create_user,1|unique:users,email',
            'user_password' => 'nullable|string|min:8|required_if:create_user,1|confirmed',
        ]);

        \DB::beginTransaction();
        try {
            // Get jabatan untuk mengambil role
            $jabatan = MasterJabatan::with('role')->findOrFail($validated['id_jabatan']);
            
            // Create user jika diperlukan
            $userId = null;
            if ($request->filled('create_user') && $request->create_user == '1') {
                $user = User::create([
                    'name' => $validated['user_name'],
                    'email' => $validated['user_email'],
                    'password' => Hash::make($validated['user_password']),
                ]);

                // Assign role dari jabatan
                if ($jabatan->role_id) {
                    $user->roles()->attach($jabatan->role_id);
                }

                $userId = $user->id;
            } elseif ($request->filled('user_id')) {
                $userId = $validated['user_id'];
                
                // Update role user sesuai jabatan baru
                $user = User::find($userId);
                if ($user && $jabatan->role_id) {
                    // Sync role dari jabatan (replace semua role dengan role dari jabatan)
                    $user->roles()->sync([$jabatan->role_id]);
                }
            }

            // Create pegawai
            $pegawai = MasterPegawai::create([
                'nip_pegawai' => $validated['nip_pegawai'],
                'nama_pegawai' => $validated['nama_pegawai'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_jabatan' => $validated['id_jabatan'],
                'email_pegawai' => $validated['email_pegawai'] ?? null,
                'no_telp' => $validated['no_telp'] ?? null,
                'user_id' => $userId,
            ]);

            \DB::commit();

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('success', 'Master Pegawai berhasil dibuat.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating master pegawai: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pegawai = MasterPegawai::with(['unitKerja', 'jabatan', 'user.roles'])->findOrFail($id);
        return view('master-manajemen.master-pegawai.show', compact('pegawai'));
    }

    public function edit($id)
    {
        $pegawai = MasterPegawai::with('user.roles')->findOrFail($id);
        $unitKerjas = MasterUnitKerja::all();
        $jabatans = MasterJabatan::with('role')->orderBy('urutan')->get();
        $users = User::whereDoesntHave('pegawai')->orWhere('id', $pegawai->user_id)->get();

        // Prepare jabatan data for JavaScript
        $jabatanData = $jabatans->mapWithKeys(function($jabatan) {
            return [$jabatan->id_jabatan => [
                'nama' => $jabatan->nama_jabatan,
                'role_id' => $jabatan->role_id,
                'role_name' => $jabatan->role ? $jabatan->role->display_name : null,
                'role_description' => $jabatan->role ? $jabatan->role->description : null
            ]];
        });

        return view('master-manajemen.master-pegawai.edit', compact('pegawai', 'unitKerjas', 'jabatans', 'users', 'jabatanData'));
    }

    public function update(Request $request, $id)
    {
        $pegawai = MasterPegawai::findOrFail($id);

        $validated = $request->validate([
            'nip_pegawai' => 'required|string|max:50|unique:master_pegawai,nip_pegawai,' . $id,
            'nama_pegawai' => 'required|string|max:255',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_jabatan' => 'required|exists:master_jabatan,id_jabatan',
            'email_pegawai' => 'nullable|email|max:255|unique:master_pegawai,email_pegawai,' . $id,
            'no_telp' => 'nullable|string|max:20',
            'create_user' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'user_name' => 'nullable|string|max:255|required_if:create_user,1',
            'user_email' => 'nullable|email|max:255|required_if:create_user,1|unique:users,email,' . ($pegawai->user_id ?? 'NULL'),
            'user_password' => 'nullable|string|min:8|required_if:create_user,1' . ($request->filled('user_password') ? '|confirmed' : ''),
        ]);

        \DB::beginTransaction();
        try {
            // Get jabatan untuk mengambil role
            $jabatan = MasterJabatan::with('role')->findOrFail($validated['id_jabatan']);
            
            // Handle user creation/update
            $userId = $pegawai->user_id;
            
            if ($request->filled('create_user') && $request->create_user == '1') {
                if ($pegawai->user_id) {
                    // Update existing user
                    $user = User::find($pegawai->user_id);
                    $user->update([
                        'name' => $validated['user_name'],
                        'email' => $validated['user_email'],
                    ]);

                    if ($request->filled('user_password')) {
                        $user->update(['password' => Hash::make($validated['user_password'])]);
                    }

                    // Update role sesuai jabatan baru
                    if ($jabatan->role_id) {
                        $user->roles()->sync([$jabatan->role_id]);
                    }
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $validated['user_name'],
                        'email' => $validated['user_email'],
                        'password' => Hash::make($validated['user_password']),
                    ]);

                    // Assign role dari jabatan
                    if ($jabatan->role_id) {
                        $user->roles()->attach($jabatan->role_id);
                    }

                    $userId = $user->id;
                }
            } elseif ($request->filled('user_id')) {
                $userId = $validated['user_id'];
                
                // Update role user sesuai jabatan baru
                $user = User::find($userId);
                if ($user && $jabatan->role_id) {
                    $user->roles()->sync([$jabatan->role_id]);
                }
            } elseif ($request->filled('remove_user') && $request->remove_user == '1') {
                $userId = null;
            } else {
                // Jika hanya mengubah jabatan tanpa mengubah user, update role sesuai jabatan baru
                if ($pegawai->user_id && $jabatan->role_id) {
                    $user = User::find($pegawai->user_id);
                    if ($user) {
                        $user->roles()->sync([$jabatan->role_id]);
                    }
                }
            }

            // Update pegawai
            $pegawai->update([
                'nip_pegawai' => $validated['nip_pegawai'],
                'nama_pegawai' => $validated['nama_pegawai'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_jabatan' => $validated['id_jabatan'],
                'email_pegawai' => $validated['email_pegawai'] ?? null,
                'no_telp' => $validated['no_telp'] ?? null,
                'user_id' => $userId,
            ]);

            \DB::commit();

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('success', 'Master Pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating master pegawai: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $pegawai = MasterPegawai::findOrFail($id);

        // Jangan hapus jika sudah digunakan di transaksi
        // Bisa ditambahkan pengecekan relasi jika diperlukan

        \DB::beginTransaction();
        try {
            // Set user_id ke null dulu sebelum hapus (jika ada)
            if ($pegawai->user_id) {
                $pegawai->update(['user_id' => null]);
            }

            $pegawai->delete();

            \DB::commit();

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('success', 'Master Pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error deleting master pegawai: ' . $e->getMessage());
            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
