<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryLokasi extends Model
{
    protected $table = 'history_lokasi';
    protected $primaryKey = 'id_history';
    public $timestamps = true;

    protected $fillable = [
        'id_inventory',
        'id_gudang_asal',
        'id_gudang_tujuan',
        'id_transaksi',
        'jenis_transaksi',
        'tanggal_transaksi',
        'qty',
        'id_satuan',
    ];

    protected $casts = [
        'jenis_transaksi' => 'string',
        'tanggal_transaksi' => 'datetime',
        'qty' => 'decimal:2',
    ];

    // Relationships
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(DataInventory::class, 'id_inventory', 'id_inventory');
    }

    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_asal', 'id_gudang');
    }

    public function gudangTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_tujuan', 'id_gudang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }
}
