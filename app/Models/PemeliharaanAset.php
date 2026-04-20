<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeliharaanAset extends Model
{
    protected $table = 'pemeliharaan_aset';
    protected $primaryKey = 'id_pemeliharaan';
    public $timestamps = true;

    protected $fillable = [
        'id_item',
        'jenis_pemeliharaan',
        'tanggal',
        'vendor',
        'biaya',
        'laporan_service',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'jenis_pemeliharaan' => 'string',
        'tanggal' => 'date',
        'biaya' => 'decimal:2',
    ];

    // Relationships
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'id_item', 'id_item');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }
}
