<?php

namespace App\Support\Rbac;

use App\Models\User;

/**
 * Konstanta role kanonik + resolusi role legacy (Tahap 2).
 */
final class RbacRoles
{
    /** @var list<string> */
    public const CANONICAL = RoleCompatibility::CANONICAL_ROLES;

    /** @var list<string> */
    public const UNIT_SCOPED = [
        'admin_unit',
        'kepala_unit',
        'pegawai',
        'pegawai_unit',
        'admin_gudang_unit',
    ];

    /** @var list<string> */
    public const WAREHOUSE_PUSAT = [
        'admin_gudang_pusat',
        'admin_gudang',
        'pengurus_barang',
        'admin_gudang_aset',
        'admin_gudang_persediaan',
        'admin_gudang_farmasi',
    ];

    /** @var list<string> */
    public const WAREHOUSE_CATEGORY = [
        'admin_gudang_aset',
        'admin_gudang_persediaan',
        'admin_gudang_farmasi',
    ];

    /**
     * Perluas daftar role kanonik dengan nama legacy yang memetakan ke salah satunya.
     *
     * @param  list<string>  $roles
     * @return list<string>
     */
    public static function expandWithLegacy(array $roles): array
    {
        $expanded = $roles;
        foreach (RoleCompatibility::LEGACY_TO_CANONICAL as $legacy => $canonical) {
            if (in_array($canonical, $roles, true)) {
                $expanded[] = $legacy;
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * @param  list<string>  $roles
     */
    public static function userHasAny(User $user, array $roles): bool
    {
        return $user->hasAnyRole(self::expandWithLegacy($roles));
    }

    public static function userHasWarehousePusatAccess(User $user): bool
    {
        return $user->hasAnyRole(self::WAREHOUSE_PUSAT);
    }

    public static function userHasUnitScopedRole(User $user): bool
    {
        return $user->hasAnyRole(self::UNIT_SCOPED);
    }

    /**
     * Normalisasi nama role ke kanonik sebelum assign/sync.
     *
     * @param  list<string>  $roleNames
     * @return list<string>
     */
    public static function normalizeRoleNames(array $roleNames): array
    {
        return array_values(array_unique(array_map(
            fn (string $name) => RoleCompatibility::canonicalFor($name),
            $roleNames
        )));
    }
}
