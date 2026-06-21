<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Rbac\RoleCompatibility;
use App\Support\Rbac\StaticRolePermissionMap;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Sinkronkan permission static map → database per role (baseline DB, bukan runtime fallback).
 */
class RoleLegacyPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $map = StaticRolePermissionMap::all();
        $permissionIdsByName = Permission::query()->pluck('id', 'name');

        $touched = 0;
        foreach ($map as $roleName => $permissionNames) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if (! $role) {
                continue;
            }

            $ids = [];
            foreach ($permissionNames as $name) {
                $id = $permissionIdsByName[$name] ?? null;
                if ($id) {
                    $ids[] = (int) $id;
                }
            }

            $ids = array_values(array_unique(array_filter($ids)));
            if ($ids === []) {
                continue;
            }

            $role->permissions()->syncWithoutDetaching($ids);

            $canonicalName = RoleCompatibility::canonicalFor($roleName);
            if ($canonicalName !== $roleName) {
                $canonicalRole = Role::query()->where('name', $canonicalName)->where('guard_name', 'web')->first();
                if ($canonicalRole) {
                    $canonicalRole->permissions()->syncWithoutDetaching($ids);
                }
            }

            $touched++;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info("✓ Permission static map → role legacy + kanonik ({$touched} entri map).");
    }
}
