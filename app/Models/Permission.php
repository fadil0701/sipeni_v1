<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'module',
        'group',
        'description',
        'sort_order',
    ];

    /**
     * The roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

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
