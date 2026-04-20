<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;
use App\Helpers\PermissionHelper;

class AssignDisposisiPermission extends Command
{
    protected $signature = 'permission:assign-disposisi';
    protected $description = 'Assign disposisi permission to admin_gudang and admin roles';

    public function handle()
    {
        $permission = Permission::where('name', 'transaction.approval.disposisi')->first();
        
        if (!$permission) {
            $this->error('Permission transaction.approval.disposisi not found!');
            return Command::FAILURE;
        }

        $roles = Role::whereIn('name', ['admin_gudang', 'admin'])->get();
        
        if ($roles->isEmpty()) {
            $this->error('Roles not found!');
            return Command::FAILURE;
        }

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching([$permission->id]);
            $this->info("✓ Permission assigned to {$role->name} role");
        }

        PermissionHelper::bumpAccessibleMenusCacheGeneration();

        return Command::SUCCESS;
    }
}

