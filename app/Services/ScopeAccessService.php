<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeAccessService
{
    public static function isPusat(User $user): bool
    {
        return $user->roles()->where('level_akses', 'pusat')->exists();
    }

    public static function userUnitKerjaId(User $user): ?int
    {
        $pegawai = $user->pegawai;
        if (! $pegawai) {
            return null;
        }

        $raw = $pegawai->unit_kerja_id ?? $pegawai->id_unit_kerja ?? null;

        return $raw === null ? null : (int) $raw;
    }

    public static function applyUnitScope(Builder $query, User $user, string $column = 'id_unit_kerja'): Builder
    {
        if (self::isPusat($user)) {
            return $query;
        }

        $unitId = self::userUnitKerjaId($user);
        if ($unitId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $unitId);
    }
}
