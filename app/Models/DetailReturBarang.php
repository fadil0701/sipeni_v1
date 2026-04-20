<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailReturBarang extends Model
{
    protected $table = 'detail_retur_barang';
    protected $primaryKey = 'id_detail_retur';
    public $timestamps = true;

    protected $fillable = [
        'id_retur',
        'id_inventory',
        'qty_retur',
        'id_satuan',
        'alasan_retur_item',
        'keterangan',
    ];

    protected $casts = [
        'qty_retur' => 'decimal:2',
    ];

    // Relationships
    public function returBarang(): BelongsTo
    {
        return $this->belongsTo(ReturBarang::class, 'id_retur', 'id_retur');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(DataInventory::class, 'id_inventory', 'id_inventory');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }
}



