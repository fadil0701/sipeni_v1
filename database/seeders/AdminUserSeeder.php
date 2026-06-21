<?php

namespace Database\Seeders;

use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\Concerns\SeedsUserRolesAndModules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use SeedsUserRolesAndModules;

    public function run(): void
    {
        $this->command->info('Membuat user administrator dari .env (SIPENI_SUPER_ADMIN_*, SIPENI_ADMIN_IT_*)...');

        $superAdminRole = Role::query()->where('name', 'super_administrator')->where('guard_name', 'web')->first();
        if (! $superAdminRole) {
            $this->command->error('✗ Role super_administrator tidak ditemukan. Jalankan RoleSeeder terlebih dahulu.');

            return;
        }

        $superConfig = config('sipeni.users.super_admin', []);
        $superEmail = trim((string) ($superConfig['email'] ?? ''));
        $superPassword = (string) ($superConfig['password'] ?? '');
        $superName = trim((string) ($superConfig['name'] ?? '')) ?: 'Super Administrator';

        if ($superEmail !== '' && $superPassword !== '') {
            $superAdmin = User::updateOrCreate(
                ['email' => $superEmail],
                [
                    'name' => $superName,
                    'password' => Hash::make($superPassword),
                    'is_active' => true,
                ]
            );
            $this->syncDemoUserAccess($superAdmin, ['super_administrator'], null);
            $this->command->info("✓ Super Admin: {$superEmail}");
        } else {
            $this->command->warn('⚠ Super Admin dilewati — set SIPENI_SUPER_ADMIN_EMAIL & SIPENI_SUPER_ADMIN_PASSWORD di .env');
        }

        $adminRole = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();
        if (! $adminRole) {
            return;
        }

        $adminConfig = config('sipeni.users.admin_it', []);
        $adminEmail = trim((string) ($adminConfig['email'] ?? ''));
        $adminPassword = (string) ($adminConfig['password'] ?? '');
        $adminName = trim((string) ($adminConfig['name'] ?? '')) ?: 'Admin IT / Pengelola Aplikasi';

        if ($adminEmail === '' || $adminPassword === '') {
            $this->command->warn('⚠ Admin IT dilewati — set SIPENI_ADMIN_IT_EMAIL & SIPENI_ADMIN_IT_PASSWORD di .env');

            return;
        }

        $unit = MasterUnitKerja::query()->orderBy('id_unit_kerja')->first();
        $adminIt = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'is_active' => true,
            ]
        );

        $jabatanIt = MasterJabatan::query()->where('nama_jabatan', 'Admin IT / Pengelola Aplikasi')->first();
        if ($unit && $jabatanIt) {
            MasterPegawai::updateOrCreate(
                ['nip_pegawai' => 'ADM000001'],
                [
                    'nama_pegawai' => $adminIt->name,
                    'id_unit_kerja' => $unit->id_unit_kerja,
                    'id_jabatan' => $jabatanIt->id_jabatan,
                    'email_pegawai' => $adminIt->email,
                    'user_id' => $adminIt->id,
                ]
            );
        }

        $this->syncDemoUserAccess($adminIt, ['admin'], null);
        $this->command->info("✓ Admin IT: {$adminEmail}");
    }
}
