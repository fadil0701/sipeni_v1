<?php

namespace App\Services;

use App\Models\DataInventory;
use App\Models\DataStock;
use RuntimeException;

class StockGuardService
{
    public function ensureInventoryQty(int $idInventory, float $qtyNeeded, string $context): void
    {
        $inventory = DataInventory::query()->find($idInventory);
        if (! $inventory) {
            throw new RuntimeException("Inventory #{$idInventory} tidak ditemukan ({$context}).");
        }

        if ((float) $inventory->qty_input < $qtyNeeded) {
            $available = number_format((float) $inventory->qty_input, 2, ',', '.');
            $needed = number_format($qtyNeeded, 2, ',', '.');
            throw new RuntimeException("Stok inventory tidak cukup untuk {$context}. Dibutuhkan {$needed}, tersedia {$available}.");
        }
    }

    public function ensureStockQty(int $idDataBarang, int $idGudang, float $qtyNeeded, string $context): void
    {
        $stock = DataStock::query()
            ->where('id_data_barang', $idDataBarang)
            ->where('id_gudang', $idGudang)
            ->first();

        if (! $stock) {
            throw new RuntimeException("Data stok gudang tidak ditemukan untuk {$context}.");
        }

        if ((float) $stock->qty_akhir < $qtyNeeded) {
            $available = number_format((float) $stock->qty_akhir, 2, ',', '.');
            $needed = number_format($qtyNeeded, 2, ',', '.');
            throw new RuntimeException("Stok gudang tidak cukup untuk {$context}. Dibutuhkan {$needed}, tersedia {$available}.");
        }
    }
}
