<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\WorkflowPermission;
use App\Models\WorkflowStatus;
use App\Helpers\PermissionHelper;
use App\Services\Audit\AuditLogService;
use App\Services\PermissionModuleService;
use App\Services\Rbac\RolePermissionResolver;
use App\Support\Admin\SystemRole;
use App\Support\AssignablePermissions;
use App\Support\PermissionModule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * @return array<int, string>
     */
    private function workflowAbilities(): array
    {
        return ['create', 'approve', 'reject', 'verify', 'process', 'finish'];
    }
    /**
     * Pengelompokan domain agar checklist role lebih mudah dipahami user non-teknis.
     * Value berisi prefix permission (route-style) yang akan dimasukkan ke bucket tersebut.
     */
    private const PERMISSION_DOMAIN_GROUPS = [
        'unit-kerja' => [
            'label' => 'Modul Khusus Unit Kerja (Pegawai/Kepala/Admin Gudang Unit)',
            'prefixes' => [
                'user.',
                'transaction.permintaan-barang.',
                'transaction.peminjaman-barang.',
                'transaction.pengembalian-barang.',
                'transaction.penerimaan-barang.',
                'transaction.retur',
                'maintenance.permintaan-pemeliharaan.',
                'inventory.data-stock.',
                'inventory.data-inventory.',
                'inventory.farmasi-kedaluwarsa.',
                'asset.register-aset.',
            ],
        ],
        'gudang-pusat' => [
            'label' => 'Modul Gudang Pusat (Pengurus Barang/Admin Gudang)',
            'prefixes' => [
                'transaction.draft-distribusi.',
                'transaction.compile-distribusi.',
                'transaction.distribusi.',
                'transaction.approval.',
                'inventory.',
                'reports.stock-gudang',
                'reports.kartu-stok',
                'master.gudang.',
                'master-data.data-barang.',
            ],
        ],
        'master-manajemen' => [
            'label' => 'Modul Master Manajemen',
            'prefixes' => [
                'master-manajemen.',
                'master.',
                'master-data.',
            ],
        ],
        'perencanaan-pengadaan-keuangan' => [
            'label' => 'Modul Perencanaan, Pengadaan, dan Keuangan',
            'prefixes' => [
                'planning.',
                'procurement.',
                'finance.',
            ],
        ],
        'maintenance' => [
            'label' => 'Modul Maintenance',
            'prefixes' => [
                'maintenance.',
            ],
        ],
        'laporan' => [
            'label' => 'Modul Laporan',
            'prefixes' => [
                'reports.',
            ],
        ],
        'admin-sistem' => [
            'label' => 'Modul Administrasi Sistem',
            'prefixes' => [
                'admin.',
            ],
        ],
    ];
    /**
     * Modul permission teknis yang tidak perlu tampil sebagai checklist role.
     * Permission untuk modul ini tetap bisa ada di database untuk kebutuhan internal.
     */
    private const HIDDEN_PERMISSION_MODULES = ['api'];
    private const ACTION_ALIAS_TO_CANONICAL = [
        'store' => 'create',
        'update' => 'edit',
        'delete' => 'destroy',
        'ajukan' => 'workflow',
        'mengetahui' => 'workflow',
        'verifikasi' => 'workflow',
        'kembalikan' => 'workflow',
        'approve' => 'workflow',
        'reject' => 'workflow',
        'disposisi' => 'workflow',
        'proses' => 'workflow',
        'kirim' => 'workflow',
        'terima' => 'workflow',
        'tolak' => 'workflow',
    ];
    private const RESOURCE_ALIAS_TO_CANONICAL = [
        'draft-distribusi' => 'distribusi',
        'compile-distribusi' => 'distribusi',
    ];
    private const ACTION_PRIORITY = [
        'index' => 1,
        'create' => 2,
        'show' => 3,
        'edit' => 4,
        'destroy' => 5,
        'workflow' => 6,
    ];

    /**
     * @param  Collection<string, Collection<int, Permission>>  $permissionsByModule
     * @param  array<int, int>  $checkedIds
     * @return array<int, array{module: string, label: string, items: Collection, checked_ids: array, all_checked: bool, some_checked: bool}>
     */
    private function buildPermissionGroups(Collection $permissionsByModule, array $checkedIds): array
    {
        $rawAll = $permissionsByModule->flatten(1)->values();
        if ($rawAll->isEmpty()) {
            return [];
        }

        $groupedByDomain = $rawAll->groupBy(function (Permission $permission): string {
            return $this->permissionDomainKey($permission->name, (string) ($permission->module ?? ''));
        });

        $sortedKeys = $this->sortDomainKeys($groupedByDomain->keys()->map(fn ($k) => (string) $k)->toArray());
        $groups = [];
        $rawItemByName = $rawAll->keyBy('name');

        foreach ($sortedKeys as $domainKey) {
            $rawItems = $groupedByDomain->get($domainKey);
            if (! $rawItems || $rawItems->isEmpty()) {
                continue;
            }

            // Kalau bucket fallback berbasis module teknis dan termasuk hidden, skip.
            if (str_starts_with($domainKey, 'module:')) {
                $module = str_replace('module:', '', $domainKey);
                if (in_array($module, self::HIDDEN_PERMISSION_MODULES, true)) {
                    continue;
                }
            }

            $items = $this->simplifyPermissionItems($rawItems);
            if (! $items || $items->isEmpty()) {
                continue;
            }

            $checked = [];
            foreach ($items as $permission) {
                if ($this->isPermissionChecked($permission, $checkedIds, $rawItemByName)) {
                    $checked[] = (int) $permission->id;
                }
            }

            $itemIds = $items->pluck('id')->map(fn ($id) => (int) $id)->toArray();
            $groups[] = [
                'module' => $domainKey,
                'label' => $this->permissionDomainLabel($domainKey),
                'items' => $items,
                'checked_ids' => $checked,
                'all_checked' => count($checked) === count($itemIds),
                'some_checked' => count($checked) > 0 && count($checked) < count($itemIds),
            ];
        }

        return $groups;
    }

    private function permissionDomainKey(string $permissionName, string $module): string
    {
        foreach (self::PERMISSION_DOMAIN_GROUPS as $domainKey => $meta) {
            foreach (($meta['prefixes'] ?? []) as $prefix) {
                if (str_starts_with($permissionName, (string) $prefix)) {
                    return $domainKey;
                }
            }
        }

        return 'module:'.$module;
    }

    private function permissionDomainLabel(string $domainKey): string
    {
        if (isset(self::PERMISSION_DOMAIN_GROUPS[$domainKey]['label'])) {
            return (string) self::PERMISSION_DOMAIN_GROUPS[$domainKey]['label'];
        }

        if (str_starts_with($domainKey, 'module:')) {
            $module = str_replace('module:', '', $domainKey);

            return PermissionModule::label($module);
        }

        return PermissionModule::label($domainKey);
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    private function sortDomainKeys(array $keys): array
    {
        $domainOrder = array_keys(self::PERMISSION_DOMAIN_GROUPS);
        usort($keys, function (string $a, string $b) use ($domainOrder): int {
            $ai = array_search($a, $domainOrder, true);
            $bi = array_search($b, $domainOrder, true);
            $aIdx = $ai === false ? 999 : $ai;
            $bIdx = $bi === false ? 999 : $bi;

            return $aIdx <=> $bIdx ?: strcmp($a, $b);
        });

        return $keys;
    }

    /**
     * Sederhanakan permission duplikat operasional:
     * - store -> create
     * - update -> edit
     * - delete -> destroy
     *
     * Bila permission canonical belum ada, alias tetap dipertahankan.
     *
     * @param  Collection<int, Permission>|null  $items
     * @return Collection<int, Permission>
     */
    private function simplifyPermissionItems(?Collection $items): Collection
    {
        if (! $items || $items->isEmpty()) {
            return collect();
        }

        $byCanonicalGroup = [];
        foreach ($items as $permission) {
            $groupKey = $this->permissionGroupKey($permission->name);
            if (! isset($byCanonicalGroup[$groupKey])) {
                $byCanonicalGroup[$groupKey] = [$permission];
                continue;
            }
            $byCanonicalGroup[$groupKey][] = $permission;
        }

        $picked = [];
        foreach ($byCanonicalGroup as $groupKey => $candidates) {
            $representative = $this->pickRepresentativePermission(collect($candidates));
            $groupedIds = collect($candidates)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

            // Metadata untuk kebutuhan checkbox grouped + expand saat submit.
            $representative->setAttribute('grouped_permission_ids', $groupedIds);
            $representative->setAttribute('grouped_permission_key', $groupKey);
            $representative->display_name = $this->groupDisplayName($representative->name);

            $picked[] = $representative;
        }

        return collect($picked)
            ->sortBy(fn (Permission $p) => sprintf('%010d:%s', (int) ($p->sort_order ?? 0), (string) $p->name))
            ->values();
    }

    /**
     * Jika permission ditampilkan dalam bentuk canonical, tetap anggap checked
     * bila role sebelumnya punya alias (mis. store saat canonical create ditampilkan).
     *
     * @param  array<int, int>  $checkedIds
     * @param  Collection<string, Permission>  $rawItemByName
     */
    private function isPermissionChecked(Permission $permission, array $checkedIds, Collection $rawItemByName): bool
    {
        $checkedSet = array_flip(array_map('intval', $checkedIds));
        $groupedIds = $permission->getAttribute('grouped_permission_ids');
        if (is_array($groupedIds) && $groupedIds !== []) {
            foreach ($groupedIds as $gid) {
                if (isset($checkedSet[(int) $gid])) {
                    return true;
                }
            }
            return false;
        }

        if (isset($checkedSet[(int) $permission->id])) {
            return true;
        }

        $aliasName = $this->aliasPermissionNameForCanonical($permission->name);
        $aliasPermission = $aliasName ? $rawItemByName->get($aliasName) : null;

        return $aliasPermission ? isset($checkedSet[(int) $aliasPermission->id]) : false;
    }

    private function canonicalPermissionName(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        if (count($parts) < 2) {
            return $permissionName;
        }

        if (count($parts) >= 3) {
            $resourceIdx = count($parts) - 2;
            $resource = $parts[$resourceIdx];
            $parts[$resourceIdx] = self::RESOURCE_ALIAS_TO_CANONICAL[$resource] ?? $resource;
        }

        $action = end($parts);
        $canonical = self::ACTION_ALIAS_TO_CANONICAL[$action] ?? $action;
        if ($canonical !== $action) {
            $parts[count($parts) - 1] = $canonical;
        }

        return implode('.', $parts);
    }

    private function aliasPermissionNameForCanonical(string $permissionName): ?string
    {
        $parts = explode('.', $permissionName);
        if (count($parts) < 2) {
            return null;
        }

        $action = end($parts);
        $canonicalToAlias = array_flip(self::ACTION_ALIAS_TO_CANONICAL);
        $alias = $canonicalToAlias[$action] ?? null;
        if (! $alias) {
            return null;
        }

        $parts[count($parts) - 1] = $alias;

        return implode('.', $parts);
    }

    private function permissionGroupKey(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        if (count($parts) >= 3) {
            $resourceIdx = count($parts) - 2;
            $resource = $parts[$resourceIdx];
            $canonicalResource = self::RESOURCE_ALIAS_TO_CANONICAL[$resource] ?? $resource;
            $module = $parts[0];

            return $module . '.' . $canonicalResource;
        }

        return $this->canonicalPermissionName($permissionName);
    }

    private function pickRepresentativePermission(Collection $candidates): Permission
    {
        return $candidates
            ->sortBy(function (Permission $permission): string {
                $parts = explode('.', $permission->name);
                $action = end($parts) ?: '';
                $canonicalAction = self::ACTION_ALIAS_TO_CANONICAL[$action] ?? $action;
                $priority = self::ACTION_PRIORITY[$canonicalAction] ?? 999;

                return sprintf('%04d:%010d:%s', $priority, (int) ($permission->sort_order ?? 0), (string) $permission->name);
            })
            ->first();
    }

    private function groupDisplayName(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        if (count($parts) >= 3) {
            $resourceIdx = count($parts) - 2;
            $resource = $parts[$resourceIdx];
            $canonicalResource = self::RESOURCE_ALIAS_TO_CANONICAL[$resource] ?? $resource;
            $resourceLabel = ucwords(str_replace(['-', '_'], ' ', $canonicalResource));

            return 'Akses ' . $resourceLabel;
        }

        return 'Akses ' . ucwords(str_replace(['-', '_'], ' ', $permissionName));
    }

    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);

        $query = Role::query()->withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('display_name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $roles = $query->latest()->paginate($perPage)->appends($request->query());

        $summary = [
            'total_roles' => Role::count(),
            'roles_in_use' => Role::query()->has('users')->count(),
        ];

        return view('admin.roles.index', compact('roles', 'summary'));
    }

    public function create(Request $request)
    {
        $editor = $request->user();
        $canDelegateAllPermissions = AssignablePermissions::editorMayAssignAll($editor);

        $permissionsByModule = AssignablePermissions::assignablePermissionsGroupedByModule($editor);
        $assignablePermissions = $permissionsByModule->flatten(1)->values();
        $permissionGroups = $this->buildPermissionGroups($permissionsByModule, []);
        $lockedPermissionGroups = [];

        $moduleService = app(PermissionModuleService::class);
        $simplifiedMatrix = $moduleService->buildMatrix($assignablePermissions, []);
        $groupedMatrix = $moduleService->buildGroupedMatrix($assignablePermissions, []);

        return view('admin.roles.create', compact(
            'permissionGroups', 'lockedPermissionGroups', 'canDelegateAllPermissions',
            'assignablePermissions', 'simplifiedMatrix', 'groupedMatrix'
        ));
    }

    public function store(Request $request)
    {
        $editor = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level_akses' => 'required|in:pusat,unit',
            'is_active' => 'nullable|boolean',
            'clone_from_role_id' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $submitted = $this->normalizePermissionIds($request->input('permissions', []));
        $submitted = $this->expandSubmittedPermissionIds($editor, $submitted);
        $this->assertEditorMayAssignPermissions($editor, $submitted);

        $role = Role::create([
            'name' => $validated['name'],
            'kode_role' => $validated['name'],
            'display_name' => $validated['display_name'],
            'nama_role' => $validated['display_name'],
            'level_akses' => $validated['level_akses'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['clone_from_role_id'])) {
            $baseIds = Role::query()->find($validated['clone_from_role_id'])?->permissions()->pluck('permissions.id')->all() ?? [];
            $submitted = array_values(array_unique(array_merge($baseIds, $submitted)));
        }

        $role->permissions()->sync($submitted);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionHelper::bumpAccessibleMenusCacheGeneration();

        AuditLogService::logCreate(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $role,
            attributes: $role->only(['name', 'display_name', 'level_akses', 'is_active', 'description']),
            description: 'Role created',
            metadata: ['permission_ids' => $submitted],
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function show($id)
    {
        $role = Role::with([
            'users',
            'permissions' => fn ($q) => $q->orderBy('module')->orderBy('sort_order')->orderBy('name'),
        ])->findOrFail($id);

        $permissionGroups = $this->buildPermissionGroups(
            $role->permissions->groupBy('module'),
            $role->permissions->pluck('id')->toArray()
        );

        $moduleService = app(PermissionModuleService::class);
        $simplifiedMatrix = $moduleService->buildMatrix(
            $role->permissions,
            $role->permissions->pluck('id')->map(fn ($id) => (int) $id)->toArray()
        );

        return view('admin.roles.show', compact('role', 'permissionGroups', 'simplifiedMatrix'));
    }

    public function edit(Request $request, $id)
    {
        $editor = $request->user();
        $canDelegateAllPermissions = AssignablePermissions::editorMayAssignAll($editor);

        $role = Role::with(['permissions'])->findOrFail($id);

        $assignableIds = AssignablePermissions::assignablePermissionIds($editor);

        $checkedIds = array_values(array_intersect(
            $role->permissions->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $assignableIds
        ));
        $totalChecked = count($checkedIds);

        $permissionsByModule = AssignablePermissions::assignablePermissionsGroupedByModule($editor);
        $permissionGroups = $this->buildPermissionGroups($permissionsByModule, $checkedIds);
        $assignablePermissions = $permissionsByModule->flatten(1)->values();

        $locked = AssignablePermissions::lockedPermissionsOnRole($editor, $role->permissions);
        $lockedPermissionGroups = [];
        if ($locked->isNotEmpty()) {
            $lockedPermissionGroups = $this->buildPermissionGroups(
                $locked->groupBy('module'),
                $locked->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
        }

        $statuses = WorkflowStatus::query()->orderBy('urutan')->get();
        $existingWorkflow = WorkflowPermission::query()
            ->where('role_id', $role->id)
            ->get()
            ->keyBy('workflow_status_id');
        $abilities = $this->workflowAbilities();

        $moduleService = app(PermissionModuleService::class);
        $simplifiedMatrix = $moduleService->buildMatrix($assignablePermissions, $checkedIds);
        $groupedMatrix = $moduleService->buildGroupedMatrix($assignablePermissions, $checkedIds);

        return view('admin.roles.edit', compact(
            'role',
            'permissionGroups',
            'lockedPermissionGroups',
            'totalChecked',
            'canDelegateAllPermissions',
            'assignablePermissions',
            'statuses',
            'existingWorkflow',
            'abilities',
            'simplifiedMatrix',
            'groupedMatrix'
        ));
    }

    public function update(Request $request, $id)
    {
        $editor = $request->user();
        $role = Role::with('permissions')->findOrFail($id);
        $before = $role->only(['name', 'display_name', 'level_akses', 'is_active', 'description']);
        $beforePermissionIds = $role->permissions->pluck('id')->map(fn ($pid) => (int) $pid)->sort()->values()->all();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level_akses' => 'required|in:pusat,unit',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $submitted = $this->normalizePermissionIds($request->input('permissions', []));
        $submitted = $this->expandSubmittedPermissionIds($editor, $submitted);
        $this->assertEditorMayAssignPermissions($editor, $submitted);

        $role->update([
            'name' => $validated['name'],
            'kode_role' => $validated['name'],
            'display_name' => $validated['display_name'],
            'nama_role' => $validated['display_name'],
            'level_akses' => $validated['level_akses'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'description' => $validated['description'] ?? null,
        ]);
        $role->refresh();

        AuditLogService::logUpdate(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $role,
            old: $before,
            new: $role->only(['name', 'display_name', 'level_akses', 'is_active', 'description']),
            description: 'Role updated',
        );

        $role->load('permissions');

        if (AssignablePermissions::editorMayAssignAll($editor)) {
            $role->permissions()->sync($submitted);
        } else {
            $lockedIds = AssignablePermissions::lockedPermissionsOnRole($editor, $role->permissions)
                ->pluck('id')
                ->map(fn ($i) => (int) $i)
                ->all();
            $role->permissions()->sync(array_values(array_unique(array_merge($submitted, $lockedIds))));
        }

        $afterPermissionIds = collect($submitted)->sort()->values()->all();
        if ($beforePermissionIds !== $afterPermissionIds) {
            AuditLogService::logAction(
                module: AuditLogService::MODULE_USER_MANAGEMENT,
                action: 'permission_matrix_updated',
                description: 'Role permission matrix updated',
                entity: $role,
                old: ['permission_ids' => $beforePermissionIds],
                new: ['permission_ids' => $afterPermissionIds],
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionHelper::bumpAccessibleMenusCacheGeneration();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (SystemRole::isProtected($role)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat dihapus.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role tidak dapat dihapus karena masih memiliki user.');
        }

        $role->load('permissions');
        $snapshot = $role->only(['name', 'display_name', 'level_akses', 'is_active', 'description']);
        $permissionIds = $role->permissions->pluck('id')->map(fn ($pid) => (int) $pid)->all();

        AuditLogService::logDelete(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $role,
            snapshot: array_merge($snapshot, ['permission_ids' => $permissionIds]),
            description: 'Role deleted',
        );

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }

    public function editWorkflowPermissions($id)
    {
        Role::query()->findOrFail($id);

        return redirect()
            ->route('admin.roles.edit', $id)
            ->with('info', 'Konfigurasi workflow per status belum aktif di UI. Kelola hak akses melalui permission matrix pada halaman edit role.');
    }

    public function updateWorkflowPermissions(Request $request, $id)
    {
        $role = Role::query()->findOrFail($id);
        $statuses = WorkflowStatus::query()->orderBy('urutan')->get();
        $abilities = $this->workflowAbilities();
        $matrix = (array) $request->input('matrix', []);

        foreach ($statuses as $status) {
            $row = (array) ($matrix[$status->id] ?? []);
            $payload = [];
            foreach ($abilities as $ability) {
                $payload['can_'.$ability] = isset($row[$ability]) && (string) $row[$ability] === '1';
            }

            WorkflowPermission::query()->updateOrCreate(
                [
                    'role_id' => $role->id,
                    'workflow_status_id' => $status->id,
                ],
                $payload
            );
        }

        return redirect()->route('admin.roles.edit', ['role' => $role->id, 'tab' => 'workflow'])
            ->with('success', 'Alur proses role berhasil diperbarui pada halaman Role Management.');
    }

    /**
     * @param  array<mixed>  $raw
     * @return array<int, int>
     */
    private function normalizePermissionIds(array $raw): array
    {
        $ids = array_map('intval', $raw);

        return array_values(array_unique(array_filter($ids, fn (int $id) => $id > 0)));
    }

    /**
     * Saat UI disederhanakan (mis. workflow), kembalikan ke set permission aktual.
     *
     * @param  array<int, int>  $submittedIds
     * @return array<int, int>
     */
    private function expandSubmittedPermissionIds(\App\Models\User $editor, array $submittedIds): array
    {
        if ($submittedIds === []) {
            return [];
        }

        $assignablePermissions = AssignablePermissions::assignablePermissionsGroupedByModule($editor)
            ->flatten(1)
            ->values();

        return app(RolePermissionResolver::class)->expand($submittedIds, $assignablePermissions);
    }

    /**
     * @param  array<int, int>  $permissionIds
     */
    private function assertEditorMayAssignPermissions(\App\Models\User $editor, array $permissionIds): void
    {
        if ($permissionIds === []) {
            return;
        }

        $allowed = array_flip(AssignablePermissions::assignablePermissionIds($editor));
        foreach ($permissionIds as $id) {
            if (! isset($allowed[(int) $id])) {
                abort(403, 'Anda tidak memiliki wewenang untuk memberikan salah satu permission yang dipilih.');
            }
        }
    }
}
