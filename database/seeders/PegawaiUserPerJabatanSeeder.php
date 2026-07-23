<?php

namespace Database\Seeders;

use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\User;
use Database\Seeders\Concerns\SeedsUserRolesAndModules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PegawaiUserPerJabatanSeeder extends Seeder
{
    use SeedsUserRolesAndModules;

    /**
     * Pasangan pegawai + user demo per jabatan (role kanonik + modul + unit_kerja_id).
     */
    public function run(): void
    {
        if (! config('sipeni.users.seed_demo_users', true)) {
            $this->command?->info('PegawaiUserPerJabatanSeeder dilewati (SIPENI_SEED_DEMO_USERS=false).');

            return;
        }

        $demoPassword = (string) config('sipeni.users.demo_pegawai_password', '');
        if ($demoPassword === '') {
            $this->command?->warn('PegawaiUserPerJabatanSeeder dilewati — set SIPENI_DEMO_PEGAWAI_PASSWORD di .env');

            return;
        }

        $unitKerjaDefault = MasterUnitKerja::orderBy('id_unit_kerja')->first();

        if (! $unitKerjaDefault) {
            $this->command?->warn('MasterUnitKerja belum tersedia. Seeder pegawai/user dilewati.');

            return;
        }

        $unitId = (int) $unitKerjaDefault->id_unit_kerja;

        $jabatans = MasterJabatan::query()
            ->where('nama_jabatan', '!=', 'Admin IT / Pengelola Aplikasi')
            ->where('nama_jabatan', '!=', 'Staf Administrasi')
            ->orderBy('urutan')
            ->orderBy('id_jabatan')
            ->get();

        if ($jabatans->isEmpty()) {
            $this->command?->warn('Tidak ada jabatan yang dapat dibuatkan user demo.');

            return;
        }

        $createdUsers = 0;
        $createdPegawai = 0;

        foreach ($jabatans as $jabatan) {
            $jabatanId = (int) $jabatan->id_jabatan;
            $slug = Str::slug($jabatan->nama_jabatan ?: 'jabatan-'.$jabatanId) ?: 'jabatan-'.$jabatanId;

            $email = $slug.'.'.$jabatanId.'@sipeni.local';
            $namaUser = 'User '.$jabatan->nama_jabatan;
            $nip = 'PEG'.str_pad((string) $jabatanId, 6, '0', STR_PAD_LEFT);
            $roleNames = $this->demoRoleNamesForJabatanTitle($jabatan->nama_jabatan);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $namaUser,
                    'password' => Hash::make($demoPassword),
                    'is_active' => true,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $createdUsers++;
            } elseif ($user->name !== $namaUser) {
                $user->name = $namaUser;
                $user->save();
            }

            $pegawai = MasterPegawai::updateOrCreate(
                ['nip_pegawai' => $nip],
                [
                    'nama_pegawai' => $namaUser,
                    'id_unit_kerja' => $unitId,
                    'id_jabatan' => $jabatanId,
                    'email_pegawai' => $email,
                    'no_telp' => null,
                    'user_id' => $user->id,
                ]
            );

            if ($pegawai->wasRecentlyCreated) {
                $createdPegawai++;
            }

            $this->syncDemoUserAccess($user, $roleNames, $unitId);
        }

        $this->seedStafAdministrasiSameJabatanDifferentRoles($unitKerjaDefault);

        $this->command?->info("✓ PegawaiUserPerJabatan: {$createdUsers} user baru, {$createdPegawai} pegawai baru.");
        $this->command?->info('  Password: dari SIPENI_DEMO_PEGAWAI_PASSWORD | Role: kanonik');
    }

    /**
     * @return list<string>
     */
    private function demoRoleNamesForJabatanTitle(string $namaJabatan): array
    {
        $primary = match ($namaJabatan) {
            'Admin Unit' => 'admin_unit',
            'Kepala Unit' => 'kepala_unit',
            'Kasubbag TU' => 'kasubbag_tu',
            'Kepala Pusat' => 'kepala_pusat',
            'Pengurus Barang' => 'pengurus_barang',
            'Admin Gudang' => 'admin_gudang_pusat',
            'Admin Gudang Aset' => 'admin_gudang_aset',
            'Admin Gudang Persediaan' => 'admin_gudang_persediaan',
            'Admin Gudang Farmasi' => 'admin_gudang_farmasi',
            'Admin Gudang Unit' => 'admin_unit',
            'Perencanaan' => 'perencana',
            'Pengadaan Barang' => 'pengadaan',
            'Keuangan/Bendahara' => 'keuangan',
            'ATEM (Teknisi Alat Kesehatan)' => 'teknisi_atem',
            'Admin IT/IT Support (Teknisi IT)' => 'teknisi_it',
            default => 'admin_unit',
        };

        return [$primary];
    }

    private function seedStafAdministrasiSameJabatanDifferentRoles(MasterUnitKerja $unit): void
    {
        $jabatan = MasterJabatan::query()->where('nama_jabatan', 'Staf Administrasi')->first();
        if (! $jabatan) {
            return;
        }

        $idJabatan = (int) $jabatan->id_jabatan;
        $unitId = (int) $unit->id_unit_kerja;

        $demoEmails = config('sipeni.users.demo_emails', []);

        $variants = [
            [
                'nip' => 'STAFADM0001',
                'email' => ($demoEmails['pemohon'] ?? '') ?: 'staf-adm.gudang-unit@sipeni.local',
                'name' => 'Staf Adm - Admin Unit',
                'roles' => ['admin_unit'],
            ],
            [
                'nip' => 'STAFADM0002',
                'email' => ($demoEmails['admin_gudang'] ?? '') ?: 'staf-adm.gudang-pusat@sipeni.local',
                'name' => 'Staf Adm - Admin Gudang Pusat',
                'roles' => ['admin_gudang_pusat'],
            ],
            [
                'nip' => 'STAFADM0003',
                'email' => ($demoEmails['perencana'] ?? '') ?: 'staf-adm.perencana@sipeni.local',
                'name' => 'Staf Adm - Perencana',
                'roles' => ['perencana'],
            ],
        ];

        $demoPassword = (string) config('sipeni.users.demo_pegawai_password', '');

        foreach ($variants as $row) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make($demoPassword),
                    'is_active' => true,
                ]
            );

            MasterPegawai::updateOrCreate(
                ['nip_pegawai' => $row['nip']],
                [
                    'nama_pegawai' => $row['name'],
                    'id_unit_kerja' => $unitId,
                    'id_jabatan' => $idJabatan,
                    'email_pegawai' => $row['email'],
                    'user_id' => $user->id,
                ]
            );

            $scopedUnit = in_array($row['roles'][0], ['admin_unit'], true) ? $unitId : null;
            $this->syncDemoUserAccess($user, $row['roles'], $scopedUnit);
        }

        $this->command?->info('✓ 3 user Staf Administrasi (role kanonik berbeda).');
    }
}
