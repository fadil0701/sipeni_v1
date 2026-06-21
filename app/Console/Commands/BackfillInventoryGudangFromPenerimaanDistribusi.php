<?php

namespace App\Console\Commands;

use App\Models\DataInventory;
use App\Models\PenerimaanBarang;
use App\Services\DataStockSyncService;
use App\Services\PenerimaanDistribusiInventoryTransferService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Memperbaiki data historis: penerimaan distribusi sudah DITERIMA tetapi
 * data_inventory PERSEDIAAN/FARMASI masih di gudang asal (belum pindah ke gudang unit).
 */
class BackfillInventoryGudangFromPenerimaanDistribusi extends Command
{
    protected $signature = 'inventory:backfill-penerimaan-unit-gudang
                            {--dry-run : Hanya menampilkan baris yang perlu perbaikan, tanpa mengubah database}';

    protected $description = 'Pindahkan data_inventory persediaan/farmasi ke gudang tujuan untuk penerimaan distribusi yang sudah DITERIMA (perbaikan satu kali / data lama)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $penerimaans = PenerimaanBarang::query()
            ->where('status_penerimaan', 'DITERIMA')
            ->whereNotNull('id_distribusi')
            ->with(['detailPenerimaan', 'distribusi.gudangTujuan'])
            ->orderBy('id_penerimaan')
            ->get();

        $toFix = [];

        foreach ($penerimaans as $p) {
            $dist = $p->distribusi;
            if (! $dist || ! $dist->id_gudang_tujuan) {
                continue;
            }
            $idTujuan = (int) $dist->id_gudang_tujuan;

            foreach ($p->detailPenerimaan as $detail) {
                $inv = DataInventory::query()->find($detail->id_inventory);
                if (! $inv || ! in_array($inv->jenis_inventory, ['PERSEDIAAN', 'FARMASI'], true)) {
                    continue;
                }
                if ((int) $inv->id_gudang === $idTujuan) {
                    continue;
                }
                $toFix[] = [
                    'id_penerimaan' => $p->id_penerimaan,
                    'no_penerimaan' => $p->no_penerimaan,
                    'id_distribusi' => $p->id_distribusi,
                    'id_inventory' => $inv->id_inventory,
                    'jenis' => $inv->jenis_inventory,
                    'id_gudang_saat_ini' => $inv->id_gudang,
                    'id_gudang_tujuan' => $idTujuan,
                    'qty_diterima' => $detail->qty_diterima,
                    'qty_inventory' => $inv->qty_input,
                ];
            }
        }

        if ($toFix === []) {
            $this->info('Tidak ada baris persediaan/farmasi yang masih salah gudang untuk penerimaan DITERIMA.');

            return self::SUCCESS;
        }

        $this->warn('Ditemukan '.count($toFix).' baris inventory yang perlu dipindahkan ke gudang tujuan.');
        foreach ($toFix as $row) {
            $this->line(sprintf(
                '  penerimaan #%s (%s) inventory #%s %s: gudang %s → %s (qty terima %s, qty inv %s)',
                $row['id_penerimaan'],
                $row['no_penerimaan'],
                $row['id_inventory'],
                $row['jenis'],
                $row['id_gudang_saat_ini'],
                $row['id_gudang_tujuan'],
                $row['qty_diterima'],
                $row['qty_inventory']
            ));
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry-run: tidak ada perubahan. Jalankan tanpa --dry-run untuk menerapkan.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Terapkan pemindahan inventory dan sinkronkan data_stock?', true)) {
            $this->warn('Dibatalkan.');

            return self::FAILURE;
        }

        $ok = 0;
        $fail = 0;

        foreach ($penerimaans as $p) {
            if (! $p->id_distribusi || ! $p->distribusi) {
                continue;
            }

            $needs = false;
            foreach ($p->detailPenerimaan as $detail) {
                $inv = DataInventory::query()->find($detail->id_inventory);
                if (! $inv || ! in_array($inv->jenis_inventory, ['PERSEDIAAN', 'FARMASI'], true)) {
                    continue;
                }
                if ((int) $inv->id_gudang !== (int) $p->distribusi->id_gudang_tujuan) {
                    $needs = true;
                    break;
                }
            }
            if (! $needs) {
                continue;
            }

            try {
                DB::transaction(function () use ($p): void {
                    $fresh = $p->fresh(['detailPenerimaan', 'distribusi']);
                    if (! $fresh->distribusi) {
                        return;
                    }
                    PenerimaanDistribusiInventoryTransferService::transferPersediaanFarmasiToGudangTujuanAfterDiterima(
                        $fresh,
                        $fresh->distribusi
                    );
                });
                $ok++;
            } catch (Throwable $e) {
                $fail++;
                $this->error("Gagal penerimaan #{$p->id_penerimaan}: {$e->getMessage()}");
            }
        }

        DataStockSyncService::syncFromInventory();

        $this->newLine();
        $this->info("Selesai. Berhasil memproses: {$ok}, gagal: {$fail}. Data stok telah disinkronkan.");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
