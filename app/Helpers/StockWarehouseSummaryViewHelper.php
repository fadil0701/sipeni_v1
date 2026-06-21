<?php

namespace App\Helpers;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Models\User;

class StockWarehouseSummaryViewHelper
{
    /**
     * Role yang boleh melihat opsi Ringkasan per gudang (kartu).
     * Mencakup pimpinan/admin pusat serta akun gudang unit (kartu unit dibatasi ke Persediaan + Farmasi di layer query).
     */
    public static function canAccessSummaryCards(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (UserScope::canViewCrossUnitData($user)) {
            return true;
        }

        return $user->hasAnyRole(array_merge(
            RbacRoles::WAREHOUSE_PUSAT,
            ['kepala_pusat', 'kasubbag_tu', 'kepala_unit'],
            RbacRoles::UNIT_SCOPED,
        ));
    }

    /**
     * Akun yang fokus ke gudang unit: stok yang ditampilkan/di-ringkas hanya Persediaan & Farmasi,
     * kecuali pengguna juga punya salah satu role manajemen/pusat di bawah (maka ikut cakupan penuh).
     */
    public static function shouldLimitStockViewsToPersediaanFarmasiForUnit(?User $user): bool
    {
        if (! $user || UserScope::canViewCrossUnitData($user)) {
            return false;
        }

        if ($user->hasAnyRole(array_merge(RbacRoles::WAREHOUSE_PUSAT, ['kepala_pusat', 'kasubbag_tu']))) {
            return false;
        }

        return $user->hasAnyRole(RbacRoles::UNIT_SCOPED);
    }
}
