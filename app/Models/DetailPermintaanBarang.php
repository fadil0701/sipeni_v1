<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPermintaanBarang extends Model
{
    protected $table = 'detail_permintaan_barang';
    protected $primaryKey = 'id_detail_permintaan';
    public $timestamps = true;

    protected $fillable = [
        'id_permintaan',
        'id_data_barang',
        'deskripsi_barang',
        'qty_diminta',
        'id_satuan',
        'keterangan',
    ];

    protected $casts = [
        'qty_diminta' => 'decimal:2',
    ];

    // Relationships
    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanBarang::class, 'id_permintaan', 'id_permintaan');
    }

    public function dataBarang(): BelongsTo
    {
        return $this->belongsTo(MasterDataBarang::class, 'id_data_barang', 'id_data_barang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }

    /**
     * Nama barang: dari master jika ada id_data_barang, else deskripsi_barang (permintaan lainnya).
     */
    public function getNamaBarangDisplayAttribute(): string
    {
        if ($this->id_data_barang && $this->relationLoaded('dataBarang') && $this->dataBarang) {
            return $this->dataBarang->nama_barang ?? (string) $this->deskripsi_barang;
        }
        return (string) ($this->deskripsi_barang ?? '-');
    }

    /** Apakah baris ini permintaan lainnya (freetext, tidak dari master). */
    public function getIsPermintaanLainnyaAttribute(): bool
    {
        return empty($this->id_data_barang) && ! empty($this->deskripsi_barang);
    }
}
