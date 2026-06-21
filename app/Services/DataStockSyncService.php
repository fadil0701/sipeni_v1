<?php

namespace App\Services;

use App\Models\DataInventory;
use App\Models\DataStock;

class DataStockSyncService
{
    /**
     * Sinkronkan DataStock dengan DataInventory eligible untuk stok:
     * PERSEDIAAN/FARMASI + ASET yang belum memiliki nomor register.
     * qty_akhir = total qty_input dari semua DataInventory aktif per barang + gudang.
     */
    public static function syncFromInventory(): void
    {
        $inventories = DataInventory::query()
            ->where(function ($q) {
                StockMerkBreakdownService::applyStockEligibleInventoryFilter($q);
            })
            ->where('status_inventory', 'AKTIF')
            ->with(['dataBarang', 'gudang', 'satuan'])
            ->get();

        $stockData = $inventories->groupBy(function ($inv) {
            return $inv->id_data_barang.'_'.$inv->id_gudang;
        })->map(function ($group) {
            $first = $group->first();

            return [
                'id_data_barang' => $first->id_data_barang,
                'id_gudang' => $first->id_gudang,
                'qty_total' => $group->sum('qty_input'),
                'id_satuan' => $first->id_satuan,
            ];
        });

        foreach ($stockData as $data) {
            $stock = DataStock::firstOrNew([
                'id_data_barang' => $data['id_data_barang'],
                'id_gudang' => $data['id_gudang'],
            ]);

            $existingQtyKeluar = $stock->exists ? (float) $stock->qty_keluar : 0.0;

            $stock->qty_akhir = $data['qty_total'];

            if (! $stock->exists) {
                $stock->qty_awal = 0;
                $stock->qty_masuk = $data['qty_total'];
                $stock->qty_keluar = 0;
                $stock->id_satuan = $data['id_satuan'];
            } else {
                $stock->qty_masuk = (float) $stock->qty_akhir + $existingQtyKeluar;
                $stock->qty_keluar = $existingQtyKeluar;
            }

            $stock->last_updated = now();
            $stock->save();
        }

        if ($stockData->count() > 0) {
            $activeBarangGudang = $stockData->map(fn ($data) => $data['id_data_barang'].'_'.$data['id_gudang'])->toArray();

            DataStock::whereHas('dataBarang', function ($q) {
                $q->whereHas('dataInventory', function ($invQ) {
                    StockMerkBreakdownService::applyStockEligibleInventoryFilter($invQ);
                });
            })->get()->each(function ($stock) use ($activeBarangGudang) {
                $key = $stock->id_data_barang.'_'.$stock->id_gudang;
                if (! in_array($key, $activeBarangGudang, true)) {
                    $hasActiveInventory = DataInventory::where('id_data_barang', $stock->id_data_barang)
                        ->where('id_gudang', $stock->id_gudang)
                        ->where(function ($q) {
                            StockMerkBreakdownService::applyStockEligibleInventoryFilter($q);
                        })
                        ->where('status_inventory', 'AKTIF')
                        ->exists();

                    if (! $hasActiveInventory) {
                        $stock->delete();
                    }
                }
            });
        }
    }
}
