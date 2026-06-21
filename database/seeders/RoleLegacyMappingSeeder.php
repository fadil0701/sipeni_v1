<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Rbac\RoleCompatibility;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Salin permission dari role legacy ke role kanonik (tanpa menghapus role lama).
 */
class RoleLegacyMappingSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = RoleCompatibility::LEGACY_TO_CANONICAL;
        $merged = 0;

        foreach ($pairs as $legacyName => $canonicalName) {
            if ($legacyName === $canonicalName) {
                continue;
            }

            $legacy = Role::query()->where('name', $legacyName)->where('guard_name', 'web')->first();
            $canonical = Role::query()->where('name', $canonicalName)->where('guard_name', 'web')->first();
            if (! $legacy || ! $canonical) {
                continue;
            }

            $permissionIds = $legacy->permissions()->pluck('permissions.id')->all();
            if ($permissionIds !== []) {
                $canonical->permissions()->syncWithoutDetaching($permissionIds);
                $merged++;
            }
        }

        $admin = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();
        $administrator = Role::query()->where('name', 'administrator')->where('guard_name', 'web')->first();
        if ($admin && $administrator) {
            $ids = $admin->permissions()->pluck('permissions.id')->all();
            if ($ids !== []) {
                $administrator->permissions()->syncWithoutDetaching($ids);
                $merged++;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info("✓ Permission disalin ke role kanonik untuk {$merged} pemetaan legacy.");
    }
}
