<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first, then permissions, then modules, then jabatan, then admin user, then approval flow
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            ModuleSeeder::class,
            MasterJabatanSeeder::class,
            AdminUserSeeder::class,
            ApprovalFlowDefinitionSeeder::class,
        ]);
    }
}
