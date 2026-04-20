<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\Role;
use App\Models\PermintaanBarang;

class CheckApprovalFlow extends Command
{
    protected $signature = 'approval:check';
    protected $description = 'Check approval flow configuration and logs';

    public function handle()
    {
        $this->info('=== Checking Approval Flow Configuration ===');
        
        // Check Roles
        $this->info("\n1. Checking Roles:");
        $roles = Role::all(['id', 'name']);
        foreach ($roles as $role) {
            $this->line("   - ID: {$role->id}, Name: {$role->name}");
        }
        
        // Check ApprovalFlowDefinition
        $this->info("\n2. Checking ApprovalFlowDefinition:");
        $flows = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
            ->orderBy('step_order')
            ->get();
        
        if ($flows->isEmpty()) {
            $this->error('   No ApprovalFlowDefinition found! Run: php artisan db:seed --class=ApprovalFlowDefinitionSeeder');
        } else {
            foreach ($flows as $flow) {
                $roleName = $flow->role ? $flow->role->name : 'NULL';
                $this->line("   - Step {$flow->step_order}: {$flow->nama_step} (Role: {$roleName}, Role ID: {$flow->role_id})");
            }
        }
        
        // Check ApprovalLog
        $this->info("\n3. Checking ApprovalLog:");
        $logs = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
            ->with(['approvalFlow', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        if ($logs->isEmpty()) {
            $this->warn('   No ApprovalLog found!');
        } else {
            $this->info("   Found {$logs->count()} approval logs:");
            foreach ($logs as $log) {
                $stepName = $log->approvalFlow ? $log->approvalFlow->nama_step : 'N/A';
                $userName = $log->user ? $log->user->name : 'N/A';
                $this->line("   - ID: {$log->id}, Referensi: {$log->id_referensi}, Step: {$stepName}, Status: {$log->status}, User: {$userName}");
            }
        }
        
        // Check Permintaan Barang yang sudah diajukan
        $this->info("\n4. Checking Permintaan Barang (DIAJUKAN):");
        $permintaans = PermintaanBarang::where('status', 'diajukan')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        if ($permintaans->isEmpty()) {
            $this->warn('   No permintaan with status DIAJUKAN found!');
        } else {
            $this->info("   Found {$permintaans->count()} permintaan:");
            foreach ($permintaans as $permintaan) {
                $logCount = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
                    ->where('id_referensi', $permintaan->id_permintaan)
                    ->count();
                $this->line("   - ID: {$permintaan->id_permintaan}, No: {$permintaan->no_permintaan}, ApprovalLogs: {$logCount}");
            }
        }
        
        $this->info("\n=== Check Complete ===");
        return 0;
    }
}





