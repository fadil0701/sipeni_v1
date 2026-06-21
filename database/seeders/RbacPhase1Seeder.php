<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orkestrasi RBAC Tahap 1 — jalankan setelah RoleSeeder + PermissionSeeder.
 */
class RbacPhase1Seeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CanonicalRoleSeeder::class,
            RoleLegacyPermissionSeeder::class,
            RoleLegacyMappingSeeder::class,
        ]);
    }
}
