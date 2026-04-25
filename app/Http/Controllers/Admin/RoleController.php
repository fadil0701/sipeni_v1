<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Helpers\PermissionHelper;
use App\Support\AssignablePermissions;
use App\Support\PermissionModule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RoleController extends Controller
{
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
        $sortedKeys = PermissionModule::sortModuleKeys($permissionsByModule->keys()->map(fn ($k) => (string) $k)->toArray());
        $groups = [];

        foreach ($sortedKeys as $module) {
            if (in_array($module, self::HIDDEN_PERMISSION_MODULES, true)) {
                continue;
            }
            $rawItems = $permissionsByModule->get($module);
            $items = $this->simplifyPermissionItems($rawItems);
            if (! $items || $items->isEmpty()) {
                continue;
            }

            $rawItemByName = $rawItems?->keyBy('name') ?? collect();
            $checked = [];
            foreach ($items as $permission) {
                if ($this->isPermissionChecked($permission, $checkedIds, $rawItemByName)) {
                    $checked[] = (int) $permission->id;
                }
            }

            $itemIds = $items->pluck('id')->map(fn ($id) => (int) $id)->toArray();
            $groups[] = [
                'module' => $module,
                'label' => PermissionModule::label($module),
                'items' => $items,
                'checked_ids' => $checked,
                'all_checked' => count($checked) === count($itemIds),
                'some_checked' => count($checked) > 0 && count($checked) < count($itemIds),
            ];
        }

        return $groups;
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
        $query = Role::withCount('users')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('display_name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $roles = $query->paginate($perPage)->appends($request->query());

        return view('admin.roles.index', compact('roles'));
    }

    public function create(Request $request)
    {
        $editor = $request->user();
        $canDelegateAllPermissions = AssignablePermissions::editorMayAssignAll($editor);

        $permissionsByModule = AssignablePermissions::assignablePermissionsGroupedByModule($editor);
        $permissionGroups = $this->buildPermissionGroups($permissionsByModule, []);
        $lockedPermissionGroups = [];

        return view('admin.roles.create', compact('permissionGroups', 'lockedPermissionGroups', 'canDelegateAllPermissions'));
    }

    public function store(Request $request)
    {
        $editor = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $submitted = $this->normalizePermissionIds($request->input('permissions', []));
        $submitted = $this->expandSubmittedPermissionIds($editor, $submitted);
        $this->assertEditorMayAssignPermissions($editor, $submitted);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($submitted);
        PermissionHelper::bumpAccessibleMenusCacheGeneration();

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

        return view('admin.roles.show', compact('role', 'permissionGroups'));
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

        $locked = AssignablePermissions::lockedPermissionsOnRole($editor, $role->permissions);
        $lockedPermissionGroups = [];
        if ($locked->isNotEmpty()) {
            $lockedPermissionGroups = $this->buildPermissionGroups(
                $locked->groupBy('module'),
                $locked->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
        }

        return view('admin.roles.edit', compact(
            'role',
            'permissionGroups',
            'lockedPermissionGroups',
            'totalChecked',
            'canDelegateAllPermissions'
        ));
    }

    public function update(Request $request, $id)
    {
        $editor = $request->user();
        $role = Role::with('permissions')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $submitted = $this->normalizePermissionIds($request->input('permissions', []));
        $submitted = $this->expandSubmittedPermissionIds($editor, $submitted);
        $this->assertEditorMayAssignPermissions($editor, $submitted);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

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

        PermissionHelper::bumpAccessibleMenusCacheGeneration();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role tidak dapat dihapus karena masih memiliki user.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
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

        if ($assignablePermissions->isEmpty()) {
            return [];
        }

        $byId = $assignablePermissions->keyBy(fn (Permission $p) => (int) $p->id);
        $groupedIds = [];
        foreach ($assignablePermissions as $permission) {
            $groupKey = $this->permissionGroupKey($permission->name);
            $groupedIds[$groupKey] ??= [];
            $groupedIds[$groupKey][] = (int) $permission->id;
        }

        $expanded = [];
        foreach ($submittedIds as $id) {
            $permission = $byId->get((int) $id);
            if (! $permission) {
                continue;
            }

            $groupKey = $this->permissionGroupKey($permission->name);
            foreach ($groupedIds[$groupKey] ?? [(int) $id] as $groupId) {
                $expanded[] = (int) $groupId;
            }
        }

        return array_values(array_unique($expanded));
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
