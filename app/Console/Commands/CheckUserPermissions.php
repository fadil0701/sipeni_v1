<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Helpers\PermissionHelper;

class CheckUserPermissions extends Command
{
    protected $signature = 'user:check-permissions {email}';
    protected $description = 'Check permissions for a specific user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return 1;
        }

        $this->info("=== User: {$user->name} ({$user->email}) ===");
        
        // Load roles
        $user->load('roles');
        $this->info("\nRoles:");
        foreach ($user->roles as $role) {
            $this->line("  - {$role->name} ({$role->display_name})");
        }

        // Load permissions dari database
        $roleIds = $user->roles->pluck('id')->toArray();
        $permissions = \DB::table('permission_role')
            ->whereIn('role_id', $roleIds)
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
            ->select('permissions.name', 'permissions.display_name', 'permissions.module')
            ->orderBy('permissions.module')
            ->orderBy('permissions.name')
            ->get();

        $this->info("\nPermissions dari Database:");
        if ($permissions->isEmpty()) {
            $this->warn("  Tidak ada permission yang di-assign ke role user ini.");
        } else {
            $grouped = $permissions->groupBy('module');
            foreach ($grouped as $module => $modulePermissions) {
                $this->line("\n  Module: {$module}");
                foreach ($modulePermissions as $perm) {
                    $this->line("    - {$perm->name} ({$perm->display_name})");
                }
            }
        }

        // Test beberapa permission
        $this->info("\n=== Test Permission Check ===");
        $testPermissions = [
            'transaction.*',
            'transaction.approval.index',
            'transaction.permintaan-barang.index',
            'master-manajemen.*',
            'inventory.*',
        ];

        foreach ($testPermissions as $perm) {
            $hasAccess = PermissionHelper::canAccess($user, $perm);
            $status = $hasAccess ? '✓' : '✗';
            $this->line("  {$status} {$perm}");
        }

        // Test accessible menus
        $this->info("\n=== Accessible Menus ===");
        $accessibleMenus = PermissionHelper::getAccessibleMenus($user);
        foreach ($accessibleMenus as $key => $menu) {
            $this->line("  ✓ {$key}");
            if (isset($menu['submenus'])) {
                foreach ($menu['submenus'] as $subKey => $submenu) {
                    $this->line("    - {$subKey}");
                }
            }
        }

        return 0;
    }
}


