<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'module',
        'modul',
        'aksi',
        'kode_permission',
        'nama_permission',
        'group',
        'description',
        'sort_order',
    ];

    /**
     * Get permissions grouped by module
     */
    public static function getGroupedByModule(): array
    {
        return self::orderBy('module')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    /**
     * Get permissions grouped by group
     */
    public static function getGroupedByGroup(): array
    {
        return self::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->toArray();
    }
}
