<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Idempotent: buat role teknisi + sync permission pemeliharaan.
 * Cocok dijalankan di Docker setelah migrate:
 *   docker compose exec app php artisan db:seed --class=TeknisiMaintenanceRoleSeeder
 */
class TeknisiMaintenanceRoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MasterJabatanSeeder::class,
            RoleSeeder::class,
            CanonicalRoleSeeder::class,
            RolePermissionBaselineSeeder::class,
            StandardRolePermissionV2Seeder::class,
        ]);

        $this->command?->info('✓ Role teknisi_atem / teknisi_it + permission pemeliharaan (SR, daftar, jadwal, kalibrasi) siap.');
        $this->command?->info('  Assign role ke user teknisi lewat Admin → Users, atau tautkan jabatan ATEM/IT Support di Master Pegawai.');
    }
}
