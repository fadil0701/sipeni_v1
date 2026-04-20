<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSubKegiatan extends Model
{
    protected $table = 'master_sub_kegiatan';
    protected $primaryKey = 'id_sub_kegiatan';
    public $timestamps = true;

    protected $fillable = [
        'id_kegiatan',
        'nama_sub_kegiatan',
        'kode_sub_kegiatan',
    ];

    // Relationships
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterKegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    public function dataInventory(): HasMany
    {
        return $this->hasMany(DataInventory::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }
}
