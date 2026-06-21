<?php

namespace App\Services;

use App\Models\DataInventory;
use App\Models\PenerimaanBarang;
use App\Models\TransaksiDistribusi;
use RuntimeException;

/**
 * Setelah penerimaan distribusi disahkan (DITERIMA), pindahkan baris data_inventory
 * PERSEDIAAN/FARMASI ke gudang tujuan agar Data Stok unit menampilkan mutasi yang benar.
 * (Aset mengikuti alur InventoryItem + RegisterAset di PenerimaanBarangController.)
 */
class PenerimaanDistribusiInventoryTransferService
{
    public static function transferPersediaanFarmasiToGudangTujuanAfterDiterima(
        PenerimaanBarang $penerimaan,
        TransaksiDistribusi $distribusi
    ): void {
        $idTujuan = (int) $distribusi->id_gudang_tujuan;
        if ($idTujuan <= 0) {
            return;
        }

        $penerimaan->loadMissing(['detailPenerimaan']);

        foreach ($penerimaan->detailPenerimaan as $detail) {
            $inventory = DataInventory::query()->find($detail->id_inventory);
            if (! $inventory) {
                continue;
            }

            if (! in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'], true)) {
                continue;
            }

            if ((int) $inventory->id_gudang === $idTujuan) {
                continue;
            }

            $qtyReceived = (float) $detail->qty_diterima;
            $qtyInv = (float) $inventory->qty_input;

            if ($qtyReceived <= 0) {
                continue;
            }

            if ($qtyReceived - $qtyInv > 0.00001) {
                throw new RuntimeException(
                    "Qty diterima ({$qtyReceived}) melebihi qty inventory #{$inventory->id_inventory} ({$qtyInv}) untuk penerimaan #{$penerimaan->id_penerimaan}."
                );
            }

            $eps = 0.00001;
            if (abs($qtyReceived - $qtyInv) < $eps) {
                $inventory->update(['id_gudang' => $idTujuan]);

                continue;
            }

            $sisaAsal = $qtyInv - $qtyReceived;
            if ($sisaAsal < -$eps) {
                throw new RuntimeException('Kesalahan perhitungan qty parsial pada inventory #'.$inventory->id_inventory);
            }

            $harga = (float) $inventory->harga_satuan;
            $inventory->qty_input = $sisaAsal;
            $inventory->total_harga = $sisaAsal * $harga;
            $inventory->save();

            $baru = $inventory->replicate();
            $baru->id_gudang = $idTujuan;
            $baru->qty_input = $qtyReceived;
            $baru->total_harga = $qtyReceived * $harga;
            $baru->save();
        }
    }
}
