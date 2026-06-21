<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'kode_role',
        'guard_name',
        'display_name',
        'nama_role',
        'level_akses',
        'description',
        'is_active',
        'is_deprecated',
        'maps_to_role',
    ];

    /**
     * Cek apakah role punya permission (nama string) — kompatibel kode lama.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public function workflowPermissions(): HasMany
    {
        return $this->hasMany(WorkflowPermission::class, 'role_id');
    }
}
