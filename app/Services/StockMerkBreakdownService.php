<?php

namespace App\Services;

use App\Models\DataInventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockMerkBreakdownService
{
    /**
     * Filter inventory yang dihitung ke stok (selaras DataStockController).
     */
    public static function applyStockEligibleInventoryFilter($query): void
    {
        $query->where(function ($invQ) {
            $invQ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI'])
                ->orWhere(function ($asetQ) {
                    $asetQ->where('jenis_inventory', 'ASET')
                        ->where(function ($regQ) {
                            $regQ->whereDoesntHave('registerAset')
                                ->orWhereHas('registerAset', function ($r) {
                                    $r->whereNull('nomor_register')
                                        ->orWhere('nomor_register', '');
                                });
                        });
                });
        });
    }

    /**
     * Kelompokkan qty inventory aktif per merk untuk satu kombinasi barang + gudang.
     *
     * @return Collection<int, object{merk_label: string, qty_total: float, line_count: int, lines: Collection}>
     */
    public static function breakdownByMerk(int $idDataBarang, int $idGudang): Collection
    {
        $inventories = DataInventory::query()
            ->where('id_data_barang', $idDataBarang)
            ->where('id_gudang', $idGudang)
            ->where('status_inventory', 'AKTIF')
            ->where(function ($q) {
                self::applyStockEligibleInventoryFilter($q);
            })
            ->orderBy('merk')
            ->orderBy('id_inventory')
            ->get(['id_inventory', 'merk', 'qty_input', 'no_batch', 'tanggal_kedaluwarsa', 'jenis_inventory']);

        self::enrichInventoriesWithQtyMasukKeluar($inventories);

        return $inventories
            ->groupBy(function ($inv) {
                $m = trim((string) $inv->merk);

                return $m === '' ? '__EMPTY__' : $m;
            })
            ->map(function ($group, $merkKey) {
                return (object) [
                    'merk_key' => $merkKey,
                    'merk_label' => $merkKey === '__EMPTY__' ? '(Tanpa merk)' : (string) $merkKey,
                    'qty_total' => (float) $group->sum(fn ($i) => (float) $i->qty_input),
                    'line_count' => $group->count(),
                    'lines' => $group->values(),
                ];
            })
            ->values();
    }

    public static function sumBreakdownQty(Collection $breakdownRows): float
    {
        return (float) $breakdownRows->sum(fn ($r) => $r->qty_total);
    }

    /**
     * Qty stock masuk: total penerimaan terverifikasi (DITERIMA) per baris inventory.
     * Qty stock keluar: distribusi status selesai + pemakaian disetujui (selaras mutasi stok umum).
     */
    private static function enrichInventoriesWithQtyMasukKeluar(Collection $inventories): void
    {
        $ids = $inventories->pluck('id_inventory')->unique()->values()->all();
        $totals = self::inventoryMovementTotalsByInventoryId($ids);
        foreach ($inventories as $inv) {
            $id = (int) $inv->id_inventory;
            $inv->qty_stock_masuk = (float) ($totals[$id]['masuk'] ?? 0.0);
            $inv->qty_stock_keluar = (float) ($totals[$id]['keluar'] ?? 0.0);
        }
    }

    /**
     * @param  array<int>  $inventoryIds
     * @return array<int, array{masuk: float, keluar: float}>
     */
    private static function inventoryMovementTotalsByInventoryId(array $inventoryIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $inventoryIds)));
        if ($ids === []) {
            return [];
        }

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = ['masuk' => 0.0, 'keluar' => 0.0];
        }

        if (! self::dbTableMissing('detail_penerimaan_barang') && ! self::dbTableMissing('penerimaan_barang')) {
            $masuk = DB::table('detail_penerimaan_barang as dpb')
                ->join('penerimaan_barang as pb', 'pb.id_penerimaan', '=', 'dpb.id_penerimaan')
                ->whereIn('dpb.id_inventory', $ids)
                ->where('pb.status_penerimaan', 'DITERIMA')
                ->groupBy('dpb.id_inventory')
                ->selectRaw('dpb.id_inventory as id_inventory, SUM(dpb.qty_diterima) as total')
                ->pluck('total', 'id_inventory');

            foreach ($masuk as $id => $total) {
                $id = (int) $id;
                if (isset($out[$id])) {
                    $out[$id]['masuk'] = (float) $total;
                }
            }
        }

        if (! self::dbTableMissing('detail_distribusi') && ! self::dbTableMissing('transaksi_distribusi')) {
            $keluarDist = DB::table('detail_distribusi as dd')
                ->join('transaksi_distribusi as td', 'td.id_distribusi', '=', 'dd.id_distribusi')
                ->whereIn('dd.id_inventory', $ids)
                ->whereRaw('LOWER(TRIM(CAST(td.status_distribusi AS CHAR))) = ?', ['selesai'])
                ->groupBy('dd.id_inventory')
                ->selectRaw('dd.id_inventory as id_inventory, SUM(dd.qty_distribusi) as total')
                ->pluck('total', 'id_inventory');

            foreach ($keluarDist as $id => $total) {
                $id = (int) $id;
                if (isset($out[$id])) {
                    $out[$id]['keluar'] += (float) $total;
                }
            }
        }

        if (! self::dbTableMissing('detail_pemakaian_barang') && ! self::dbTableMissing('pemakaian_barang')) {
            $keluarPakai = DB::table('detail_pemakaian_barang as dpm')
                ->join('pemakaian_barang as p', 'p.id_pemakaian', '=', 'dpm.id_pemakaian')
                ->whereIn('dpm.id_inventory', $ids)
                ->where('p.status_pemakaian', 'DISETUJUI')
                ->groupBy('dpm.id_inventory')
                ->selectRaw('dpm.id_inventory as id_inventory, SUM(dpm.qty_pemakaian) as total')
                ->pluck('total', 'id_inventory');

            foreach ($keluarPakai as $id => $total) {
                $id = (int) $id;
                if (isset($out[$id])) {
                    $out[$id]['keluar'] += (float) $total;
                }
            }
        }

        return $out;
    }

    private static function dbTableMissing(string $table): bool
    {
        try {
            return ! DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return true;
        }
    }
}
