<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataInventory extends Model
{
    protected $table = 'data_inventory';
    protected $primaryKey = 'id_inventory';
    public $timestamps = true;

    protected $fillable = [
        'id_data_barang',
        'id_gudang',
        'id_anggaran',
        'id_sub_kegiatan',
        'jenis_inventory',
        'jenis_barang',
        'tahun_anggaran',
        'qty_input',
        'id_satuan',
        'harga_satuan',
        'total_harga',
        'merk',
        'tipe',
        'spesifikasi',
        'tahun_produksi',
        'nama_penyedia',
        'no_seri',
        'no_batch',
        'tanggal_kedaluwarsa',
        'status_inventory',
        'upload_foto',
        'upload_dokumen',
        'auto_qr_code',
        'created_by',
    ];

    protected $casts = [
        'jenis_inventory' => 'string',
        'status_inventory' => 'string',
        'qty_input' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'tanggal_kedaluwarsa' => 'date',
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

    public function sumberAnggaran(): BelongsTo
    {
        return $this->belongsTo(MasterSumberAnggaran::class, 'id_anggaran', 'id_anggaran');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'id_inventory', 'id_inventory');
    }

    public function registerAset(): HasMany
    {
        return $this->hasMany(RegisterAset::class, 'id_inventory', 'id_inventory');
    }

    public function detailDistribusi(): HasMany
    {
        return $this->hasMany(DetailDistribusi::class, 'id_inventory', 'id_inventory');
    }

    public function detailPenerimaan(): HasMany
    {
        return $this->hasMany(DetailPenerimaanBarang::class, 'id_inventory', 'id_inventory');
    }

    public function detailRetur(): HasMany
    {
        return $this->hasMany(DetailReturBarang::class, 'id_inventory', 'id_inventory');
    }

    public function historyLokasi(): HasMany
    {
        return $this->hasMany(HistoryLokasi::class, 'id_inventory', 'id_inventory');
    }
}
