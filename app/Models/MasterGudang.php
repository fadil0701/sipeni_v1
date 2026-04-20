<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterGudang extends Model
{
    protected $table = 'master_gudang';
    protected $primaryKey = 'id_gudang';
    public $timestamps = true;

    protected $fillable = [
        'id_unit_kerja',
        'nama_gudang',
        'jenis_gudang',
        'kategori_gudang',
    ];

    protected $casts = [
        'jenis_gudang' => 'string',
        'kategori_gudang' => 'string',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function dataInventory(): HasMany
    {
        return $this->hasMany(DataInventory::class, 'id_gudang', 'id_gudang');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'id_gudang', 'id_gudang');
    }

    public function dataStock(): HasMany
    {
        return $this->hasMany(DataStock::class, 'id_gudang', 'id_gudang');
    }

    public function distribusiAsal(): HasMany
    {
        return $this->hasMany(TransaksiDistribusi::class, 'id_gudang_asal', 'id_gudang');
    }

    public function distribusiTujuan(): HasMany
    {
        return $this->hasMany(TransaksiDistribusi::class, 'id_gudang_tujuan', 'id_gudang');
    }

    public function returAsal(): HasMany
    {
        return $this->hasMany(ReturBarang::class, 'id_gudang_asal', 'id_gudang');
    }

    public function returTujuan(): HasMany
    {
        return $this->hasMany(ReturBarang::class, 'id_gudang_tujuan', 'id_gudang');
    }
}
