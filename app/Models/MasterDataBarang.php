<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterDataBarang extends Model
{
    protected $table = 'master_data_barang';
    protected $primaryKey = 'id_data_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_subjenis_barang',
        'id_satuan',
        'kode_data_barang',
        'nama_barang',
        'deskripsi',
        'upload_foto',
        'foto_barang',
    ];

    // Relationships
    public function subjenisBarang(): BelongsTo
    {
        return $this->belongsTo(MasterSubjenisBarang::class, 'id_subjenis_barang', 'id_subjenis_barang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }

    public function dataInventory(): HasMany
    {
        return $this->hasMany(DataInventory::class, 'id_data_barang', 'id_data_barang');
    }

    public function dataStock(): HasMany
    {
        return $this->hasMany(DataStock::class, 'id_data_barang', 'id_data_barang');
    }

    public function detailPermintaan(): HasMany
    {
        return $this->hasMany(DetailPermintaanBarang::class, 'id_data_barang', 'id_data_barang');
    }
}
