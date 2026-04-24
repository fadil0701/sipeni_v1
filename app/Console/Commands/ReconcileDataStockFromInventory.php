<?php

namespace App\Console\Commands;

use App\Models\DataInventory;
use App\Models\DataStock;
use Illuminate\Console\Command;

class ReconcileDataStockFromInventory extends Command
{
    protected $signature = 'inventory:reconcile-stock {--fix : Terapkan hasil rekonsiliasi ke data_stock}';

    protected $description = 'Rekonsiliasi qty_akhir data_stock berdasarkan agregasi data_inventory aktif';

    public function handle(): int
    {
        $expected = DataInventory::query()
            ->selectRaw('id_data_barang, id_gudang, COALESCE(SUM(qty_input), 0) as expected_qty')
            ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI'])
            ->where('status_inventory', '!=', 'HABIS')
            ->groupBy('id_data_barang', 'id_gudang')
            ->get()
            ->keyBy(fn ($row) => $row->id_data_barang.'-'.$row->id_gudang);

        $stocks = DataStock::query()->get();
        $rows = [];
        $mismatchCount = 0;
        $isFix = (bool) $this->option('fix');

        foreach ($stocks as $stock) {
            $key = $stock->id_data_barang.'-'.$stock->id_gudang;
            $expectedQty = isset($expected[$key]) ? (float) $expected[$key]->expected_qty : 0.0;
            $currentQty = (float) $stock->qty_akhir;
            $diff = $expectedQty - $currentQty;

            if (abs($diff) < 0.0001) {
                continue;
            }

            $mismatchCount++;
            $rows[] = [
                $stock->id_stock,
                $stock->id_data_barang,
                $stock->id_gudang,
                number_format($currentQty, 2, ',', '.'),
                number_format($expectedQty, 2, ',', '.'),
                number_format($diff, 2, ',', '.'),
            ];

            if ($isFix) {
                $stock->qty_akhir = $expectedQty;
                $stock->last_updated = now();
                $stock->save();
            }
        }

        $this->table(
            ['ID Stock', 'ID Barang', 'ID Gudang', 'Qty Sistem', 'Qty Rekonsiliasi', 'Selisih'],
            $rows
        );

        $this->info("Total mismatch: {$mismatchCount}");
        $this->info($isFix ? 'Mode fix: perubahan disimpan.' : 'Mode audit: jalankan dengan --fix untuk sinkronisasi.');

        return self::SUCCESS;
    }
}
