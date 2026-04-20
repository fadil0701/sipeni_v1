<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Services\InventoryQrCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateInventoryQrCodes extends Command
{
    protected $signature = 'inventory:regenerate-qr-codes
                            {--dry-run : Hanya tampilkan yang akan diperbaiki tanpa menulis file/DB}
                            {--force : Generate ulang semua QR (termasuk yang berkasnya sudah ada)}';

    protected $description = 'Sinkronkan kolom qr_code dengan berkas di storage, atau generate ulang SVG jika berkas hilang';

    public function handle(InventoryQrCodeService $qrCodeService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $disk = Storage::disk('public');

        $fixedDb = 0;
        $regenerated = 0;
        $skipped = 0;

        InventoryItem::query()
            ->whereNotNull('kode_register')
            ->orderBy('id_item')
            ->chunkById(100, function ($items) use ($disk, $qrCodeService, $dryRun, $force, &$fixedDb, &$regenerated, &$skipped) {
                foreach ($items as $item) {
                    $rel = $item->qr_code
                        ? str_replace('\\', '/', ltrim($item->qr_code, '/'))
                        : '';

                    if (! $force && $rel !== '' && $disk->exists($rel)) {
                        $skipped++;

                        continue;
                    }

                    if ($rel !== '') {
                        $base = preg_replace('/\.(png|jpe?g|svg)$/i', '', $rel);
                    } else {
                        $base = 'qrcodes/inventory_item/'.str_replace('\\', '/', $item->kode_register);
                    }

                    if (! $force) {
                        foreach (['svg', 'png', 'jpeg', 'jpg'] as $ext) {
                            $try = $base.'.'.$ext;
                            if ($disk->exists($try)) {
                                if (! $dryRun) {
                                    $item->update(['qr_code' => $try]);
                                }
                                $this->line("[DB] id_item={$item->id_item} qr_code diselaraskan ke {$try}".($dryRun ? ' (dry-run)' : ''));
                                $fixedDb++;

                                continue 2;
                            }
                        }
                    }

                    if ($dryRun) {
                        $would = 'qrcodes/inventory_item/'.str_replace('\\', '/', $item->kode_register).'.'.strtolower((string) env('QR_CODE_FORMAT', 'png'));
                        $this->line("[DRY-RUN] akan generate QR & set qr_code={$would} untuk id_item={$item->id_item}");
                        $regenerated++;

                        continue;
                    }

                    $path = $qrCodeService->generateForKodeRegister($item->kode_register);
                    if ($path === null) {
                        $this->warn("[GAGAL] id_item={$item->id_item} kode_register={$item->kode_register}");

                        continue;
                    }

                    $item->update(['qr_code' => $path]);
                    $this->info("[OK] id_item={$item->id_item} QR dibuat ulang: {$path}");
                    $regenerated++;
                }
            });

        $this->newLine();
        $this->info("Selesai. Lewati (berkas sudah ada): {$skipped}, DB diselaraskan: {$fixedDb}, di-generate ulang: {$regenerated}".($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
