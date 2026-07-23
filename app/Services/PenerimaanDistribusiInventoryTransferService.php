<?php

namespace App\Services;

use App\Models\DataInventory;
use App\Models\PenerimaanBarang;
use App\Models\TransaksiDistribusi;
use RuntimeException;

/**
 * Setelah penerimaan distribusi disahkan (DITERIMA):
 * - Persediaan/Farmasi biasanya sudah pindah ke gudang tujuan saat Kirim (Opsi A).
 *   Service ini idempotent: skip jika sudah di tujuan; selesaikan sisa jika belum.
 * - Aset ditangani di PenerimaanBarangController (inventory_item + register + relocate inventory).
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
        $mutation = app(DistribusiStockMutationService::class);
        $context = "penerimaan {$penerimaan->no_penerimaan}";

        foreach ($penerimaan->detailPenerimaan as $detail) {
            $inventory = DataInventory::query()->find($detail->id_inventory);
            if (! $inventory) {
                continue;
            }

            if (! in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'], true)) {
                continue;
            }

            // Sudah dipindah saat Kirim — tidak perlu aksi.
            if ((int) $inventory->id_gudang === $idTujuan) {
                continue;
            }

            $qtyReceived = (float) $detail->qty_diterima;
            if ($qtyReceived <= 0) {
                continue;
            }

            // Fallback untuk data lama yang belum dimutasi saat Kirim.
            try {
                $relocated = $mutation->relocateOrSplitInventory($inventory, $qtyReceived, $idTujuan, $context);
                if ((int) $relocated->id_inventory !== (int) $detail->id_inventory) {
                    $detail->update(['id_inventory' => $relocated->id_inventory]);
                }
            } catch (RuntimeException $e) {
                throw $e;
            }
        }
    }
}
