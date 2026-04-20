<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class SimplifyPermissionsAdvanced extends Command
{
    protected $signature = 'permission:simplify-advanced {--dry-run : Tampilkan preview tanpa melakukan perubahan}';
    protected $description = 'Menyederhanakan permission dengan menggabungkan create/store, edit/update menjadi satu permission atau wildcard';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - Tidak ada perubahan yang akan dilakukan');
            $this->newLine();
        }

        $this->info('Menyederhanakan permission...');
        $this->newLine();

        // Mapping action yang bisa digabungkan
        $actionGroups = [
            'create' => ['create', 'store'],  // create dan store -> create saja
            'edit' => ['edit', 'update'],      // edit dan update -> edit saja
            'view' => ['index', 'show'],       // index dan show -> view saja (opsional)
        ];

        // Ambil semua permission
        $permissions = Permission::all();
        $resourceGroups = [];

        // Group permission berdasarkan resource
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            
            // Skip jika sudah wildcard atau bukan resource permission
            if (str_ends_with($permission->name, '.*')) {
                continue;
            }

            // Skip system routes
            $ignorePrefixes = ['_ignition', 'livewire', 'filament', 'vendor', 'sanctum', 'storage'];
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

                if (!isset($resourceGroups[$resourceName])) {
                    $resourceGroups[$resourceName] = [];
                }
                $resourceGroups[$resourceName][] = [
                    'permission' => $permission,
                    'action' => $action,
                ];
            }
        }

        $this->info('Ditemukan ' . count($resourceGroups) . ' resource groups untuk disederhanakan.');
        $this->newLine();

        $simplified = 0;
        $deleted = 0;
        $changes = [];

        DB::beginTransaction();
        try {
            foreach ($resourceGroups as $resourceName => $items) {
                $actions = array_column($items, 'action');
                $permissionsToKeep = [];
                $permissionsToDelete = [];
                $permissionsToRename = [];

                // Cek apakah ada create dan store
                if (in_array('create', $actions) && in_array('store', $actions)) {
                    $createPerm = collect($items)->firstWhere('action', 'create')['permission'];
                    $storePerm = collect($items)->firstWhere('action', 'store')['permission'];
                    
                    // Update display_name create untuk lebih jelas
                    if ($createPerm->display_name === $storePerm->display_name) {
                        $permissionsToKeep[] = $createPerm;
                        $permissionsToDelete[] = $storePerm;
                        $changes[] = "  • {$resourceName}: Gabungkan create+store -> create";
                    }
                }

                // Cek apakah ada edit dan update
                if (in_array('edit', $actions) && in_array('update', $actions)) {
                    $editPerm = collect($items)->firstWhere('action', 'edit')['permission'];
                    $updatePerm = collect($items)->firstWhere('action', 'update')['permission'];
                    
                    // Update display_name edit untuk lebih jelas
                    if ($editPerm->display_name !== $updatePerm->display_name) {
                        $permissionsToKeep[] = $editPerm;
                        $permissionsToDelete[] = $updatePerm;
                        $changes[] = "  • {$resourceName}: Gabungkan edit+update -> edit";
                    } else {
                        $permissionsToKeep[] = $editPerm;
                        $permissionsToDelete[] = $updatePerm;
                        $changes[] = "  • {$resourceName}: Gabungkan edit+update -> edit";
                    }
                }

                // Hapus permission yang duplikat
                if (!empty($permissionsToDelete)) {
                    foreach ($permissionsToDelete as $perm) {
                        if (!$dryRun) {
                            // Pindahkan role permission dari store/update ke create/edit
                            if ($perm->name === $resourceName . '.store' && !empty($permissionsToKeep)) {
                                $createPerm = collect($permissionsToKeep)->first(function($p) use ($resourceName) {
                                    return str_ends_with($p->name, '.create');
                                });
                                if ($createPerm) {
                                    $this->migrateRolePermissions($perm, $createPerm);
                                }
                            } elseif ($perm->name === $resourceName . '.update' && !empty($permissionsToKeep)) {
                                $editPerm = collect($permissionsToKeep)->first(function($p) use ($resourceName) {
                                    return str_ends_with($p->name, '.edit');
                                });
                                if ($editPerm) {
                                    $this->migrateRolePermissions($perm, $editPerm);
                                }
                            }
                            
                            // Hapus dari role_permission
                            DB::table('permission_role')->where('permission_id', $perm->id)->delete();
                            $perm->delete();
                        }
                        $deleted++;
                    }
                    $simplified++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            $this->newLine();
            if ($dryRun) {
                $this->warn('PREVIEW PERUBAHAN:');
                $this->newLine();
                foreach ($changes as $change) {
                    $this->line($change);
                }
                $this->newLine();
                $this->info("Akan menyederhanakan {$simplified} resource groups");
                $this->info("Akan menghapus {$deleted} permission yang duplikat");
                $this->newLine();
                $this->warn('Jalankan tanpa --dry-run untuk menerapkan perubahan');
            } else {
                $this->info("✓ Berhasil menyederhanakan {$simplified} resource groups!");
                $this->info("✓ Dihapus {$deleted} permission yang duplikat!");
                $this->newLine();
                $this->warn("⚠ PENTING: Permission store dan update telah digabungkan dengan create dan edit.");
                $this->warn("⚠ Role yang menggunakan store/update telah dipindahkan ke create/edit.");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal menyederhanakan permission: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function migrateRolePermissions($oldPermission, $newPermission)
    {
        // Ambil semua role yang menggunakan oldPermission
        $roles = DB::table('permission_role')
            ->where('permission_id', $oldPermission->id)
            ->pluck('role_id');

        foreach ($roles as $roleId) {
            // Cek apakah role sudah punya newPermission
            $hasNew = DB::table('permission_role')
                ->where('role_id', $roleId)
                ->where('permission_id', $newPermission->id)
                ->exists();

            if (!$hasNew) {
                // Tambahkan newPermission ke role
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $newPermission->id,
                ]);
            }
        }
    }
}


