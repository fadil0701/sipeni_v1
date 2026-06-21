<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\MasterPegawai;
use App\Helpers\PermissionHelper;
use App\Services\Audit\AuditLogService;
use App\Support\Admin\SuperAdminGuard;
use App\Support\SipeniPassword;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    /**
     * @return array<int, int>
     */
    private function validatedRoleIds($request): array
    {
        $ids = $request->input('role_ids', []);
        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id) => $id > 0)));
    }

    public function index(mixed $request = null)
    {
        $request ??= \call_user_func('\\app', 'request');
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $query = \call_user_func([User::class, 'with'], ['roles', 'pegawai.jabatan', 'pegawai.unitKerja'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->integer('role_id'));
            });
        }

        if ($request->boolean('needs_attention')) {
            $query->doesntHave('roles');
        }

        $users = $query->paginate($perPage)->appends($request->query());
        $roles = \call_user_func([Role::class, 'orderBy'], 'display_name')->get();

        $summary = [
            'total_users' => User::count(),
            'users_without_roles' => User::query()->doesntHave('roles')->count(),
        ];

        return \call_user_func('\\view', 'admin.users.index', compact('users', 'roles', 'summary'));
    }

    public function create(mixed $request = null)
    {
        $request ??= \call_user_func('\\app', 'request');
        $editor = $request->user();
        $roles = \call_user_func([Role::class, 'all']);

        $pegawais = \call_user_func([MasterPegawai::class, 'with'], 'unitKerja')
            ->orderBy('nama_pegawai')
            ->get();

        return \call_user_func('\\view', 'admin.users.create', compact('roles', 'pegawais'));
    }

    public function store(mixed $request = null)
    {
        $request ??= \call_user_func('\\app', 'request');
        $editor = $request->user();

        $roleIds = $this->validatedRoleIds($request);
        if ($error = SuperAdminGuard::validateRoleAssignment($editor, $roleIds)) {
            return \call_user_func('\\back')->withInput()->with('error', $error);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => SipeniPassword::requiredConfirmed(),
            'is_active' => 'nullable|boolean',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = \call_user_func([User::class, 'create'], [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \call_user_func('\\bcrypt', $validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::logCreate(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $user,
            attributes: $user->only(['name', 'email', 'is_active']),
            description: 'User account created',
            metadata: ['role_ids' => $this->validatedRoleIds($request)],
        );

        // Assign roles (multi-choice) — Spatie model_has_roles
        $user->syncUnifiedRoles($this->validatedRoleIds($request));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        PermissionHelper::forgetAccessibleMenusCacheForUser($user->id);

        return \call_user_func('\\redirect')->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(int|string $id)
    {
        $user = \call_user_func([User::class, 'with'], 'roles')->findOrFail($id);
        return \call_user_func('\\view', 'admin.users.show', compact('user'));
    }

    public function edit(int|string $id, mixed $request = null)
    {
        $request ??= \call_user_func('\\app', 'request');
        $editor = $request->user();
        $user = \call_user_func([User::class, 'with'], ['roles', 'pegawai'])->findOrFail($id);
        $roles = \call_user_func([Role::class, 'all']);

        $pegawais = \call_user_func([MasterPegawai::class, 'where'], function ($query) use ($id) {
            $query->whereDoesntHave('user')
                ->orWhereNull('user_id')
                ->orWhere('user_id', $id);
        })
            ->orderBy('nama_pegawai')
            ->get();

        return \call_user_func('\\view', 'admin.users.edit', compact('user', 'roles', 'pegawais'));
    }

    public function update(int|string $id, mixed $request = null)
    {
        $request ??= \call_user_func('\\app', 'request');

        $user = \call_user_func([User::class, 'findOrFail'], $id);
        $before = $user->only(['name', 'email', 'is_active']);
        $beforeRoleIds = $user->roles()->pluck('roles.id')->map(fn ($rid) => (int) $rid)->sort()->values()->all();

        $roleIds = $this->validatedRoleIds($request);
        if (SuperAdminGuard::wouldRemoveLastSuperAdministrator($user, $roleIds)) {
            return \call_user_func('\\redirect')->route('admin.users.edit', $id)
                ->with('error', 'Tidak dapat menghapus role Super Administrator dari user terakhir yang aktif.');
        }

        if ($error = SuperAdminGuard::validateRoleAssignment($request->user(), $roleIds)) {
            return \call_user_func('\\redirect')->route('admin.users.edit', $id)
                ->with('error', $error);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => SipeniPassword::optionalConfirmed(),
            'is_active' => 'nullable|boolean',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = \call_user_func('\\bcrypt', $validated['password']);
        }

        $user->update($updateData);
        $user->refresh();

        AuditLogService::logUpdate(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $user,
            old: $before,
            new: $user->only(['name', 'email', 'is_active']),
            description: 'User account updated',
        );

        // Update roles (multi-choice) — Spatie model_has_roles
        $user->syncUnifiedRoles($this->validatedRoleIds($request));
        $afterRoleIds = collect($this->validatedRoleIds($request))->sort()->values()->all();
        if ($beforeRoleIds !== $afterRoleIds) {
            AuditLogService::logAction(
                module: AuditLogService::MODULE_USER_MANAGEMENT,
                action: 'roles_assigned',
                description: 'User roles updated',
                entity: $user,
                old: ['role_ids' => $beforeRoleIds],
                new: ['role_ids' => $afterRoleIds],
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        PermissionHelper::forgetAccessibleMenusCacheForUser($user->id);

        return \call_user_func('\\redirect')->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(int|string $id)
    {
        $user = \call_user_func([User::class, 'findOrFail'], $id);

        // Prevent deletion of own account
        if ($user->id === \call_user_func('\\auth')->id()) {
            return \call_user_func('\\redirect')->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $uid = $user->id;
        $snapshot = $user->only(['name', 'email', 'is_active']);
        $user->syncUnifiedRoles([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->delete();

        AuditLogService::logDelete(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $user,
            snapshot: $snapshot,
            description: 'User account deleted',
        );

        PermissionHelper::forgetAccessibleMenusCacheForUser($uid);

        return \call_user_func('\\redirect')->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

}
