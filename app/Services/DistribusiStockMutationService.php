<?php

namespace App\Services;

use App\Models\DataInventory;
use App\Models\DataStock;
use App\Models\DetailDistribusi;
use App\Models\InventoryItem;
use App\Models\RegisterAset;
use App\Models\TransaksiDistribusi;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Mutasi stok/inventory untuk alur distribusi (Opsi A):
 * - Persediaan/Farmasi: saat Kirim, data_stock asal turun DAN data_inventory ikut pindah/split ke gudang tujuan.
 * - Aset: saat Diterima, inventory_item + data_inventory diselaraskan ke gudang tujuan (register terpisah).
 */
class DistribusiStockMutationService
{
    public function __construct(
        private readonly StockGuardService $stockGuard
    ) {}

    /**
     * Saat Kirim SBBK: guard + kurangi data_stock asal + pindahkan/split inventory PERS/FARMASI ke tujuan.
     * Detail distribusi di-update ke id_inventory hasil pindah (jika di-split).
     */
    public function applyPersediaanFarmasiOnKirim(TransaksiDistribusi $distribusi): void
    {
        $distribusi->loadMissing(['detailDistribusi.inventory']);

        $idTujuan = (int) $distribusi->id_gudang_tujuan;
        $idAsal = (int) $distribusi->id_gudang_asal;
        if ($idTujuan <= 0 || $idAsal <= 0) {
            throw new RuntimeException('Gudang asal/tujuan distribusi tidak valid untuk mutasi stok.');
        }

        foreach ($distribusi->detailDistribusi as $detail) {
            $inventory = $detail->inventory;
            if (! $inventory) {
                continue;
            }

            if (! in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'], true)) {
                continue;
            }

            $qty = (float) $detail->qty_distribusi;
            $context = "pengiriman distribusi {$distribusi->no_sbbk}";

            $this->stockGuard->ensureInventoryQty((int) $inventory->id_inventory, $qty, $context);
            $this->stockGuard->ensureStockQty(
                (int) $inventory->id_data_barang,
                $idAsal,
                $qty,
                $context
            );

            $stockAsal = DataStock::query()
                ->where('id_data_barang', $inventory->id_data_barang)
                ->where('id_gudang', $idAsal)
                ->lockForUpdate()
                ->first();

            if ($stockAsal) {
                $stockAsal->qty_keluar = (float) $stockAsal->qty_keluar + $qty;
                $stockAsal->qty_akhir = (float) $stockAsal->qty_akhir - $qty;
                $stockAsal->last_updated = now();
                $stockAsal->save();
            }

            $relocated = $this->relocateOrSplitInventory($inventory->fresh(), $qty, $idTujuan, $context);
            if ((int) $relocated->id_inventory !== (int) $detail->id_inventory) {
                $detail->update(['id_inventory' => $relocated->id_inventory]);
            }
        }
    }

    /**
     * Guard ASET saat Kirim: pastikan qty inventory + jumlah inventory_item tersedia di gudang asal.
     */
    public function assertAsetAvailableOnKirim(TransaksiDistribusi $distribusi): void
    {
        $distribusi->loadMissing(['detailDistribusi.inventory']);
        $idAsal = (int) $distribusi->id_gudang_asal;
        $context = "pengiriman distribusi {$distribusi->no_sbbk}";

        foreach ($distribusi->detailDistribusi as $detail) {
            $inventory = $detail->inventory;
            if (! $inventory || $inventory->jenis_inventory !== 'ASET') {
                continue;
            }

            $qty = (float) $detail->qty_distribusi;
            $this->stockGuard->ensureInventoryQty((int) $inventory->id_inventory, $qty, $context);

            $availableItems = InventoryItem::query()
                ->where('id_inventory', $inventory->id_inventory)
                ->where('id_gudang', $idAsal)
                ->where('status_item', 'AKTIF')
                ->count();

            if ($availableItems < (int) ceil($qty)) {
                throw new RuntimeException(
                    "Item aset tidak cukup untuk {$context}. Dibutuhkan ".(int) ceil($qty).", tersedia {$availableItems} (inventory #{$inventory->id_inventory})."
                );
            }
        }
    }

    /**
     * Saat Diterima: pastikan data_inventory ASET berada di gudang tujuan (pindah/split),
     * selaras dengan inventory_item yang sudah dipindah saat auto-register.
     */
    public function relocateAsetInventoryOnDiterima(
        DataInventory $inventory,
        float $qty,
        int $idGudangTujuan,
        string $context
    ): DataInventory {
        if ($inventory->jenis_inventory !== 'ASET') {
            return $inventory;
        }

        if ((int) $inventory->id_gudang === $idGudangTujuan) {
            return $inventory;
        }

        return $this->relocateOrSplitInventory($inventory, $qty, $idGudangTujuan, $context);
    }

    /**
     * True jika id_item sudah punya register (anti double-register).
     */
    public static function registerExistsForItem(?int $idItem): bool
    {
        if (! $idItem || $idItem <= 0) {
            return false;
        }

        if (! Schema::hasColumn('register_aset', 'id_item')) {
            return false;
        }

        return RegisterAset::query()->where('id_item', $idItem)->exists();
    }

    /**
     * Pindahkan seluruh qty atau split sebagian ke gudang tujuan.
     * Mengembalikan baris inventory yang menampung qty yang dipindah.
     */
    public function relocateOrSplitInventory(
        DataInventory $inventory,
        float $qty,
        int $idGudangTujuan,
        string $context
    ): DataInventory {
        $inventory = DataInventory::query()->whereKey($inventory->id_inventory)->lockForUpdate()->firstOrFail();

        $eps = 0.00001;
        $qtyInv = (float) $inventory->qty_input;

        if ($qty <= 0) {
            throw new RuntimeException("Qty mutasi tidak valid untuk {$context}.");
        }

        if ($qty - $qtyInv > $eps) {
            throw new RuntimeException(
                "Qty ({$qty}) melebihi inventory #{$inventory->id_inventory} ({$qtyInv}) untuk {$context}."
            );
        }

        if ((int) $inventory->id_gudang === $idGudangTujuan) {
            return $inventory;
        }

        $harga = (float) $inventory->harga_satuan;

        if (abs($qty - $qtyInv) < $eps) {
            $inventory->id_gudang = $idGudangTujuan;
            $inventory->save();

            return $inventory->fresh();
        }

        $inventory->qty_input = $qtyInv - $qty;
        $inventory->total_harga = (float) $inventory->qty_input * $harga;
        $inventory->save();

        $baru = $inventory->replicate();
        $baru->id_gudang = $idGudangTujuan;
        $baru->qty_input = $qty;
        $baru->total_harga = $qty * $harga;
        $baru->save();

        return $baru->fresh();
    }
}
