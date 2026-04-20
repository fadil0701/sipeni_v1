<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\DataInventory;
use App\Models\RegisterAset;
use App\Models\InventoryItem;
use App\Models\MasterUnitKerja;
use App\Models\MasterGudang;

class SyncRegisterAset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register-aset:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync RegisterAset dari DataInventory yang sudah ada dengan jenis ASET';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sync RegisterAset...');

        // Ambil semua DataInventory dengan jenis ASET yang belum memiliki RegisterAset
        $inventories = DataInventory::where('jenis_inventory', 'ASET')
            ->whereDoesntHave('registerAset')
            ->with(['gudang', 'inventoryItems'])
            ->get();

        $this->info("Ditemukan {$inventories->count()} DataInventory ASET yang belum memiliki RegisterAset");

        $created = 0;
        $skipped = 0;

        foreach ($inventories as $inventory) {
            $gudang = $inventory->gudang;
            $unitKerja = $gudang->unitKerja ?? MasterUnitKerja::first();
            
            if (!$unitKerja) {
                $this->warn("Unit kerja tidak ditemukan untuk inventory ID: {$inventory->id_inventory}");
                $skipped++;
                continue;
            }

            // Generate nomor register dari kode register pertama di InventoryItem
            $firstInventoryItem = InventoryItem::where('id_inventory', $inventory->id_inventory)
                ->orderBy('id_item')
                ->first();
            
            if (!$firstInventoryItem) {
                $this->warn("InventoryItem tidak ditemukan untuk inventory ID: {$inventory->id_inventory}");
                $skipped++;
                continue;
            }

            $nomorRegister = $firstInventoryItem->kode_register;

            // Cek apakah nomor register sudah digunakan
            $existingRegister = RegisterAset::where('nomor_register', $nomorRegister)->first();
            if ($existingRegister) {
                $nomorRegister = $nomorRegister . '-' . $inventory->id_inventory;
            }

            try {
                // Cek apakah kolom id_item sudah ada
                $hasIdItemColumn = \Schema::hasColumn('register_aset', 'id_item');
                
                // Cek apakah InventoryItem ini sudah ter-register
                if ($hasIdItemColumn) {
                    $existingRegister = RegisterAset::where('id_item', $firstInventoryItem->id_item)->first();
                    if ($existingRegister) {
                        $this->warn("InventoryItem ID {$firstInventoryItem->id_item} sudah ter-register, skip");
                        $skipped++;
                        continue;
                    }
                } else {
                    // Fallback: cek berdasarkan id_inventory
                    $existingRegister = RegisterAset::where('id_inventory', $inventory->id_inventory)->first();
                    if ($existingRegister) {
                        $this->warn("Inventory ID {$inventory->id_inventory} sudah ter-register, skip");
                        $skipped++;
                        continue;
                    }
                }
                
                $registerData = [
                    'id_inventory' => $inventory->id_inventory,
                    'id_unit_kerja' => $unitKerja->id_unit_kerja,
                    'nomor_register' => $nomorRegister,
                    'kondisi_aset' => 'BAIK',
                    'tanggal_perolehan' => $inventory->created_at->toDateString(),
                    'status_aset' => 'AKTIF',
                ];
                
                // Tambahkan id_item jika kolom sudah ada
                if ($hasIdItemColumn) {
                    $registerData['id_item'] = $firstInventoryItem->id_item;
                }
                
                RegisterAset::create($registerData);

                $created++;
                $itemInfo = $hasIdItemColumn ? ", item ID: {$firstInventoryItem->id_item}" : "";
                $this->info("RegisterAset berhasil dibuat untuk inventory ID: {$inventory->id_inventory}{$itemInfo}");
            } catch (\Exception $e) {
                $this->error("Error membuat RegisterAset untuk inventory ID: {$inventory->id_inventory} - {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->info("\nSync selesai!");
        $this->info("Created: {$created}");
        $this->info("Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
