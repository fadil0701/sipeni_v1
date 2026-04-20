<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKodeBarang extends Model
{
    protected $table = 'master_kode_barang';
    protected $primaryKey = 'id_kode_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_aset',
        'kode_barang',
        'nama_kode_barang',
    ];

    // Relationships
    public function aset(): BelongsTo
    {
        return $this->belongsTo(MasterAset::class, 'id_aset', 'id_aset');
    }

    public function kategoriBarang(): HasMany
    {
        return $this->hasMany(MasterKategoriBarang::class, 'id_kode_barang', 'id_kode_barang');
    }
}
