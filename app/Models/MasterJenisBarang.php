<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterJenisBarang extends Model
{
    protected $table = 'master_jenis_barang';
    protected $primaryKey = 'id_jenis_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_kategori_barang',
        'kode_jenis_barang',
        'nama_jenis_barang',
    ];

    // Relationships
    public function kategoriBarang(): BelongsTo
    {
        return $this->belongsTo(MasterKategoriBarang::class, 'id_kategori_barang', 'id_kategori_barang');
    }

    public function subjenisBarang(): HasMany
    {
        return $this->hasMany(MasterSubjenisBarang::class, 'id_jenis_barang', 'id_jenis_barang');
    }
}
