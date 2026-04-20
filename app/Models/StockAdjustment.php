<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustment';
    protected $primaryKey = 'id_adjustment';
    public $timestamps = true;

    protected $fillable = [
        'id_stock',
        'id_data_barang',
        'id_gudang',
        'tanggal_adjustment',
        'qty_sebelum',
        'qty_sesudah',
        'qty_selisih',
        'jenis_adjustment',
        'alasan',
        'keterangan',
        'id_petugas',
        'status',
        'id_approver',
        'tanggal_approval',
        'catatan_approval',
    ];

    protected $casts = [
        'tanggal_adjustment' => 'date',
        'qty_sebelum' => 'decimal:2',
        'qty_sesudah' => 'decimal:2',
        'qty_selisih' => 'decimal:2',
        'jenis_adjustment' => 'string',
        'status' => 'string',
        'tanggal_approval' => 'datetime',
    ];

    // Relationships
    public function stock(): BelongsTo
    {
        return $this->belongsTo(DataStock::class, 'id_stock', 'id_stock');
    }

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
        return $this->belongsTo(User::class, 'id_petugas', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver', 'id');
    }
}
