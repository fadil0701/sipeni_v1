<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailDistribusi extends Model
{
    protected $table = 'detail_distribusi';
    protected $primaryKey = 'id_detail_distribusi';
    public $timestamps = true;

    protected $fillable = [
        'id_distribusi',
        'id_inventory',
        'qty_distribusi',
        'id_satuan',
        'harga_satuan',
        'subtotal',
        'keterangan',
    ];

    protected $casts = [
        'qty_distribusi' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(TransaksiDistribusi::class, 'id_distribusi', 'id_distribusi');
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
