<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenerimaanBarang extends Model
{
    protected $table = 'detail_penerimaan_barang';
    protected $primaryKey = 'id_detail_penerimaan';
    public $timestamps = true;

    protected $fillable = [
        'id_penerimaan',
        'id_inventory',
        'qty_diterima',
        'id_satuan',
        'keterangan',
    ];

    protected $casts = [
        'qty_diterima' => 'decimal:2',
    ];

    // Relationships
    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class, 'id_penerimaan', 'id_penerimaan');
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
