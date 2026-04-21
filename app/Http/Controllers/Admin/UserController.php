<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\MasterPegawai;
use App\Models\Module;
use App\Helpers\PermissionHelper;
use App\Support\AssignablePermissions;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $users = User::with('roles')->latest()->paginate($perPage)->appends($request->query());
        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $editor = $request->user();
        $roles = Role::all();
        $allowedModuleNames = AssignablePermissions::assignableModuleNamesForUserForm($editor);
        $modules = Module::orderBy('sort_order')
            ->get()
            ->filter(fn (Module $m) => in_array($m->name, $allowedModuleNames, true))
            ->values();
        $canDelegateAllPermissions = AssignablePermissions::editorMayAssignAll($editor);

        $pegawais = MasterPegawai::with('unitKerja')
            ->orderBy('nama_pegawai')
            ->get();

        $lockedModules = collect();

        return view('admin.users.create', compact('roles', 'pegawais', 'modules', 'lockedModules', 'canDelegateAllPermissions'));
    }

    public function store(Request $request)
    {
        $this->assertEditorMayAssignModules($request->user(), $request->input('modules', []));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role
        $user->roles()->attach($validated['role_id']);

        // Assign modules
        if ($request->has('modules')) {
            $user->modules()->sync($request->modules);
        }

        PermissionHelper::forgetAccessibleMenusCacheForUser($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit(Request $request, $id)
    {
        $editor = $request->user();
        $user = User::with(['roles', 'modules'])->findOrFail($id);
        $roles = Role::all();
        $allowedModuleNames = AssignablePermissions::assignableModuleNamesForUserForm($editor);
        $modules = Module::orderBy('sort_order')
            ->get()
            ->filter(fn (Module $m) => in_array($m->name, $allowedModuleNames, true))
            ->values();
        $canDelegateAllPermissions = AssignablePermissions::editorMayAssignAll($editor);

        $userModuleNames = $user->modules->pluck('name')->all();
        $lockedModuleNames = array_values(array_diff($userModuleNames, $allowedModuleNames));
        $lockedModules = $lockedModuleNames === []
            ? collect()
            : Module::whereIn('name', $lockedModuleNames)->orderBy('sort_order')->get();

        // Ambil pegawai yang belum punya user atau pegawai yang sudah terhubung dengan user ini
        $pegawais = MasterPegawai::where(function($query) use ($id) {
                $query->whereDoesntHave('user')
                      ->orWhereNull('user_id')
                      ->orWhere('user_id', $id);
            })
            ->orderBy('nama_pegawai')
            ->get();
        return view('admin.users.edit', compact('user', 'roles', 'pegawais', 'modules', 'lockedModules', 'canDelegateAllPermissions'));
    }

    public function update(Request $request, $id)
    {
        $this->assertEditorMayAssignModules($request->user(), $request->input('modules', []));

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,name',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update role
        $user->roles()->sync([$validated['role_id']]);

        $user->load('modules');
        $submittedModules = (array) $request->input('modules', []);

        if (AssignablePermissions::editorMayAssignAll($request->user())) {
            if ($request->has('modules')) {
                $user->modules()->sync($submittedModules);
            } else {
                $user->modules()->detach();
            }
        } else {
            $allowed = array_flip(AssignablePermissions::assignableModuleNamesForUserForm($request->user()));
            $lockedNames = $user->modules->pluck('name')->filter(fn ($n) => ! isset($allowed[(string) $n]))->values()->all();
            $user->modules()->sync(array_values(array_unique(array_merge($submittedModules, $lockedNames))));
        }

        PermissionHelper::forgetAccessibleMenusCacheForUser($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deletion of own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $uid = $user->id;
        $user->roles()->detach();
        $user->delete();

        PermissionHelper::forgetAccessibleMenusCacheForUser($uid);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * @param  array<mixed>  $moduleNames
     */
    private function assertEditorMayAssignModules(\App\Models\User $editor, array $moduleNames): void
    {
        if (! is_array($moduleNames)) {
            return;
        }

        $allowed = array_flip(AssignablePermissions::assignableModuleNamesForUserForm($editor));
        foreach ($moduleNames as $name) {
            if ($name === null || $name === '') {
                continue;
            }
            if (! isset($allowed[(string) $name])) {
                abort(403, 'Anda tidak dapat memberikan modul menu yang dipilih.');
            }
        }
    }
}
