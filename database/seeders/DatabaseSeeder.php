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
        // Urutan: role & permission → master data → jabatan (gelar organisasi) → admin → pegawai+user (role per akun) → approval flow
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            WorkflowStatusSeeder::class,
            RolePermissionBaselineSeeder::class,
            RbacPhase1Seeder::class,
            RolePermissionCorrectionSeeder::class,
            ModuleSeeder::class,
            MasterSatuanSeeder::class,
            MasterSumberAnggaranSeeder::class,
            MasterUnitKerjaSeeder::class,
            MasterGudangSeeder::class,
            MasterRuanganSeeder::class,
            MasterJabatanSeeder::class,
            //AdminUserSeeder::class,
            PegawaiUserPerJabatanSeeder::class,
            ApprovalFlowDefinitionSeeder::class,
            SbbkPrintTemplateSeeder::class,
            // ComprehensiveDummySeeder::class,
        ]);
    }
}
