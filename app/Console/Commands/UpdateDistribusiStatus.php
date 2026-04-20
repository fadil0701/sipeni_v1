<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransaksiDistribusi;

class UpdateDistribusiStatus extends Command
{
    protected $signature = 'distribusi:update-status';
    protected $description = 'Update status distribusi yang sudah di-compile menjadi DIKIRIM';

    public function handle()
    {
        $this->info('Memperbarui status distribusi...');
        
        // Update distribusi yang sudah memiliki detail distribusi (sudah di-compile)
        // tapi masih berstatus DRAFT
        $distribusi = TransaksiDistribusi::where('status_distribusi', 'draft')
            ->whereHas('detailDistribusi')
            ->get();
        
        $count = $distribusi->count();
        
        if ($count > 0) {
            foreach ($distribusi as $d) {
                $d->update(['status_distribusi' => 'dikirim']);
                $this->info("✓ Updated: {$d->no_sbbk}");
            }
            
            $this->info("✓ {$count} distribusi berhasil diupdate menjadi DIKIRIM.");
        } else {
            $this->info('Tidak ada distribusi yang perlu diupdate.');
        }
        
        return 0;
    }
}

