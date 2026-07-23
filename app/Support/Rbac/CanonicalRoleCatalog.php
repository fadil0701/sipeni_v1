<?php

namespace App\Support\Rbac;

use App\Models\Role;

/**
 * Definisi role kanonik SI-MANTIK — dipakai RoleSeeder, app:seed-system-roles, dan perbaikan RBAC.
 */
final class CanonicalRoleCatalog
{
    /** @var list<string> */
    public const PUSAT_LEVEL_ROLES = [
        'super_administrator',
        'kepala_pusat',
        'kasubbag_tu',
        'perencana',
        'pengadaan',
        'keuangan',
        'pptk_apbd',
        'pptk_blud',
        'pengurus_barang',
        'teknisi_atem',
        'teknisi_it',
        'admin_gudang_pusat',
        'admin_gudang_aset',
        'admin_gudang_persediaan',
        'admin_gudang_farmasi',
        'pphp',
    ];

    /**
     * @return array<string, array{display_name: string, description: string, level_akses: string}>
     */
    public static function definitions(): array
    {
        return [
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
            'pphp' => [
                'display_name' => 'PPHP',
                'description' => 'Pejabat pemeriksa hasil pekerjaan',
                'level_akses' => 'pusat',
            ],
        ];
    }

    public static function isPusatLevel(string $roleName): bool
    {
        return in_array($roleName, self::PUSAT_LEVEL_ROLES, true);
    }

    public static function isProtectedRole(string $roleName): bool
    {
        return in_array($roleName, config('sipeni.rbac.bypass_roles', ['super_administrator']), true);
    }

    /**
     * Upsert semua role kanonik + flag sistem (additive, tidak menghapus role custom).
     */
    public static function upsertAll(): int
    {
        $count = 0;

        foreach (self::definitions() as $name => $meta) {
            Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'display_name' => $meta['display_name'],
                    'kode_role' => $name,
                    'nama_role' => $meta['display_name'],
                    'level_akses' => $meta['level_akses'],
                    'description' => $meta['description'],
                    'is_active' => true,
                    'is_deprecated' => false,
                    'maps_to_role' => null,
                    'is_system_role' => true,
                    'is_protected' => self::isProtectedRole($name),
                ]
            );
            $count++;
        }

        return $count;
    }
}
