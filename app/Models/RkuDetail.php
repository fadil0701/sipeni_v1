<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkuDetail extends Model
{
    protected $table = 'rku_detail';
    protected $primaryKey = 'id_rku_detail';
    public $timestamps = true;

    protected $fillable = [
        'id_rku',
        'id_data_barang',
        'qty_rencana',
        'id_satuan',
        'harga_satuan_rencana',
        'subtotal_rencana',
        'keterangan',
    ];

    protected $casts = [
        'qty_rencana' => 'decimal:2',
        'harga_satuan_rencana' => 'decimal:2',
        'subtotal_rencana' => 'decimal:2',
    ];

    // Relationships
    public function rkuHeader(): BelongsTo
    {
        return $this->belongsTo(RkuHeader::class, 'id_rku', 'id_rku');
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
