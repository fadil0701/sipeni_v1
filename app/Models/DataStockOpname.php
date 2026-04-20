<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataStockOpname extends Model
{
    protected $table = 'data_stock_opname';
    protected $primaryKey = 'id_opname';
    public $timestamps = true;

    protected $fillable = [
        'id_data_barang',
        'id_gudang',
        'tanggal_opname',
        'qty_sistem',
        'qty_fisik',
        'selisih',
        'keterangan',
        'id_petugas',
    ];

    protected $casts = [
        'tanggal_opname' => 'date',
        'qty_sistem' => 'decimal:2',
        'qty_fisik' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    // Relationships
    public function dataBarang(): BelongsTo
    {
        return $this->belongsTo(MasterDataBarang::class, 'id_data_barang', 'id_data_barang');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang', 'id_gudang');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id_petugas', 'id');
    }
}
