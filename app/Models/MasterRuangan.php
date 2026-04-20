<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterRuangan extends Model
{
    protected $table = 'master_ruangan';
    protected $primaryKey = 'id_ruangan';
    public $timestamps = true;

    protected $fillable = [
        'id_unit_kerja',
        'kode_ruangan',
        'nama_ruangan',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'id_ruangan', 'id_ruangan');
    }
}
