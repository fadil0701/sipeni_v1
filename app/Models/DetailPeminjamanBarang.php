<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPeminjamanBarang extends Model
{
    protected $table = 'detail_peminjaman_barang';
    protected $primaryKey = 'id_detail_peminjaman';

    protected $fillable = [
        'id_peminjaman',
        'id_data_barang',
        'qty_pinjam',
        'id_satuan',
        'kondisi_serah',
        'kondisi_kembali',
        'keterangan',
    ];

    protected $casts = [
        'qty_pinjam' => 'decimal:2',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(PeminjamanBarang::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function dataBarang(): BelongsTo
    {
        return $this->belongsTo(MasterDataBarang::class, 'id_data_barang', 'id_data_barang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }
}

