<?php

namespace App\Support\Rbac;

use App\Helpers\PermissionHelper;
use App\Models\MasterGudang;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;
use App\Models\User;
use App\Services\ScopeAccessService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pembatasan scope data unit kerja — pengganti pola hasRole('admin') / pegawai+kepala_unit.
 */
final class UserScope
{
    /**
     * User harus hanya melihat data unit_kerja sendiri (admin_unit, kepala_unit, legacy unit).
     */
    public static function mustScopeToUnitKerja(User $user): bool
    {
        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return false;
        }

        return RbacRoles::userHasUnitScopedRole($user);
    }

    /**
     * User hanya boleh melihat data unit sendiri (bukan gudang pusat / KIB lintas unit).
     * Setara pola lama: role unit + bukan pengurus/gudang pusat.
     */
    public static function isUnitKerjaDataRestricted(User $user): bool
    {
        if (! self::mustScopeToUnitKerja($user)) {
            return false;
        }

        if (RbacRoles::userHasWarehousePusatAccess($user) || $user->hasRole('pengurus_barang')) {
            return false;
        }

        return true;
    }

    /**
     * Dapat melihat data lintas unit kerja.
     */
    public static function canViewCrossUnitData(User $user): bool
    {
        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return true;
        }

        if (self::mustScopeToUnitKerja($user)) {
            return false;
        }

        return ScopeAccessService::isPusat($user);
    }

    public static function unitKerjaId(User $user): ?int
    {
        return ScopeAccessService::userUnitKerjaId($user);
    }

    /**
     * Terapkan filter unit_kerja pada query Eloquent.
     */
    public static function applyUnitKerja(Builder $query, User $user, string $column = 'id_unit_kerja'): Builder
    {
        if (self::mustScopeToUnitKerja($user)) {
            $unitId = self::unitKerjaId($user);
            if ($unitId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where($column, $unitId);
        }

        return ScopeAccessService::applyUnitScope($query, $user, $column);
    }

    /**
     * Alias eksplisit sesuai konvensi dokumentasi Tahap 2.
     */
    public static function unitKerja(Builder $query, User $user, string $column = 'id_unit_kerja'): Builder
    {
        return self::applyUnitKerja($query, $user, $column);
    }

    /**
     * Tolak akses jika user unit-scoped meminta data unit kerja lain.
     */
    public static function assertCanAccessUnitKerjaData(User $user, int|string $unitKerjaId): void
    {
        if (self::canViewCrossUnitData($user)) {
            return;
        }

        if (RbacRoles::userHasWarehousePusatAccess($user)) {
            return;
        }

        if (self::mustScopeToUnitKerja($user)) {
            $unitId = self::unitKerjaId($user);
            if ($unitId !== null && (int) $unitId === (int) $unitKerjaId) {
                return;
            }
        }

        abort(403, 'Anda tidak dapat mengakses data unit kerja ini.');
    }

    public static function assertCanAccessGudang(User $user, MasterGudang $gudang): void
    {
        self::assertCanAccessUnitKerjaData($user, (int) $gudang->id_unit_kerja);
    }

    public static function assertCanAccessPermintaan(User $user, PermintaanBarang $permintaan): void
    {
        self::assertCanAccessUnitKerjaData($user, (int) $permintaan->id_unit_kerja);
    }

    public static function assertCanAccessDistribusi(User $user, TransaksiDistribusi $distribusi): void
    {
        if (self::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user)) {
            return;
        }

        if (! self::mustScopeToUnitKerja($user)) {
            return;
        }

        $unitId = self::unitKerjaId($user);
        if ($unitId === null) {
            abort(403, 'Anda tidak dapat mengakses distribusi ini.');
        }

        $distribusi->loadMissing(['gudangAsal', 'gudangTujuan', 'permintaan']);
        $relatedUnits = array_filter([
            (int) ($distribusi->gudangAsal?->id_unit_kerja ?? 0),
            (int) ($distribusi->gudangTujuan?->id_unit_kerja ?? 0),
            (int) ($distribusi->permintaan?->id_unit_kerja ?? 0),
        ], fn (int $id) => $id > 0);

        if (! in_array($unitId, $relatedUnits, true)) {
            abort(403, 'Anda tidak dapat mengakses distribusi ini.');
        }
    }
}
