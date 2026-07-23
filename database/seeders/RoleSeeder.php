<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Rbac\RoleCompatibility;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $canonical = [
            'super_administrator' => [
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh seluruh sistem lintas unit kerja',
                'level_akses' => 'pusat',
            ],
            'kepala_pusat' => [
                'display_name' => 'Kepala Pusat',
                'description' => 'Kepala Pusat / Kepala UPT — approve/reject permintaan',
                'level_akses' => 'pusat',
            ],
            'kasubbag_tu' => [
                'display_name' => 'Kasubbag TU',
                'description' => 'Verifikasi administrasi permintaan',
                'level_akses' => 'pusat',
            ],
            'kepala_unit' => [
                'display_name' => 'Kepala Unit',
                'description' => 'Approval pengajuan unit kerja',
                'level_akses' => 'unit',
            ],
            'admin_unit' => [
                'display_name' => 'Admin Unit',
                'description' => 'Operator unit kerja — RKU, permintaan, stok/KIR unit',
                'level_akses' => 'unit',
            ],
            'perencana' => [
                'display_name' => 'Perencana',
                'description' => 'Unit perencanaan — RKU dan disposisi',
                'level_akses' => 'pusat',
            ],
            'pengadaan' => [
                'display_name' => 'Pengadaan',
                'description' => 'Unit pengadaan barang/jasa',
                'level_akses' => 'pusat',
            ],
            'keuangan' => [
                'display_name' => 'Keuangan',
                'description' => 'Unit keuangan / bendahara',
                'level_akses' => 'pusat',
            ],
            'pptk_apbd' => [
                'display_name' => 'PPTK APBD',
                'description' => 'Pelaksana teknis kegiatan APBD',
                'level_akses' => 'pusat',
            ],
            'pptk_blud' => [
                'display_name' => 'PPTK BLUD',
                'description' => 'Pelaksana teknis kegiatan BLUD',
                'level_akses' => 'pusat',
            ],
            'pengurus_barang' => [
                'display_name' => 'Pengurus Barang',
                'description' => 'Pengelola operasional persediaan dan distribusi',
                'level_akses' => 'pusat',
            ],
            'teknisi_atem' => [
                'display_name' => 'Teknisi ATEM',
                'description' => 'Teknisi Alat Kesehatan — daftar permintaan, service report, jadwal & kalibrasi',
                'level_akses' => 'pusat',
            ],
            'teknisi_it' => [
                'display_name' => 'Teknisi IT',
                'description' => 'Teknisi IT / IT Support — daftar permintaan, service report, jadwal & kalibrasi',
                'level_akses' => 'pusat',
            ],
            'admin_gudang_pusat' => [
                'display_name' => 'Admin Gudang Pusat',
                'description' => 'Admin gudang pusat (semua kategori)',
                'level_akses' => 'pusat',
            ],
            'admin_gudang_aset' => [
                'display_name' => 'Admin Gudang Aset',
                'description' => 'Admin gudang pusat kategori aset',
                'level_akses' => 'pusat',
            ],
            'admin_gudang_persediaan' => [
                'display_name' => 'Admin Gudang Persediaan',
                'description' => 'Admin gudang pusat kategori persediaan',
                'level_akses' => 'pusat',
            ],
            'admin_gudang_farmasi' => [
                'display_name' => 'Admin Gudang Farmasi',
                'description' => 'Admin gudang pusat kategori farmasi',
                'level_akses' => 'pusat',
            ],
        ];

        $legacy = [
            'administrator' => [
                'display_name' => 'Administrator',
                'description' => 'Administrator aplikasi (legacy — berbasis permission)',
                'level_akses' => 'pusat',
            ],
            'admin' => [
                'display_name' => 'Admin Sistem',
                'description' => 'Admin IT / pengelola aplikasi (legacy)',
                'level_akses' => 'pusat',
            ],
            'pegawai' => [
                'display_name' => 'Pegawai (Pemohon)',
                'description' => 'Legacy → admin_unit',
                'level_akses' => 'unit',
            ],
            'admin_gudang' => [
                'display_name' => 'Admin Gudang / Pengurus Barang',
                'description' => 'Legacy → admin_gudang_pusat',
                'level_akses' => 'pusat',
            ],
            'admin_gudang_unit' => [
                'display_name' => 'Admin Gudang Unit',
                'description' => 'Legacy → admin_unit',
                'level_akses' => 'unit',
            ],
            'perencanaan' => [
                'display_name' => 'Perencanaan',
                'description' => 'Legacy → perencana',
                'level_akses' => 'pusat',
            ],
            'admin_perencanaan' => [
                'display_name' => 'Admin Perencanaan',
                'description' => 'Legacy → perencana',
                'level_akses' => 'pusat',
            ],
            'admin_pengadaan_apbd' => [
                'display_name' => 'Admin Pengadaan APBD',
                'description' => 'Legacy → pengadaan',
                'level_akses' => 'pusat',
            ],
            'admin_pengadaan_blud' => [
                'display_name' => 'Admin Pengadaan BLUD',
                'description' => 'Legacy → pengadaan',
                'level_akses' => 'pusat',
            ],
            'admin_keuangan' => [
                'display_name' => 'Admin Keuangan',
                'description' => 'Legacy → keuangan',
                'level_akses' => 'pusat',
            ],
            'admin_pptk_apbd' => [
                'display_name' => 'Admin PPTK APBD',
                'description' => 'Legacy → pptk_apbd',
                'level_akses' => 'pusat',
            ],
            'admin_pptk_blud' => [
                'display_name' => 'Admin PPTK BLUD',
                'description' => 'Legacy → pptk_blud',
                'level_akses' => 'pusat',
            ],
            'pphp' => [
                'display_name' => 'PPHP',
                'description' => 'Pejabat pemeriksa hasil pekerjaan',
                'level_akses' => 'pusat',
            ],
            'admin_pphp' => [
                'display_name' => 'Admin PPHP',
                'description' => 'Admin PPHP',
                'level_akses' => 'pusat',
            ],
            'verifikator' => [
                'display_name' => 'Verifikator',
                'description' => 'Verifikator dokumen',
                'level_akses' => 'unit',
            ],
            'operator' => [
                'display_name' => 'Operator',
                'description' => 'Operator input data',
                'level_akses' => 'unit',
            ],
            'auditor' => [
                'display_name' => 'Auditor',
                'description' => 'Audit trail',
                'level_akses' => 'pusat',
            ],
        ];

        foreach ($canonical as $name => $meta) {
            $this->upsertRole($name, $meta, isDeprecated: false, mapsTo: null);
        }

        foreach ($legacy as $name => $meta) {
            $mapsTo = RoleCompatibility::LEGACY_TO_CANONICAL[$name] ?? null;
            $isDeprecated = in_array($name, RoleCompatibility::DEPRECATED_LEGACY_ROLES, true)
                || ($mapsTo !== null && $mapsTo !== $name);
            $this->upsertRole($name, $meta, isDeprecated: $isDeprecated, mapsTo: $mapsTo !== $name ? $mapsTo : null);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('✓ Role kanonik + legacy disimpan ('.count($canonical).' kanonik, '.count($legacy).' legacy).');
    }

    /**
     * @param  array{display_name: string, description?: string, level_akses: string}  $meta
     */
    private function upsertRole(string $name, array $meta, bool $isDeprecated, ?string $mapsTo): void
    {
        Role::updateOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            [
                'display_name' => $meta['display_name'],
                'kode_role' => $name,
                'nama_role' => $meta['display_name'],
                'level_akses' => $meta['level_akses'],
                'description' => $meta['description'] ?? null,
                'is_active' => true,
                'is_deprecated' => $isDeprecated,
                'maps_to_role' => $mapsTo,
            ]
        );
    }
}
