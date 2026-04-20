<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PermintaanBarang;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;

class FixPermintaanRelationships extends Command
{
    protected $signature = 'fix:permintaan-relationships';
    protected $description = 'Fix permintaan barang relationships after migration from SQLite to MySQL';

    public function handle()
    {
        $this->info('Checking permintaan barang relationships...');
        
        // Check unit kerja relationships
        $this->info('Checking unit kerja relationships...');
        $invalidUnitKerja = DB::table('permintaan_barang')
            ->leftJoin('master_unit_kerja', 'permintaan_barang.id_unit_kerja', '=', 'master_unit_kerja.id_unit_kerja')
            ->whereNull('master_unit_kerja.id_unit_kerja')
            ->whereNotNull('permintaan_barang.id_unit_kerja')
            ->select('permintaan_barang.id_permintaan', 'permintaan_barang.id_unit_kerja', 'permintaan_barang.no_permintaan')
            ->get();
        
        if ($invalidUnitKerja->count() > 0) {
            $this->warn("Found {$invalidUnitKerja->count()} permintaan with invalid unit kerja:");
            foreach ($invalidUnitKerja as $item) {
                $this->line("  - ID: {$item->id_permintaan}, No: {$item->no_permintaan}, Unit Kerja ID: {$item->id_unit_kerja}");
            }
            
            // Get first valid unit kerja as fallback
            $defaultUnitKerja = MasterUnitKerja::first();
            if ($defaultUnitKerja) {
                if ($this->confirm("Fix by setting invalid unit kerja to '{$defaultUnitKerja->nama_unit_kerja}' (ID: {$defaultUnitKerja->id_unit_kerja})?")) {
                    DB::table('permintaan_barang')
                        ->whereIn('id_permintaan', $invalidUnitKerja->pluck('id_permintaan'))
                        ->update(['id_unit_kerja' => $defaultUnitKerja->id_unit_kerja]);
                    $this->info("Fixed {$invalidUnitKerja->count()} permintaan unit kerja relationships.");
                }
            }
        } else {
            $this->info('All unit kerja relationships are valid.');
        }
        
        // Check pemohon relationships
        $this->info('Checking pemohon relationships...');
        $invalidPemohon = DB::table('permintaan_barang')
            ->leftJoin('master_pegawai', 'permintaan_barang.id_pemohon', '=', 'master_pegawai.id')
            ->whereNull('master_pegawai.id')
            ->whereNotNull('permintaan_barang.id_pemohon')
            ->select('permintaan_barang.id_permintaan', 'permintaan_barang.id_pemohon', 'permintaan_barang.no_permintaan')
            ->get();
        
        if ($invalidPemohon->count() > 0) {
            $this->warn("Found {$invalidPemohon->count()} permintaan with invalid pemohon:");
            foreach ($invalidPemohon as $item) {
                $this->line("  - ID: {$item->id_permintaan}, No: {$item->no_permintaan}, Pemohon ID: {$item->id_pemohon}");
            }
            
            // Get first valid pegawai as fallback
            $defaultPegawai = MasterPegawai::first();
            if ($defaultPegawai) {
                if ($this->confirm("Fix by setting invalid pemohon to '{$defaultPegawai->nama_pegawai}' (ID: {$defaultPegawai->id})?")) {
                    DB::table('permintaan_barang')
                        ->whereIn('id_permintaan', $invalidPemohon->pluck('id_permintaan'))
                        ->update(['id_pemohon' => $defaultPegawai->id]);
                    $this->info("Fixed {$invalidPemohon->count()} permintaan pemohon relationships.");
                }
            }
        } else {
            $this->info('All pemohon relationships are valid.');
        }
        
        // Test relationships
        $this->info('Testing relationships...');
        $testPermintaan = PermintaanBarang::with(['unitKerja', 'pemohon'])->first();
        if ($testPermintaan) {
            $this->info("Test permintaan: {$testPermintaan->no_permintaan}");
            $this->info("  Unit Kerja: " . ($testPermintaan->unitKerja ? $testPermintaan->unitKerja->nama_unit_kerja : 'NULL'));
            $this->info("  Pemohon: " . ($testPermintaan->pemohon ? $testPermintaan->pemohon->nama_pegawai : 'NULL'));
        }
        
        $this->info('Done!');
    }
}

