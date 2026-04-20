<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PermintaanBarang;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use App\Models\MasterGudang;

class VerifyDataAfterMigration extends Command
{
    protected $signature = 'verify:data-after-migration';
    protected $description = 'Verify and fix data integrity after migration from SQLite to MySQL';

    public function handle()
    {
        $this->info('Verifying data integrity after migration...');
        $this->newLine();
        
        // 1. Check permintaan barang
        $this->info('1. Checking Permintaan Barang...');
        $totalPermintaan = PermintaanBarang::count();
        $this->line("   Total permintaan: {$totalPermintaan}");
        
        // Check with relationships
        $permintaans = PermintaanBarang::with(['unitKerja', 'pemohon'])->get();
        $missingUnitKerja = 0;
        $missingPemohon = 0;
        
        foreach ($permintaans as $permintaan) {
            if (!$permintaan->unitKerja) {
                $missingUnitKerja++;
                $this->warn("   - Permintaan {$permintaan->no_permintaan} (ID: {$permintaan->id_permintaan}) missing unit kerja (id_unit_kerja: {$permintaan->id_unit_kerja})");
            }
            if (!$permintaan->pemohon) {
                $missingPemohon++;
                $this->warn("   - Permintaan {$permintaan->no_permintaan} (ID: {$permintaan->id_permintaan}) missing pemohon (id_pemohon: {$permintaan->id_pemohon})");
            }
        }
        
        if ($missingUnitKerja == 0 && $missingPemohon == 0) {
            $this->info("   ✓ All permintaan have valid relationships");
        } else {
            $this->warn("   ✗ Found {$missingUnitKerja} permintaan with missing unit kerja");
            $this->warn("   ✗ Found {$missingPemohon} permintaan with missing pemohon");
        }
        
        $this->newLine();
        
        // 2. Check unit kerja and gudang
        $this->info('2. Checking Unit Kerja and Gudang...');
        $unitKerjas = MasterUnitKerja::with('gudang')->get();
        $this->line("   Total unit kerja: {$unitKerjas->count()}");
        
        $unitKerjaWithGudang = 0;
        foreach ($unitKerjas as $unitKerja) {
            if ($unitKerja->gudang->count() > 0) {
                $unitKerjaWithGudang++;
            }
        }
        
        $this->line("   Unit kerja with gudang: {$unitKerjaWithGudang}");
        
        // Check gudang unit
        $gudangUnits = MasterGudang::where('jenis_gudang', 'UNIT')->get();
        $this->line("   Total gudang unit: {$gudangUnits->count()}");
        
        foreach ($gudangUnits as $gudang) {
            $unitKerja = $gudang->unitKerja;
            if (!$unitKerja) {
                $this->warn("   - Gudang {$gudang->nama_gudang} (ID: {$gudang->id_gudang}) missing unit kerja (id_unit_kerja: {$gudang->id_unit_kerja})");
            }
        }
        
        $this->newLine();
        
        // 3. Check master pegawai
        $this->info('3. Checking Master Pegawai...');
        $totalPegawai = MasterPegawai::count();
        $this->line("   Total pegawai: {$totalPegawai}");
        
        $pegawaiWithUnitKerja = MasterPegawai::whereNotNull('id_unit_kerja')->count();
        $this->line("   Pegawai with unit kerja: {$pegawaiWithUnitKerja}");
        
        $this->newLine();
        
        // 4. Test query performance
        $this->info('4. Testing query performance...');
        $start = microtime(true);
        $testPermintaans = PermintaanBarang::with(['unitKerja.gudang', 'pemohon.jabatan'])->limit(10)->get();
        $end = microtime(true);
        $duration = round(($end - $start) * 1000, 2);
        
        $this->line("   Query time: {$duration}ms");
        
        foreach ($testPermintaans as $permintaan) {
            $unitKerjaName = $permintaan->unitKerja ? $permintaan->unitKerja->nama_unit_kerja : 'NULL';
            $pemohonName = $permintaan->pemohon ? $permintaan->pemohon->nama_pegawai : 'NULL';
            $gudangCount = $permintaan->unitKerja && $permintaan->unitKerja->gudang ? $permintaan->unitKerja->gudang->count() : 0;
            
            $this->line("   - {$permintaan->no_permintaan}: Unit Kerja={$unitKerjaName}, Pemohon={$pemohonName}, Gudang={$gudangCount}");
        }
        
        $this->newLine();
        $this->info('Verification complete!');
    }
}

