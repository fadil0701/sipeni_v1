<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterDataBarangPermendagri extends Model
{
    protected $table = 'master_data_barang_permendagri';

    protected $fillable = [
        'id_data_barang',
        'kode_barang_108',
        'kode_akun',
        'kode_kelompok',
        'kode_jenis_108',
        'kode_objek',
        'kode_rincian_objek',
        'kode_sub_rincian_objek',
        'kode_sub_sub_rincian_objek',
        'sumber_mapping',
        'status_validasi',
        'catatan',
    ];

    public function dataBarang(): BelongsTo
    {
        return $this->belongsTo(MasterDataBarang::class, 'id_data_barang', 'id_data_barang');
    }
}

