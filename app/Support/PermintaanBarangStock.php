<?php

namespace App\Support;

use App\Models\DataStock;
use App\Models\PermintaanBarang;

/**
 * Agregat stok gudang pusat per baris detail permintaan.
 * Angka "total" selaras dengan validasi store/update (gudang Farmasi jika barang di inventory Farmasi, else Persediaan).
 * Rincian per gudang tetap dari getStockPerGudangPusat untuk tampilan.
 */
final class PermintaanBarangStock
{
    /**
     * @return array<int, array{total: float, per_gudang: mixed}>
     */
    public static function stockDataForDetails(PermintaanBarang $permintaan): array
    {
        $stockPersediaanIds = \app('db')->table('data_inventory')
            ->where('jenis_inventory', 'PERSEDIAAN')
            ->where('status_inventory', 'AKTIF')
            ->whereNotNull('id_data_barang')
            ->distinct()
            ->pluck('id_data_barang')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $stockFarmasiIds = \app('db')->table('data_inventory')
            ->where('jenis_inventory', 'FARMASI')
            ->where('status_inventory', 'AKTIF')
            ->whereNotNull('id_data_barang')
            ->distinct()
            ->pluck('id_data_barang')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $stockData = [];
        foreach ($permintaan->detailPermintaan()->get() as $detail) {
            if (! $detail->id_data_barang) {
                $stockData[$detail->id_detail_permintaan] = [
                    'total' => 0.0,
                    'per_gudang' => DataStock::getStockPerGudangPusat(0),
                ];

                continue;
            }

            $idDataBarang = (int) $detail->id_data_barang;
            $perGudangPusat = DataStock::getStockPerGudangPusat($detail->id_data_barang);
            $sumPusat = (float) $perGudangPusat->sum('qty_akhir');

            if (in_array($idDataBarang, $stockFarmasiIds, true)) {
                $effective = (float) DataStock::getStockGudangPusat($detail->id_data_barang, 'FARMASI');
            } elseif (in_array($idDataBarang, $stockPersediaanIds, true)) {
                $effective = (float) DataStock::getStockGudangPusat($detail->id_data_barang, 'PERSEDIAAN');
            } else {
                $effective = $sumPusat;
            }

            $stockData[$detail->id_detail_permintaan] = [
                'total' => $effective,
                'per_gudang' => $perGudangPusat,
            ];
        }

        return $stockData;
    }
}
