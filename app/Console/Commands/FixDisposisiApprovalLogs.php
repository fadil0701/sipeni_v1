<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApprovalLog;
use App\Models\ApprovalFlowDefinition;
use App\Models\Role;

class FixDisposisiApprovalLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:disposisi-approval-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix approval logs for disposisi that have wrong status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memperbaiki approval log untuk disposisi...');
        
        // Ambil semua approval log untuk step 4 (disposisi) dengan status DIDISPOSISIKAN
        $disposisiFlowIds = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->pluck('id')
            ->toArray();
        
        $this->info('Found ' . count($disposisiFlowIds) . ' disposisi flow definitions');
        
        // Update approval log dengan status DIDISPOSISIKAN menjadi MENUNGGU
        $updated = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
            ->whereIn('id_approval_flow', $disposisiFlowIds)
            ->where('status', 'DIDISPOSISIKAN')
            ->update([
                'status' => 'MENUNGGU',
                'user_id' => null,
                'approved_at' => null
            ]);
        
        $this->info("Updated {$updated} approval logs from DIDISPOSISIKAN to MENUNGGU");
        
        // Cek juga approval log yang mungkin tidak memiliki id_approval_flow yang benar
        // Tapi memiliki role_id untuk admin gudang kategori
        $kategoriRoleIds = Role::whereIn('name', ['admin_gudang_aset', 'admin_gudang_persediaan', 'admin_gudang_farmasi'])
            ->pluck('id')
            ->toArray();
        
        $logsWithoutFlow = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
            ->whereIn('role_id', $kategoriRoleIds)
            ->where('status', 'DIDISPOSISIKAN')
            ->whereNotIn('id_approval_flow', $disposisiFlowIds)
            ->get();
        
        foreach ($logsWithoutFlow as $log) {
            // Cari atau buat flow definition untuk role ini
            $role = Role::find($log->role_id);
            if ($role) {
                $kategori = match($role->name) {
                    'admin_gudang_aset' => 'ASET',
                    'admin_gudang_persediaan' => 'PERSEDIAAN',
                    'admin_gudang_farmasi' => 'FARMASI',
                    default => null
                };
                
                if ($kategori) {
                    $flow = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
                        ->where('step_order', 4)
                        ->where('role_id', $role->id)
                        ->first();
                    
                    if ($flow) {
                        $log->update([
                            'id_approval_flow' => $flow->id,
                            'status' => 'MENUNGGU',
                            'user_id' => null,
                            'approved_at' => null
                        ]);
                        $this->info("Fixed approval log ID {$log->id} - updated flow and status");
                    }
                }
            }
        }
        
        $this->info('Selesai memperbaiki approval log untuk disposisi.');
        
        return Command::SUCCESS;
    }
}
