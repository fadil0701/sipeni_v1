<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSubjenisBarang extends Model
{
    protected $table = 'master_subjenis_barang';
    protected $primaryKey = 'id_subjenis_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_jenis_barang',
        'kode_subjenis_barang',
        'nama_subjenis_barang',
    ];

    // Relationships
    public function jenisBarang(): BelongsTo
    {
        return $this->belongsTo(MasterJenisBarang::class, 'id_jenis_barang', 'id_jenis_barang');
    }

    public function dataBarang(): HasMany
    {
        return $this->hasMany(MasterDataBarang::class, 'id_subjenis_barang', 'id_subjenis_barang');
    }
}
