<?php

namespace Database\Seeders\Concerns;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\RoleCompatibility;
use Spatie\Permission\PermissionRegistrar;

trait SeedsUserRolesAndModules
{
    /**
     * @param  list<string>  $roleNames
     */
    protected function syncDemoUserAccess(User $user, array $roleNames, ?int $unitKerjaId = null): void
    {
        $canonical = RbacRoles::normalizeRoleNames($roleNames);
        $roles = Role::query()
            ->whereIn('name', $canonical)
            ->where('guard_name', 'web')
            ->get();

        if ($roles->isEmpty()) {
            $this->command?->warn("Tidak ada role valid untuk {$user->email}; lewati sync role.");

            return;
        }

        $roleIds = $roles->pluck('id')->map(fn ($id) => (int) $id)->all();

        $scopedUnitId = null;
        foreach ($roles as $role) {
            if (in_array($role->name, RbacRoles::UNIT_SCOPED, true)) {
                $scopedUnitId = $unitKerjaId;
                break;
            }
        }

        $user->syncUnifiedRoles($roleIds, $scopedUnitId);
        $this->syncDemoUserModules($user, $canonical);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  list<string>  $canonicalRoleNames
     */
    protected function syncDemoUserModules(User $user, array $canonicalRoleNames): void
    {
        $moduleNames = [];
        foreach ($canonicalRoleNames as $roleName) {
            $moduleNames = array_merge($moduleNames, $this->defaultModulesForCanonicalRole($roleName));
        }
        $moduleNames = array_values(array_unique($moduleNames));
        if ($moduleNames === []) {
            return;
        }

        $existing = Module::query()->whereIn('name', $moduleNames)->pluck('name')->all();
        if ($existing !== []) {
            $user->modules()->sync($existing);
        }
    }

    /**
     * @return list<string>
     */
    protected function defaultModulesForCanonicalRole(string $canonicalRole): array
    {
        $canonical = RoleCompatibility::canonicalFor($canonicalRole);

        return match ($canonical) {
            'super_administrator' => ['master-manajemen', 'master-data', 'inventory', 'transaction', 'asset', 'planning', 'procurement', 'finance', 'maintenance', 'reports'],
            'admin', 'administrator' => ['master-manajemen', 'master-data', 'inventory', 'transaction', 'asset', 'planning', 'procurement', 'finance', 'reports'],
            'admin_unit', 'kepala_unit' => ['transaction', 'inventory', 'asset', 'planning'],
            'kepala_pusat', 'kasubbag_tu' => ['transaction', 'reports'],
            'perencana' => ['planning', 'transaction'],
            'pengadaan' => ['procurement', 'transaction'],
            'keuangan' => ['finance', 'transaction'],
            'pptk_apbd', 'pptk_blud' => ['planning', 'procurement', 'reports'],
            'pengurus_barang', 'admin_gudang_pusat' => ['inventory', 'transaction', 'asset'],
            'admin_gudang_aset' => ['inventory', 'asset'],
            'admin_gudang_persediaan' => ['inventory', 'transaction'],
            'admin_gudang_farmasi' => ['inventory', 'transaction'],
            default => ['transaction'],
        };
    }
}
