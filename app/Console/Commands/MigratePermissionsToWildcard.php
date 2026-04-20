<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class MigratePermissionsToWildcard extends Command
{
    protected $signature = 'permission:migrate-to-wildcard';
    protected $description = 'Migrasikan permission individual ke wildcard untuk role yang sudah ada';

    public function handle()
    {
        $this->info('Memigrasikan permission individual ke wildcard...');
        $this->newLine();

        // Ambil semua role
        $roles = Role::all();
        $migrated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($roles as $role) {
                $this->line("Processing role: {$role->name}...");
                
                // Ambil semua permission role ini
                $rolePermissions = $role->permissions;
                
                // Group permission berdasarkan resource
                $permissionGroups = [];
                
                foreach ($rolePermissions as $permission) {
                    $parts = explode('.', $permission->name);
                    
                    // Skip jika sudah wildcard
                    if (str_ends_with($permission->name, '.*')) {
                        continue;
                    }
                    
                    // Skip system routes
                    $ignorePrefixes = ['_ignition', 'livewire', 'filament', 'vendor', 'sanctum', 'api', 'storage'];
                    $shouldIgnore = false;
                    foreach ($ignorePrefixes as $prefix) {
                        if (str_starts_with($permission->name, $prefix)) {
                            $shouldIgnore = true;
                            break;
                        }
                    }
                    if ($shouldIgnore) {
                        continue;
                    }
                    
                    // Ambil resource name (semua bagian kecuali yang terakhir)
                    if (count($parts) >= 2) {
                        $resourceName = implode('.', array_slice($parts, 0, -1));
                        $action = end($parts);
                        
                        // Hanya proses jika action adalah create, store, edit, update, destroy, delete
                        if (in_array($action, ['create', 'store', 'edit', 'update', 'destroy', 'delete', 'show', 'index'])) {
                            if (!isset($permissionGroups[$resourceName])) {
                                $permissionGroups[$resourceName] = [];
                            }
                            $permissionGroups[$resourceName][] = $permission;
                        }
                    }
                }
                
                // Untuk setiap resource group, cek apakah ada wildcard permission
                foreach ($permissionGroups as $resourceName => $permissions) {
                    $wildcardName = $resourceName . '.*';
                    $wildcardPermission = Permission::where('name', $wildcardName)->first();
                    
                    if ($wildcardPermission) {
                        // Cek apakah role sudah punya wildcard permission
                        $hasWildcard = $role->permissions->contains('id', $wildcardPermission->id);
                        
                        if (!$hasWildcard) {
                            // Tambahkan wildcard permission ke role
                            $role->permissions()->attach($wildcardPermission->id);
                            $this->line("  ✓ Added wildcard: {$wildcardName}");
                            $migrated++;
                        } else {
                            $this->line("  - Already has wildcard: {$wildcardName}");
                            $skipped++;
                        }
                    }
                }
            }
            
            DB::commit();
            $this->newLine();
            $this->info("✓ Berhasil memigrasikan {$migrated} permission ke wildcard!");
            $this->info("✓ Skipped {$skipped} permission yang sudah ada!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal memigrasikan permission: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}


