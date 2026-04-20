<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftDetailDistribusi extends Model
{
    protected $table = 'draft_detail_distribusi';
    protected $primaryKey = 'id_draft_detail';
    public $timestamps = true;

    protected $fillable = [
        'id_permintaan',
        'id_inventory',
        'id_gudang_asal',
        'qty_distribusi',
        'id_satuan',
        'harga_satuan',
        'subtotal',
        'kategori_gudang',
        'created_by',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'qty_distribusi' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanBarang::class, 'id_permintaan', 'id_permintaan');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(DataInventory::class, 'id_inventory', 'id_inventory');
    }

    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_asal', 'id_gudang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
