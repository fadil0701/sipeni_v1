<?php

namespace Database\Seeders;

use App\Helpers\PermissionHelper;
use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\Role;
use App\Models\User;
use App\Support\Rbac\RbacRoles;
use Database\Seeders\Concerns\SeedsUserRolesAndModules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * User demo untuk uji alur transaksi & role (bukan akun produksi).
 *
 * Jalankan:
 *   php artisan db:seed --class=DemoUserSeeder
 *   php artisan db:seed --class=UserSeeder
 */
class DemoUserSeeder extends Seeder
{
    use SeedsUserRolesAndModules;

    public function run(): void
    {
        if (! config('sipeni.users.seed_demo_users', true)) {
            $this->command?->info('DemoUserSeeder dilewati (SIPENI_SEED_DEMO_USERS=false).');

            return;
        }

        $password = (string) config('sipeni.users.demo_pegawai_password', '');
        if ($password === '') {
            $password = 'Demo@Sipeni2026!!';
            $this->command?->warn('SIPENI_DEMO_PEGAWAI_PASSWORD kosong — memakai password default lokal: Demo@Sipeni2026!!');
        }

        $missingRoles = $this->missingRoles();
        if ($missingRoles !== []) {
            $this->command?->error('Role belum tersedia: '.implode(', ', $missingRoles));
            $this->command?->error('Jalankan RoleSeeder / baseline permission terlebih dahulu.');

            return;
        }

        $units = MasterUnitKerja::query()->orderBy('id_unit_kerja')->get();
        $unitPrimary = $units->first();
        $unitSecondary = $units->skip(1)->first() ?? $unitPrimary;

        if (! $unitPrimary) {
            $this->command?->warn('MasterUnitKerja belum ada — seeder demo dilewati.');

            return;
        }

        $this->command?->info('Membuat / memperbarui user demo...');

        $created = 0;
        $updated = 0;

        foreach ($this->demoAccounts($unitPrimary, $unitSecondary) as $account) {
            $result = $this->upsertDemoAccount($account, $password);
            if ($result === 'created') {
                $created++;
            } elseif ($result === 'updated') {
                $updated++;
            }
        }

        PermissionHelper::bumpAccessibleMenusCacheGeneration();

        $this->command?->info("✓ Demo users: {$created} baru, {$updated} diperbarui.");
        $this->printCredentialTable($password);
    }

    /**
     * @return list<array{
     *   email: string,
     *   name: string,
     *   nip: string,
     *   roles: list<string>,
     *   jabatan: string,
     *   unit: MasterUnitKerja|null,
     *   scope_unit: bool
     * }>
     */
    private function demoAccounts(MasterUnitKerja $unitPrimary, MasterUnitKerja $unitSecondary): array
    {
        return [
            [
                'email' => 'demo.admin.unit@sipeni.local',
                'name' => 'Demo Admin Unit',
                'nip' => 'DEMO000001',
                'roles' => ['admin_unit'],
                'jabatan' => 'Admin Unit',
                'unit' => $unitPrimary,
                'scope_unit' => true,
            ],
            [
                'email' => 'demo.admin.unit2@sipeni.local',
                'name' => 'Demo Admin Unit 2',
                'nip' => 'DEMO000002',
                'roles' => ['admin_unit'],
                'jabatan' => 'Admin Unit',
                'unit' => $unitSecondary,
                'scope_unit' => true,
            ],
            [
                'email' => 'demo.kepala.unit@sipeni.local',
                'name' => 'Demo Kepala Unit',
                'nip' => 'DEMO000003',
                'roles' => ['kepala_unit'],
                'jabatan' => 'Kepala Unit',
                'unit' => $unitPrimary,
                'scope_unit' => true,
            ],
            [
                'email' => 'demo.kasubbag.tu@sipeni.local',
                'name' => 'Demo Kasubbag TU',
                'nip' => 'DEMO000004',
                'roles' => ['kasubbag_tu'],
                'jabatan' => 'Kasubbag TU',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.kepala.pusat@sipeni.local',
                'name' => 'Demo Kepala Pusat',
                'nip' => 'DEMO000005',
                'roles' => ['kepala_pusat'],
                'jabatan' => 'Kepala Pusat',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.pengurus.barang@sipeni.local',
                'name' => 'Demo Pengurus Barang',
                'nip' => 'DEMO000006',
                'roles' => ['pengurus_barang'],
                'jabatan' => 'Pengurus Barang',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.admin.gudang@sipeni.local',
                'name' => 'Demo Admin Gudang Pusat',
                'nip' => 'DEMO000007',
                'roles' => ['admin_gudang_pusat'],
                'jabatan' => 'Admin Gudang',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.admin.gudang.aset@sipeni.local',
                'name' => 'Demo Admin Gudang Aset',
                'nip' => 'DEMO000008',
                'roles' => ['admin_gudang_aset'],
                'jabatan' => 'Admin Gudang Aset',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.admin.gudang.persediaan@sipeni.local',
                'name' => 'Demo Admin Gudang Persediaan',
                'nip' => 'DEMO000009',
                'roles' => ['admin_gudang_persediaan'],
                'jabatan' => 'Admin Gudang Persediaan',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.admin.gudang.farmasi@sipeni.local',
                'name' => 'Demo Admin Gudang Farmasi',
                'nip' => 'DEMO000010',
                'roles' => ['admin_gudang_farmasi'],
                'jabatan' => 'Admin Gudang Farmasi',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.perencana@sipeni.local',
                'name' => 'Demo Perencana',
                'nip' => 'DEMO000011',
                'roles' => ['perencana'],
                'jabatan' => 'Perencanaan',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.pengadaan@sipeni.local',
                'name' => 'Demo Pengadaan',
                'nip' => 'DEMO000012',
                'roles' => ['pengadaan'],
                'jabatan' => 'Pengadaan Barang',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
            [
                'email' => 'demo.keuangan@sipeni.local',
                'name' => 'Demo Keuangan',
                'nip' => 'DEMO000013',
                'roles' => ['keuangan'],
                'jabatan' => 'Keuangan/Bendahara',
                'unit' => $unitPrimary,
                'scope_unit' => false,
            ],
        ];
    }

    /**
     * @param  array{
     *   email: string,
     *   name: string,
     *   nip: string,
     *   roles: list<string>,
     *   jabatan: string,
     *   unit: MasterUnitKerja|null,
     *   scope_unit: bool
     * }  $account
     */
    private function upsertDemoAccount(array $account, string $password): string
    {
        $user = User::query()->where('email', $account['email'])->first();
        $wasNew = $user === null;

        $user = User::updateOrCreate(
            ['email' => $account['email']],
            [
                'name' => $account['name'],
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );

        $jabatanId = null;
        if ($account['jabatan'] !== '') {
            $jabatan = MasterJabatan::query()->firstOrCreate(
                ['nama_jabatan' => $account['jabatan']],
                [
                    'urutan' => 900,
                    'deskripsi' => 'Jabatan untuk akun demo',
                ]
            );
            $jabatanId = (int) $jabatan->id_jabatan;
        }

        $unitId = $account['unit']?->id_unit_kerja;

        MasterPegawai::updateOrCreate(
            ['nip_pegawai' => $account['nip']],
            [
                'nama_pegawai' => $account['name'],
                'id_unit_kerja' => $unitId,
                'id_jabatan' => $jabatanId,
                'email_pegawai' => $account['email'],
                'user_id' => $user->id,
            ]
        );

        $scopedUnit = $account['scope_unit'] ? ($unitId !== null ? (int) $unitId : null) : null;

        // Pastikan role unit-scoped mendapat scope meski flag di akun salah
        foreach ($account['roles'] as $roleName) {
            if (in_array($roleName, RbacRoles::UNIT_SCOPED, true)) {
                $scopedUnit = $unitId !== null ? (int) $unitId : null;
                break;
            }
        }

        $this->syncDemoUserAccess($user, $account['roles'], $scopedUnit);

        $this->command?->info(sprintf(
            '  %s %s (%s) → %s',
            $wasNew ? '✓' : '↻',
            $account['email'],
            implode(', ', $account['roles']),
            $account['unit']?->nama_unit_kerja ?? '-'
        ));

        return $wasNew ? 'created' : 'updated';
    }

    /**
     * @return list<string>
     */
    private function missingRoles(): array
    {
        $needed = [
            'admin_unit',
            'kepala_unit',
            'kasubbag_tu',
            'kepala_pusat',
            'pengurus_barang',
            'admin_gudang_pusat',
            'admin_gudang_aset',
            'admin_gudang_persediaan',
            'admin_gudang_farmasi',
            'perencana',
            'pengadaan',
            'keuangan',
        ];

        $existing = Role::query()
            ->whereIn('name', $needed)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        return array_values(array_diff($needed, $existing));
    }

    private function printCredentialTable(string $password): void
    {
        $this->command?->newLine();
        $this->command?->info('📋 Kredensial user demo (password sama untuk semua):');
        $this->command?->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command?->info("  Password: {$password}");
        $this->command?->info('');
        $this->command?->info('  demo.admin.unit@sipeni.local          → admin_unit (pemohon)');
        $this->command?->info('  demo.admin.unit2@sipeni.local         → admin_unit (unit lain)');
        $this->command?->info('  demo.kepala.unit@sipeni.local         → kepala_unit');
        $this->command?->info('  demo.kasubbag.tu@sipeni.local         → kasubbag_tu');
        $this->command?->info('  demo.kepala.pusat@sipeni.local        → kepala_pusat');
        $this->command?->info('  demo.pengurus.barang@sipeni.local     → pengurus_barang');
        $this->command?->info('  demo.admin.gudang@sipeni.local        → admin_gudang_pusat');
        $this->command?->info('  demo.admin.gudang.aset@sipeni.local   → admin_gudang_aset');
        $this->command?->info('  demo.admin.gudang.persediaan@sipeni.local → admin_gudang_persediaan');
        $this->command?->info('  demo.admin.gudang.farmasi@sipeni.local → admin_gudang_farmasi');
        $this->command?->info('  demo.perencana@sipeni.local           → perencana');
        $this->command?->info('  demo.pengadaan@sipeni.local           → pengadaan');
        $this->command?->info('  demo.keuangan@sipeni.local            → keuangan');
        $this->command?->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command?->info('Super Admin / Admin IT: dari AdminUserSeeder (.env SIPENI_*).');
    }
}
