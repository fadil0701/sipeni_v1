<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class RegisterAset extends Model
{
    protected $table = 'register_aset';
    protected $primaryKey = 'id_register_aset';
    public $timestamps = true;

    protected $fillable = [
        'id_inventory',
        'id_item',
        'id_unit_kerja',
        'id_ruangan',
        'nomor_register',
        'kondisi_aset',
        'tanggal_perolehan',
        'status_aset',
    ];

    protected $casts = [
        'kondisi_aset' => 'string',
        'status_aset' => 'string',
        'tanggal_perolehan' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (RegisterAset $registerAset) {
            // Guard sinkronisasi referensi item:
            // pastikan id_item dan id_inventory tetap konsisten.
            $registerAset->syncInventoryReferences();
        });
    }

    public function syncInventoryReferences(): void
    {
        $hasIdItemColumn = Schema::hasColumn($this->getTable(), 'id_item');
        if (! $hasIdItemColumn) {
            return;
        }

        $inventoryItem = null;

        if ($this->id_item) {
            $inventoryItem = InventoryItem::query()->find($this->id_item);
        }

        // Fallback untuk data lama yang id_item masih null.
        if (! $inventoryItem && $this->id_inventory) {
            if (! empty($this->nomor_register)) {
                $inventoryItem = InventoryItem::query()
                    ->where('id_inventory', $this->id_inventory)
                    ->where('kode_register', $this->nomor_register)
                    ->first();
            }

            if (! $inventoryItem) {
                $inventoryItem = InventoryItem::query()
                    ->where('id_inventory', $this->id_inventory)
                    ->orderBy('id_item')
                    ->first();
            }
        }

        if (! $inventoryItem) {
            return;
        }

        $this->id_item = $inventoryItem->id_item;
        $this->id_inventory = $inventoryItem->id_inventory;
    }

    // Relationships
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(DataInventory::class, 'id_inventory', 'id_inventory');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'id_item', 'id_item');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(MasterRuangan::class, 'id_ruangan', 'id_ruangan');
    }

    public function kartuInventarisRuangan(): HasMany
    {
        return $this->hasMany(KartuInventarisRuangan::class, 'id_register_aset', 'id_register_aset');
    }

    public function mutasiAset(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Permintaan pemeliharaan untuk aset ini
     */
    public function permintaanPemeliharaan(): HasMany
    {
        return $this->hasMany(PermintaanPemeliharaan::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Jadwal maintenance untuk aset ini
     */
    public function jadwalMaintenance(): HasMany
    {
        return $this->hasMany(JadwalMaintenance::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Kalibrasi untuk aset ini
     */
    public function kalibrasi(): HasMany
    {
        return $this->hasMany(KalibrasiAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Service report untuk aset ini
     */
    public function serviceReport(): HasMany
    {
        return $this->hasMany(ServiceReport::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Riwayat pemeliharaan untuk aset ini
     */
    public function riwayatPemeliharaan(): HasMany
    {
        return $this->hasMany(RiwayatPemeliharaan::class, 'id_register_aset', 'id_register_aset');
    }
}
