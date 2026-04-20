<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Helpers\PermissionHelper;

class RoleController extends Controller
{
    /** Label modul untuk tampilan (sederhana, satu sumber) */
    private const MODULE_LABELS = [
        'dashboard' => 'Dashboard',
        'master-manajemen' => 'Master Manajemen',
        'inventory' => 'Inventory',
        'permintaan' => 'Permintaan',
        'approval' => 'Approval',
        'distribusi' => 'Distribusi',
        'penerimaan-barang' => 'Penerimaan Barang',
        'retur-barang' => 'Retur Barang',
        'draft-distribusi' => 'Draft Distribusi',
        'compile-distribusi' => 'Compile Distribusi',
        'asset' => 'Aset & KIR',
        'reports' => 'Laporan',
        'admin' => 'Admin',
    ];

    /**
     * Siapkan permission dalam grup sederhana untuk view (satu variabel).
     *
     * @param  \Illuminate\Support\Collection  $permissionsByModule  Permission grouped by module
     * @param  array  $checkedIds  ID permission yang sudah dipilih role
     * @return array<int, array{module: string, label: string, items: \Illuminate\Support\Collection, checked_ids: array, all_checked: bool, some_checked: bool}>
     */
    private function buildPermissionGroups(\Illuminate\Support\Collection $permissionsByModule, array $checkedIds): array
    {
        $groups = [];
        foreach ($permissionsByModule as $module => $items) {
            $itemIds = $items->pluck('id')->toArray();
            $checked = array_values(array_intersect($itemIds, $checkedIds));
            $groups[] = [
                'module' => $module,
                'label' => self::MODULE_LABELS[$module] ?? ucwords(str_replace(['-', '_'], ' ', $module)),
                'items' => $items,
                'checked_ids' => $checked,
                'all_checked' => count($checked) === count($itemIds),
                'some_checked' => count($checked) > 0 && count($checked) < count($itemIds),
            ];
        }
        return $groups;
    }

    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $roles = Role::withCount('users')->latest()->paginate($perPage)->appends($request->query());
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionsByModule = Permission::orderBy('module')->orderBy('sort_order')->get()->groupBy('module');
        $permissionGroups = $this->buildPermissionGroups($permissionsByModule, []);
        return view('admin.roles.create', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Attach permissions
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
            PermissionHelper::bumpAccessibleMenusCacheGeneration();
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function show($id)
    {
        $role = Role::with(['users', 'permissions'])->findOrFail($id);
        return view('admin.roles.show', compact('role'));
    }

    public function edit($id)
    {
        $role = Role::with(['permissions', 'users.modules'])->findOrFail($id);

        $userModules = $role->users->flatMap(fn ($u) => $u->modules->pluck('name'))->unique()->sort()->values();

        if ($userModules->isNotEmpty()) {
            $permissionsByModule = Permission::whereIn('module', $userModules->toArray())
                ->orderBy('module')->orderBy('sort_order')->get()->groupBy('module');
        } else {
            $permissionsByModule = Permission::orderBy('module')->orderBy('sort_order')->get()->groupBy('module');
        }

        $checkedIds = $role->permissions->pluck('id')->toArray();
        $permissionGroups = $this->buildPermissionGroups($permissionsByModule, $checkedIds);
        $totalChecked = count($checkedIds);

        return view('admin.roles.edit', compact('role', 'permissionGroups', 'userModules', 'totalChecked'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        PermissionHelper::bumpAccessibleMenusCacheGeneration();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deletion if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role tidak dapat dihapus karena masih memiliki user.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
