<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApprovalLog;
use App\Models\Role;
use App\Models\ApprovalFlowDefinition;

class CleanupDuplicateApprovalLogs extends Command
{
    protected $signature = 'approval:cleanup-duplicates';
    protected $description = 'Menghapus approval log duplikat untuk admin_gudang_pusat yang tidak diperlukan';

    public function handle()
    {
        $this->info('Membersihkan approval log duplikat...');
        
        $adminGudangRole = Role::where('name', 'admin_gudang_pusat')->first();

        if (!$adminGudangRole) {
            $this->info('Role admin_gudang_pusat tidak ditemukan.');
            return 0;
        }

        $adminGudangFlow = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 5)
            ->where('role_id', $adminGudangRole->id)
            ->first();

        if (!$adminGudangFlow) {
            $this->info('Flow definition untuk admin_gudang_pusat tidak ditemukan.');
            return 0;
        }

        $count = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_approval_flow', $adminGudangFlow->id)
            ->where('status', 'MENUNGGU')
            ->count();

        if ($count > 0) {
            ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('id_approval_flow', $adminGudangFlow->id)
                ->where('status', 'MENUNGGU')
                ->delete();

            $this->info("✓ {$count} approval log duplikat untuk admin_gudang_pusat telah dihapus.");
        } else {
            $this->info('Tidak ada approval log duplikat yang perlu dihapus.');
        }
        
        return 0;
    }
}

