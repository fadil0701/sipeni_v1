<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'sort_order',
    ];

    /**
     * The users that have access to this module
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_modules', 'module', 'user_id')
            ->withTimestamps();
    }

    /**
     * The permissions that belong to this module
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'module', 'name');
    }
}
