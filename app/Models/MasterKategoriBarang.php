<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKategoriBarang extends Model
{
    protected $table = 'master_kategori_barang';
    protected $primaryKey = 'id_kategori_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_kode_barang',
        'kode_kategori_barang',
        'nama_kategori_barang',
    ];

    // Relationships
    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(MasterKodeBarang::class, 'id_kode_barang', 'id_kode_barang');
    }

    public function jenisBarang(): HasMany
    {
        return $this->hasMany(MasterJenisBarang::class, 'id_kategori_barang', 'id_kategori_barang');
    }
}
