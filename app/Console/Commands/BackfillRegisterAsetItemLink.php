<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\RegisterAset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BackfillRegisterAsetItemLink extends Command
{
    protected $signature = 'register-aset:backfill-item-link {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Backfill register_aset.id_item dan sinkronkan nomor_register dari inventory_item.kode_register';

    public function handle(): int
    {
        if (!Schema::hasColumn('register_aset', 'id_item')) {
            $this->error('Kolom register_aset.id_item belum ada. Jalankan migration terlebih dahulu.');
            return self::FAILURE;
        }

        $isDryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $skipped = 0;
        $conflicts = 0;

        $registers = RegisterAset::query()
            ->with('inventoryItem')
            ->orderBy('id_register_aset')
            ->get();

        foreach ($registers as $register) {
            $targetItem = null;

            if ($register->id_item) {
                $targetItem = $register->inventoryItem;
            }

            if (!$targetItem) {
                // Fallback data lama: cari item berdasarkan id_inventory dengan kode register sama.
                if (!empty($register->nomor_register)) {
                    $targetItem = InventoryItem::query()
                        ->where('id_inventory', $register->id_inventory)
                        ->where('kode_register', $register->nomor_register)
                        ->first();
                }

                // Jika belum ketemu, ambil item pertama per inventory (legacy fallback).
                if (!$targetItem) {
                    $targetItem = InventoryItem::query()
                        ->where('id_inventory', $register->id_inventory)
                        ->orderBy('id_item')
                        ->first();
                }
            }

            if (!$targetItem) {
                $this->warn("Skip #{$register->id_register_aset}: tidak ditemukan InventoryItem untuk id_inventory {$register->id_inventory}");
                $skipped++;
                continue;
            }

            $newIdItem = $targetItem->id_item;
            $newNomorRegister = $targetItem->kode_register;

            // Hindari 2 register menunjuk item yang sama.
            $duplicateForItem = RegisterAset::query()
                ->where('id_item', $newIdItem)
                ->where('id_register_aset', '!=', $register->id_register_aset)
                ->exists();

            if ($duplicateForItem) {
                $this->warn("Conflict #{$register->id_register_aset}: id_item {$newIdItem} sudah dipakai register lain");
                $conflicts++;
                continue;
            }

            $needsUpdate = ((int) $register->id_item !== (int) $newIdItem)
                || ((string) $register->nomor_register !== (string) $newNomorRegister);

            if (!$needsUpdate) {
                $skipped++;
                continue;
            }

            $this->line("Update #{$register->id_register_aset}: id_item {$register->id_item} -> {$newIdItem}, nomor_register {$register->nomor_register} -> {$newNomorRegister}");

            if (!$isDryRun) {
                $register->id_item = $newIdItem;
                $register->nomor_register = $newNomorRegister;
                $register->save();
            }

            $updated++;
        }

        $this->info("Selesai. Updated: {$updated}, Skipped: {$skipped}, Conflicts: {$conflicts}, Dry-run: ".($isDryRun ? 'yes' : 'no'));
        return self::SUCCESS;
    }
}

