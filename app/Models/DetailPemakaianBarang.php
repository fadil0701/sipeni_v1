<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPemakaianBarang extends Model
{
    protected $table = 'detail_pemakaian_barang';
    protected $primaryKey = 'id_detail_pemakaian';
    public $timestamps = true;

    protected $fillable = [
        'id_pemakaian',
        'id_inventory',
        'qty_pemakaian',
        'id_satuan',
        'alasan_pemakaian_item',
        'keterangan',
    ];

    protected $casts = [
        'qty_pemakaian' => 'decimal:2',
    ];

    // Relationships
    public function pemakaianBarang(): BelongsTo
    {
        return $this->belongsTo(PemakaianBarang::class, 'id_pemakaian', 'id_pemakaian');
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
