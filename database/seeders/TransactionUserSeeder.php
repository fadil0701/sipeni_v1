<?php

namespace Database\Seeders;

use App\Models\MasterUnitKerja;
use App\Models\Role;
use App\Models\User;
use App\Support\Rbac\RbacRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TransactionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder ini akan:
     * 1. Menghapus semua user kecuali yang memiliki role 'admin'
     * 2. Membuat user baru untuk skenario transaksi; hak akses via Spatie syncRoles (model_has_roles).
     */
    public function run(): void
    {
        // Step 1: Hapus semua user kecuali admin
        $this->command->info('Menghapus semua user kecuali administrator...');

        // Ambil semua user yang memiliki role admin
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->pluck('id')->toArray();

        if (empty($adminUsers)) {
            $this->command->warn('⚠ Tidak ada user dengan role admin ditemukan. Semua user akan dihapus!');
            $this->command->warn('⚠ Pastikan Anda sudah membuat user admin terlebih dahulu.');

            if (! $this->command->confirm('Apakah Anda yakin ingin melanjutkan?', false)) {
                $this->command->info('Seeder dibatalkan.');

                return;
            }
        }

        // Hapus semua user yang bukan admin
        // Hapus dari pivot table terlebih dahulu
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereNotIn('model_id', $adminUsers)
            ->delete();

        // Hapus user
        $deletedCount = User::whereNotIn('id', $adminUsers)->delete();
        $this->command->info("✓ Dihapus {$deletedCount} user (kecuali admin)");

        // Step 2: Buat user baru sesuai kebutuhan transaksi
        $this->command->info('Membuat user baru untuk transaksi...');

        $users = [
            // ============================================
            // PEGAWAI (PEMOHON)
            // ============================================
            [
                'name' => 'Pegawai Unit 1',
                'email' => 'pegawai1@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_unit'],
            ],
            [
                'name' => 'Pegawai Unit 2',
                'email' => 'pegawai2@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_unit'],
            ],
            [
                'name' => 'Pegawai Unit 3',
                'email' => 'pegawai3@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_unit'],
            ],

            // ============================================
            // KEPALA UNIT (APPROVAL LEVEL 1)
            // ============================================
            [
                'name' => 'Kepala Unit Kerja 1',
                'email' => 'kepala_unit1@example.com',
                'password' => Hash::make('password'),
                'roles' => ['kepala_unit'],
            ],
            [
                'name' => 'Kepala Unit Kerja 2',
                'email' => 'kepala_unit2@example.com',
                'password' => Hash::make('password'),
                'roles' => ['kepala_unit'],
            ],

            // ============================================
            // KASUBBAG TU (VERIFIKASI)
            // ============================================
            [
                'name' => 'Kasubbag Tata Usaha',
                'email' => 'kasubbag_tu@example.com',
                'password' => Hash::make('password'),
                'roles' => ['kasubbag_tu'],
            ],

            // ============================================
            // KEPALA PUSAT (APPROVAL FINAL)
            // ============================================
            [
                'name' => 'Kepala Pusat',
                'email' => 'kepala_pusat@example.com',
                'password' => Hash::make('password'),
                'roles' => ['kepala_pusat'],
            ],

            // ============================================
            // ADMIN GUDANG (DISPOSISI, COMPILE, DISTRIBUSI)
            // ============================================
            [
                'name' => 'Admin Gudang Pusat',
                'email' => 'admin_gudang@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_gudang'],
            ],

            // ============================================
            // ADMIN GUDANG KATEGORI (PROSES DISPOSISI)
            // ============================================
            [
                'name' => 'Admin Gudang Aset',
                'email' => 'admin_gudang_aset@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_gudang_aset'],
            ],
            [
                'name' => 'Admin Gudang Persediaan',
                'email' => 'admin_gudang_persediaan@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_gudang_persediaan'],
            ],
            [
                'name' => 'Admin Gudang Farmasi',
                'email' => 'admin_gudang_farmasi@example.com',
                'password' => Hash::make('password'),
                'roles' => ['admin_gudang_farmasi'],
            ],

            // ============================================
            // UNIT TERKAIT (MONITORING)
            // ============================================
            [
                'name' => 'Staff Perencanaan',
                'email' => 'perencanaan@example.com',
                'password' => Hash::make('password'),
                'roles' => ['perencana'],
            ],
            [
                'name' => 'Staff Pengadaan',
                'email' => 'pengadaan@example.com',
                'password' => Hash::make('password'),
                'roles' => ['pengadaan'],
            ],
            [
                'name' => 'Staff Keuangan',
                'email' => 'keuangan@example.com',
                'password' => Hash::make('password'),
                'roles' => ['keuangan'],
            ],
        ];

        $unitKerjaId = MasterUnitKerja::query()->orderBy('id_unit_kerja')->value('id_unit_kerja');
        $unitKerjaId = $unitKerjaId !== null ? (int) $unitKerjaId : null;

        $createdCount = 0;
        foreach ($users as $userData) {
            $roles = RbacRoles::normalizeRoleNames($userData['roles']);
            unset($userData['roles']);

            // Cek apakah user sudah ada
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser) {
                $this->command->warn("  ⚠ User {$userData['email']} sudah ada, melewati...");

                continue;
            }

            $user = User::create($userData);

            $resolved = Role::query()->whereIn('name', $roles)->where('guard_name', 'web')->get(['id', 'name']);
            $roleIds = $resolved->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $missing = array_diff($roles, $resolved->pluck('name')->all());
            foreach ($missing as $name) {
                $this->command->error("  ✗ Role '{$name}' tidak ditemukan untuk user {$userData['email']}");
            }
            if ($roleIds !== []) {
                $scopedUnit = null;
                foreach ($resolved as $role) {
                    if (in_array($role->name, RbacRoles::UNIT_SCOPED, true)) {
                        $scopedUnit = $unitKerjaId;
                        break;
                    }
                }
                $user->syncUnifiedRoles($roleIds, $scopedUnit);
            }

            $createdCount++;
            $this->command->info("  ✓ Created: {$userData['name']} ({$userData['email']}) - Roles: ".implode(', ', $roles));
        }

        $this->command->info("\n✓ Selesai! Dibuat {$createdCount} user baru.");
        $this->command->info("\n📋 Daftar User yang Dibuat:");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('ADMIN UNIT (Pemohon):');
        $this->command->info('  • pegawai1@example.com / password');
        $this->command->info('  • pegawai2@example.com / password');
        $this->command->info('  • pegawai3@example.com / password');
        $this->command->info('');
        $this->command->info('KEPALA UNIT (Approval Level 1):');
        $this->command->info('  • kepala_unit1@example.com / password');
        $this->command->info('  • kepala_unit2@example.com / password');
        $this->command->info('');
        $this->command->info('KASUBBAG TU (Verifikasi):');
        $this->command->info('  • kasubbag_tu@example.com / password');
        $this->command->info('');
        $this->command->info('KEPALA PUSAT (Approval Final):');
        $this->command->info('  • kepala_pusat@example.com / password');
        $this->command->info('');
        $this->command->info('ADMIN GUDANG (Disposisi, Compile, Distribusi):');
        $this->command->info('  • admin_gudang@example.com / password');
        $this->command->info('');
        $this->command->info('ADMIN GUDANG KATEGORI (Proses Disposisi):');
        $this->command->info('  • admin_gudang_aset@example.com / password');
        $this->command->info('  • admin_gudang_persediaan@example.com / password');
        $this->command->info('  • admin_gudang_farmasi@example.com / password');
        $this->command->info('');
        $this->command->info('UNIT TERKAIT (Monitoring):');
        $this->command->info('  • perencanaan@example.com / password');
        $this->command->info('  • pengadaan@example.com / password');
        $this->command->info('  • keuangan@example.com / password');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
