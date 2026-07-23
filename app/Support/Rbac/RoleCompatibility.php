<?php

namespace App\Support\Rbac;

/**
 * Pemetaan role lama → role kanonik (Tahap 1 RBAC).
 * Role lama tidak dihapus; dipakai untuk alias resolusi dan sinkronisasi permission.
 */
final class RoleCompatibility
{
    /** @var array<string, string> legacy => canonical */
    public const LEGACY_TO_CANONICAL = [
        'pegawai' => 'admin_unit',
        'pegawai_unit' => 'admin_unit',
        'admin_gudang_unit' => 'admin_unit',
        'admin_perencanaan' => 'perencana',
        'perencanaan' => 'perencana',
        'admin_pengadaan_apbd' => 'pengadaan',
        'admin_pengadaan_blud' => 'pengadaan',
        'admin_keuangan' => 'keuangan',
        'admin_pptk_apbd' => 'pptk_apbd',
        'admin_pptk_blud' => 'pptk_blud',
        'administrator' => 'administrator',
        'admin' => 'admin',
        'admin_gudang' => 'admin_gudang_pusat',
        'pengurus_barang' => 'pengurus_barang',
    ];

    /** @var list<string> */
    public const CANONICAL_ROLES = [
        'super_administrator',
        'kepala_pusat',
        'kasubbag_tu',
        'kepala_unit',
        'admin_unit',
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
    ];

    /** @var list<string> */
    public const DEPRECATED_LEGACY_ROLES = [
        'pegawai',
        'pegawai_unit',
        'admin_gudang_unit',
        'admin_perencanaan',
        'perencanaan',
        'admin_pengadaan_apbd',
        'admin_pengadaan_blud',
        'admin_keuangan',
        'admin_pptk_apbd',
        'admin_pptk_blud',
        'admin_gudang',
    ];

    public static function canonicalFor(string $roleName): string
    {
        return self::LEGACY_TO_CANONICAL[$roleName] ?? $roleName;
    }

    /**
     * @return list<string>
     */
    public static function effectiveRoleNamesForUser(mixed $user): array
    {
        if (! $user || ! method_exists($user, 'roles')) {
            return [];
        }

        $names = $user->roles()->pluck('name')->map(fn ($n) => (string) $n)->all();
        $merged = [];
        foreach ($names as $name) {
            $merged[] = $name;
            $canonical = self::canonicalFor($name);
            if ($canonical !== $name) {
                $merged[] = $canonical;
            }
        }

        return array_values(array_unique($merged));
    }
}
