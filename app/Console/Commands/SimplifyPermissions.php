<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class SimplifyPermissions extends Command
{
    protected $signature = 'permission:simplify';
    protected $description = 'Menyederhanakan permission dengan menggabungkan create/store, edit/update menjadi wildcard permission';

    public function handle()
    {
        $this->info('Menyederhanakan permission...');
        $this->newLine();

        // Mapping untuk menggabungkan permission yang duplikat
        $simplifyMap = [
            // Resource CRUD operations - gabungkan menjadi wildcard
            'create' => '.*',  // create + store -> .*
            'store' => '.*',   // akan dihapus, diganti dengan .*
            'edit' => '.*',    // edit + update -> .*
            'update' => '.*',   // akan dihapus, diganti dengan .*
            'destroy' => '.*', // destroy -> .*
            'delete' => '.*',   // delete -> .*
        ];

        // Ambil semua permission yang perlu disederhanakan
        $permissions = Permission::all();
        $groups = [];

        // Group permission berdasarkan resource
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            
            // Skip jika sudah wildcard atau bukan resource permission
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

                if (!isset($groups[$resourceName])) {
                    $groups[$resourceName] = [];
                }
                $groups[$resourceName][] = [
                    'permission' => $permission,
                    'action' => $action,
                ];
            }
        }

        $this->info('Ditemukan ' . count($groups) . ' resource groups untuk disederhanakan.');
        $this->newLine();

        $simplified = 0;
        $deleted = 0;

        DB::beginTransaction();
        try {
            foreach ($groups as $resourceName => $items) {
                // Cek apakah sudah ada wildcard permission
                $wildcardName = $resourceName . '.*';
                $wildcardPermission = Permission::where('name', $wildcardName)->first();

                // Hitung berapa permission yang akan digabungkan
                $actionsToSimplify = ['create', 'store', 'edit', 'update', 'destroy', 'delete'];
                $hasSimplifiable = false;
                $permissionsToDelete = [];

                foreach ($items as $item) {
                    if (in_array($item['action'], $actionsToSimplify)) {
                        $hasSimplifiable = true;
                        $permissionsToDelete[] = $item['permission'];
                    }
                }

                // Jika ada permission yang bisa disederhanakan
                if ($hasSimplifiable && count($permissionsToDelete) > 0) {
                    // Buat atau update wildcard permission
                    if (!$wildcardPermission) {
                        $displayName = collect(explode('.', $resourceName))
                            ->map(fn($part) => ucwords(str_replace(['-', '_'], ' ', $part)))
                            ->implode(' ');
                        
                        $module = explode('.', $resourceName)[0] ?? 'general';
                        
                        $wildcardPermission = Permission::create([
                            'name' => $wildcardName,
                            'display_name' => $displayName . ' (All)',
                            'module' => $module,
                            'group' => $resourceName,
                            'description' => 'Akses penuh ke ' . str_replace('.', ' ', $resourceName),
                            'sort_order' => 999,
                        ]);
                        $this->line("  ✓ Created: {$wildcardName}");
                    }

                    // Hapus permission yang duplikat
                    foreach ($permissionsToDelete as $perm) {
                        // Hapus dari role_permission terlebih dahulu
                        DB::table('permission_role')->where('permission_id', $perm->id)->delete();
                        $perm->delete();
                        $deleted++;
                    }

                    $simplified++;
                    $this->line("  ✓ Simplified: {$resourceName} (deleted " . count($permissionsToDelete) . " permissions)");
                }
            }

            DB::commit();
            $this->newLine();
            $this->info("✓ Berhasil menyederhanakan {$simplified} resource groups!");
            $this->info("✓ Dihapus {$deleted} permission yang duplikat!");
            $this->newLine();
            $this->warn("⚠ PENTING: Permission yang dihapus akan digantikan dengan wildcard permission (*).");
            $this->warn("⚠ Pastikan untuk mengupdate role yang menggunakan permission yang dihapus.");
            $this->newLine();
            $this->info("💡 Tips: Gunakan 'permission:sync-routes' untuk menambahkan permission baru dari routes.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal menyederhanakan permission: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}


