<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Helpers\PermissionHelper;

class CheckUserRoles extends Command
{
    protected $signature = 'user:check-roles {email?}';
    protected $description = 'Check user roles and permissions';

    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("User dengan email {$email} tidak ditemukan!");
                return 1;
            }
            $users = collect([$user]);
        } else {
            $users = User::all();
        }
        
        foreach ($users as $user) {
            $this->info("\n=== User: {$user->name} ({$user->email}) ===");
            
            // Load roles
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }
            
            $roles = $user->roles->pluck('name')->toArray();
            $this->info("Roles: " . implode(', ', $roles));
            
            // Check approval permissions
            $canAccessApprovalIndex = PermissionHelper::canAccess($user, 'transaction.approval.index');
            $canAccessApprovalShow = PermissionHelper::canAccess($user, 'transaction.approval.show');
            $canAccessApprovalMengetahui = PermissionHelper::canAccess($user, 'transaction.approval.mengetahui');
            
            $this->info("Permission 'transaction.approval.index': " . ($canAccessApprovalIndex ? 'YES' : 'NO'));
            $this->info("Permission 'transaction.approval.show': " . ($canAccessApprovalShow ? 'YES' : 'NO'));
            $this->info("Permission 'transaction.approval.mengetahui': " . ($canAccessApprovalMengetahui ? 'YES' : 'NO'));
            
            // Check if has kepala_unit role
            $hasKepalaUnit = $user->hasRole('kepala_unit');
            $this->info("Has role 'kepala_unit': " . ($hasKepalaUnit ? 'YES' : 'NO'));
        }
        
        return 0;
    }
}





