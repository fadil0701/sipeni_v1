<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHistory extends Model
{
    protected $table = 'asset_histories';

    protected $fillable = [
        'id_register_aset',
        'event',
        'before_state',
        'after_state',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }
}
