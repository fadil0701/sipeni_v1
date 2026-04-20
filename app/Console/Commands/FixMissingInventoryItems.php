<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataInventory;
use App\Models\InventoryItem;
use App\Models\MasterUnitKerja;
use App\Services\InventoryQrCodeService;
use Illuminate\Support\Facades\DB;

class FixMissingInventoryItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-missing-inventory-items {--dry-run : Show what would be created without actually creating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing inventory items for ASET type DataInventory records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be created');
        }

        // Find all ASET inventory that don't have inventory items or have less items than qty_input
        $inventories = DataInventory::where('jenis_inventory', 'ASET')
            ->where('qty_input', '>', 0)
            ->with('inventoryItems')
            ->get();

        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($inventories as $inventory) {
            $currentItemCount = $inventory->inventoryItems->count();
            $expectedCount = (int)$inventory->qty_input;

            if ($currentItemCount >= $expectedCount) {
                $skipped++;
                continue;
            }

            $missingCount = $expectedCount - $currentItemCount;

            $this->info("📦 Inventory ID: {$inventory->id_inventory}");
            $this->info("   Barang: {$inventory->dataBarang->nama_barang ?? 'N/A'}");
            $this->info("   Qty Input: {$expectedCount}");
            $this->info("   Current Items: {$currentItemCount}");
            $this->info("   Missing Items: {$missingCount}");

            if ($dryRun) {
                $this->warn("   [DRY RUN] Would create {$missingCount} inventory items");
                $fixed++;
                continue;
            }

            try {
                DB::beginTransaction();

                // Create missing inventory items
                $created = $this->createMissingInventoryItems($inventory, $missingCount, $currentItemCount);

                DB::commit();

                $this->info("   ✅ Created {$created} inventory items");
                $fixed++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("   ❌ Error: " . $e->getMessage());
                $errors++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   Fixed: {$fixed}");
        $this->info("   Skipped: {$skipped}");
        $this->info("   Errors: {$errors}");

        return 0;
    }

    private function createMissingInventoryItems(DataInventory $inventory, int $missingCount, int $currentItemCount)
    {
        $dataBarang = $inventory->dataBarang;
        $gudang = $inventory->gudang;
        $unitKerja = $gudang->unitKerja ?? MasterUnitKerja::first();

        // Generate kode register base - ambil dari hierarki barang
        $kodeBarang = 'UNK';
        try {
            if ($dataBarang && $dataBarang->subjenisBarang) {
                $subjenis = $dataBarang->subjenisBarang;
                if ($subjenis->jenisBarang && $subjenis->jenisBarang->kategoriBarang) {
                    $kategori = $subjenis->jenisBarang->kategoriBarang;
                    if ($kategori->kodeBarang) {
                        $kodeBarang = $kategori->kodeBarang->kode_barang;
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback jika relasi tidak lengkap
            $kodeBarang = 'UNK';
        }

        $tahun = $inventory->tahun_anggaran;
        $unitCode = $unitKerja ? $unitKerja->kode_unit_kerja : 'UNIT';

        // Get max urut untuk tahun dan kode barang ini
        $existingRegisters = InventoryItem::where('kode_register', 'like', "{$unitCode}/{$kodeBarang}/{$tahun}/%")
            ->get()
            ->map(function ($item) {
                $parts = explode('/', $item->kode_register);
                return isset($parts[3]) ? (int)$parts[3] : 0;
            });

        $maxUrut = $existingRegisters->max() ?? 0;
        $created = 0;

        // Create missing items
        for ($i = 1; $i <= $missingCount; $i++) {
            $urut = $maxUrut + $currentItemCount + $i;
            $kodeRegister = sprintf('%s/%s/%s/%04d', $unitCode, $kodeBarang, $tahun, $urut);

            $qrCodePath = app(InventoryQrCodeService::class)->generateForKodeRegister($kodeRegister);

            InventoryItem::create([
                'id_inventory' => $inventory->id_inventory,
                'kode_register' => $kodeRegister,
                'no_seri' => $inventory->no_seri ?? null,
                'kondisi_item' => 'BAIK',
                'status_item' => 'AKTIF',
                'id_gudang' => $inventory->id_gudang,
                'id_ruangan' => null,
                'qr_code' => $qrCodePath,
            ]);

            $created++;
        }

        return $created;
    }
}

