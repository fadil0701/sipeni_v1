<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RegisterAset;

class FixNomorRegisterXXXX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register-aset:fix-nomor-xxxx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki nomor register yang masih menggunakan XXXX menjadi angka yang benar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai perbaikan nomor register yang mengandung XXXX...');
        
        // Ambil semua RegisterAset yang nomor_register-nya mengandung XXXX
        $registerAsets = RegisterAset::where('nomor_register', 'like', '%XXXX%')->get();
        
        if ($registerAsets->isEmpty()) {
            $this->info('Tidak ada nomor register yang perlu diperbaiki.');
            return 0;
        }
        
        $this->info("Ditemukan {$registerAsets->count()} nomor register yang perlu diperbaiki.");
        
        $fixed = 0;
        $skipped = 0;
        
        foreach ($registerAsets as $registerAset) {
            try {
                // Generate nomor register baru menggunakan logika yang sama dengan controller
                $newNomorRegister = $this->generateNomorRegister(
                    $registerAset->id_unit_kerja,
                    $registerAset->id_ruangan,
                    $registerAset->tanggal_perolehan
                );
                
                // Cek apakah nomor register baru sudah ada
                $exists = RegisterAset::where('nomor_register', $newNomorRegister)
                    ->where('id_register_aset', '!=', $registerAset->id_register_aset)
                    ->exists();
                
                if ($exists) {
                    // Jika sudah ada, tambahkan suffix
                    $counter = 1;
                    $baseNomor = $newNomorRegister;
                    while (RegisterAset::where('nomor_register', $newNomorRegister)
                        ->where('id_register_aset', '!=', $registerAset->id_register_aset)
                        ->exists()) {
                        $newNomorRegister = $baseNomor . '-' . $counter;
                        $counter++;
                    }
                }
                
                $oldNomor = $registerAset->nomor_register;
                
                // Update nomor register
                $registerAset->update(['nomor_register' => $newNomorRegister]);
                
                $this->line("✓ ID {$registerAset->id_register_aset}: '{$oldNomor}' -> '{$newNomorRegister}'");
                $fixed++;
            } catch (\Exception $e) {
                $this->error("✗ ID {$registerAset->id_register_aset}: Error - {$e->getMessage()}");
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("Selesai! Diperbaiki: {$fixed}, Dilewati: {$skipped}");
        
        return 0;
    }
    
    /**
     * Generate nomor register (copy dari RegisterAsetController)
     */
    protected function generateNomorRegister($idUnitKerja, $idRuangan = null, $tanggalPerolehan = null)
    {
        $tahun = $tanggalPerolehan ? date('Y', strtotime($tanggalPerolehan)) : date('Y');
        
        // Format baru: ID_UNIT_KERJA/ID_RUANGAN/URUT atau ID_UNIT_KERJA/URUT
        if ($idRuangan) {
            $prefix = sprintf('%03d/%03d', $idUnitKerja, $idRuangan);
        } else {
            $prefix = sprintf('%03d', $idUnitKerja);
        }
        
        // Cari nomor urut terakhir untuk kombinasi unit kerja + ruangan + tahun ini
        $lastRegister = RegisterAset::where('id_unit_kerja', $idUnitKerja)
            ->where(function($q) use ($idRuangan) {
                if ($idRuangan) {
                    $q->where('id_ruangan', $idRuangan);
                } else {
                    $q->whereNull('id_ruangan');
                }
            })
            ->whereYear('tanggal_perolehan', $tahun)
            ->where('nomor_register', 'like', $prefix . '/%')
            ->where('nomor_register', 'not like', '%XXXX%') // Exclude yang masih XXXX
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_register, "/", -1) AS UNSIGNED) DESC')
            ->first();
        
        $urut = 1;
        if ($lastRegister) {
            // Extract nomor urut dari nomor register terakhir
            $parts = explode('/', $lastRegister->nomor_register);
            $lastUrut = (int)end($parts);
            $urut = $lastUrut + 1;
        }
        
        return sprintf('%s/%04d', $prefix, $urut);
    }
}
