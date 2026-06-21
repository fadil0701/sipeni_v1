<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Rbac\RoleCompatibility;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Memastikan flag deprecated pada role legacy (idempotent setelah RoleSeeder).
 */
class CanonicalRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleCompatibility::DEPRECATED_LEGACY_ROLES as $legacyName) {
            $canonical = RoleCompatibility::canonicalFor($legacyName);
            Role::query()
                ->where('name', $legacyName)
                ->where('guard_name', 'web')
                ->update([
                    'is_deprecated' => true,
                    'maps_to_role' => $canonical,
                ]);
        }

        foreach (RoleCompatibility::CANONICAL_ROLES as $name) {
            Role::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->update([
                    'is_deprecated' => false,
                    'maps_to_role' => null,
                    'is_active' => true,
                ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('✓ Flag deprecated role legacy diperbarui.');
    }
}
