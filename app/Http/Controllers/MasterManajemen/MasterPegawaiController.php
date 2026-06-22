<?php

namespace App\Http\Controllers\MasterManajemen;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Services\Audit\AuditLogService;
use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\User;
use App\Support\Http\SafeUserMessage;
use App\Support\SipeniPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MasterPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterPegawai::with(['unitKerja', 'masterJabatan', 'user.roles']);

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
            $query->where(function ($q) use ($search) {
                $q->where('nip_pegawai', 'like', "%{$search}%")
                    ->orWhere('nama_pegawai', 'like', "%{$search}%")
                    ->orWhere('email_pegawai', 'like', "%{$search}%");
            });
        }

        $perPage = PaginationHelper::getPerPage($request, 10);
        $pegawais = $query->latest()->paginate($perPage)->appends($request->query());
        $unitKerjas = MasterUnitKerja::all();
        $jabatans = MasterJabatan::orderBy('urutan')->get();

        return view('master-manajemen.master-pegawai.index', compact('pegawais', 'unitKerjas', 'jabatans'));
    }

    public function create()
    {
        $unitKerjas = MasterUnitKerja::all();
        $jabatans = MasterJabatan::orderBy('urutan')->get();
        $users = User::whereDoesntHave('pegawai')->get();

        return view('master-manajemen.master-pegawai.create', compact('unitKerjas', 'jabatans', 'users'));
    }

    public function store(Request $request)
    {
        $option = (string) $request->input('user_option', 'none');

        $validated = $request->validate([
            'nip_pegawai' => 'required|string|max:50|unique:master_pegawai,nip_pegawai',
            'nama_pegawai' => 'required|string|max:255',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_jabatan' => 'required|exists:master_jabatan,id_jabatan',
            'email_pegawai' => 'nullable|email|max:255|unique:master_pegawai,email_pegawai',
            'no_telp' => 'nullable|string|max:20',
            'user_option' => 'nullable|in:none,existing,new',
            'create_user' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'user_name' => 'nullable|string|max:255|required_if:user_option,new',
            'user_email' => 'nullable|email|max:255|required_if:user_option,new|unique:users,email',
            'user_password' => array_merge(
                [
                    Rule::requiredIf($option === 'new'),
                    'nullable',
                    'string',
                    'confirmed',
                ],
                [SipeniPassword::rule()],
            ),
        ]);

        if ($option === 'existing') {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
        }

        $emailPegawai = $this->resolveEmailPegawai($validated, $option);

        DB::beginTransaction();
        try {
            MasterJabatan::findOrFail($validated['id_jabatan']);

            $userId = null;

            if ($option === 'new' && $request->filled('create_user') && (string) $request->create_user === '1') {
                $user = User::create([
                    'name' => $validated['user_name'],
                    'email' => $validated['user_email'],
                    'password' => Hash::make($validated['user_password']),
                ]);
                $userId = $user->id;
            } elseif ($option === 'existing' && $request->filled('user_id')) {
                $userId = (int) $validated['user_id'];
            }

            $jabatanNama = MasterJabatan::query()
                ->whereKey($validated['id_jabatan'])
                ->value('nama_jabatan');

            MasterPegawai::create([
                'nip_pegawai' => $validated['nip_pegawai'],
                'nip' => $validated['nip_pegawai'],
                'nama_pegawai' => $validated['nama_pegawai'],
                'nama' => $validated['nama_pegawai'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'unit_kerja_id' => $validated['id_unit_kerja'],
                'id_jabatan' => $validated['id_jabatan'],
                'jabatan' => $jabatanNama,
                'email_pegawai' => $emailPegawai,
                'email' => $emailPegawai,
                'no_telp' => $validated['no_telp'] ?? null,
                'user_id' => $userId,
                'is_user' => $userId !== null,
                'status_pegawai' => 'aktif',
            ]);

            DB::commit();

            $message = 'Master Pegawai berhasil dibuat.';
            if ($userId !== null) {
                $message .= ' Atur role akun melalui User & Account Directory.';
            }

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating master pegawai: '.$e->getMessage(), ['exception' => $e]);

            return back()->withInput()->with('error', SafeUserMessage::fromThrowable($e, 'menyimpan data'));
        }
    }

    public function show(int|string $id)
    {
        $pegawai = MasterPegawai::with(['unitKerja', 'masterJabatan', 'user.roles'])->findOrFail($id);

        return view('master-manajemen.master-pegawai.show', compact('pegawai'));
    }

    public function edit(int|string $id)
    {
        $pegawai = MasterPegawai::with('user')->findOrFail($id);
        $unitKerjas = MasterUnitKerja::all();
        $jabatans = MasterJabatan::orderBy('urutan')->get();
        $users = User::whereDoesntHave('pegawai')->orWhere('id', $pegawai->user_id)->get();

        return view('master-manajemen.master-pegawai.edit', compact('pegawai', 'unitKerjas', 'jabatans', 'users'));
    }

    public function update(Request $request, int|string $id)
    {
        $pegawai = MasterPegawai::findOrFail($id);
        $before = $pegawai->only([
            'nip_pegawai', 'nama_pegawai', 'id_unit_kerja', 'id_jabatan',
            'email_pegawai', 'no_telp', 'user_id',
        ]);
        $option = (string) $request->input('user_option', $pegawai->user_id ? 'existing' : 'none');

        $validated = $request->validate([
            'nip_pegawai' => 'required|string|max:50|unique:master_pegawai,nip_pegawai,'.$id,
            'nama_pegawai' => 'required|string|max:255',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_jabatan' => 'required|exists:master_jabatan,id_jabatan',
            'email_pegawai' => 'nullable|email|max:255|unique:master_pegawai,email_pegawai,'.$id,
            'no_telp' => 'nullable|string|max:20',
            'user_option' => 'nullable|in:none,existing,new',
            'create_user' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'user_name' => 'nullable|string|max:255|required_if:user_option,new',
            'user_email' => 'nullable|email|max:255|required_if:user_option,new|unique:users,email,'.($pegawai->user_id ?? 'NULL'),
            'user_password' => array_merge(
                [
                    Rule::requiredIf($option === 'new' && ! $pegawai->user_id),
                    'nullable',
                    'string',
                    'confirmed',
                ],
                [SipeniPassword::rule()],
            ),
        ]);

        if ($option === 'existing') {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
        }

        $emailPegawai = $this->resolveEmailPegawai($validated, $option, $pegawai->user_id);

        DB::beginTransaction();
        try {
            MasterJabatan::findOrFail($validated['id_jabatan']);

            $userId = $pegawai->user_id;

            if ($option === 'none') {
                $userId = null;
            } elseif ($option === 'new' && $request->filled('create_user') && (string) $request->create_user === '1') {
                if ($pegawai->user_id) {
                    $user = User::findOrFail($pegawai->user_id);
                    $user->update([
                        'name' => $validated['user_name'],
                        'email' => $validated['user_email'],
                    ]);
                    if ($request->filled('user_password')) {
                        $user->update(['password' => Hash::make($validated['user_password'])]);
                    }
                    $userId = $user->id;
                } else {
                    $user = User::create([
                        'name' => $validated['user_name'],
                        'email' => $validated['user_email'],
                        'password' => Hash::make($validated['user_password']),
                    ]);
                    $userId = $user->id;
                }
            } elseif ($option === 'existing' && $request->filled('user_id')) {
                $userId = (int) $validated['user_id'];
            }

            $jabatanNama = MasterJabatan::query()
                ->whereKey($validated['id_jabatan'])
                ->value('nama_jabatan');

            $pegawai->update([
                'nip_pegawai' => $validated['nip_pegawai'],
                'nip' => $validated['nip_pegawai'],
                'nama_pegawai' => $validated['nama_pegawai'],
                'nama' => $validated['nama_pegawai'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'unit_kerja_id' => $validated['id_unit_kerja'],
                'id_jabatan' => $validated['id_jabatan'],
                'jabatan' => $jabatanNama,
                'email_pegawai' => $emailPegawai,
                'email' => $emailPegawai,
                'no_telp' => $validated['no_telp'] ?? null,
                'user_id' => $userId,
                'is_user' => $userId !== null,
            ]);
            $pegawai->refresh();

            AuditLogService::logUpdate(
                module: AuditLogService::MODULE_MASTER_DATA,
                entity: $pegawai,
                old: $before,
                new: $pegawai->only([
                    'nip_pegawai', 'nama_pegawai', 'id_unit_kerja', 'id_jabatan',
                    'email_pegawai', 'no_telp', 'user_id',
                ]),
                description: 'Master pegawai updated',
            );

            DB::commit();

            $message = 'Master Pegawai berhasil diperbarui.';
            if ($userId !== null) {
                $message .= ' Atur role akun melalui User & Account Directory.';
            }

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating master pegawai: '.$e->getMessage(), ['exception' => $e]);

            return back()->withInput()->with('error', SafeUserMessage::fromThrowable($e, 'memperbarui data'));
        }
    }

    public function destroy(int|string $id)
    {
        $pegawai = MasterPegawai::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($pegawai->user_id) {
                $pegawai->update(['user_id' => null]);
            }

            $pegawai->delete();

            DB::commit();

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('success', 'Master Pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting master pegawai: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('master-manajemen.master-pegawai.index')
                ->with('error', SafeUserMessage::fromThrowable($e, 'menghapus data'));
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveEmailPegawai(array $validated, string $userOption, ?int $linkedUserId = null): string
    {
        $email = trim((string) ($validated['email_pegawai'] ?? ''));

        if ($email === '' && $userOption === 'new') {
            $email = trim((string) ($validated['user_email'] ?? ''));
        }

        if ($email === '' && $userOption === 'existing') {
            $userId = (int) ($validated['user_id'] ?? $linkedUserId ?? 0);
            if ($userId > 0) {
                $email = trim((string) (User::query()->whereKey($userId)->value('email') ?? ''));
            }
        }

        if ($email === '') {
            throw ValidationException::withMessages([
                'email_pegawai' => 'Email pegawai wajib diisi. Jika membuat akun baru, isi Email Pegawai atau Email User.',
            ]);
        }

        return $email;
    }
}
