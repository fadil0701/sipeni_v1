<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Permission;

class SyncPermissionsFromRoutes extends Command
{
    protected $signature = 'permission:sync-routes';
    protected $description = 'Sync permissions from all routes in web.php';

    public function handle()
    {
        $this->info('Mengecek semua route dan menambahkan permission yang missing...');
        $this->newLine();

        $routes = Route::getRoutes();
        $permissions = [];
        $missingPermissions = [];

        foreach ($routes as $route) {
            $routeName = $route->getName();
            
            // Skip jika tidak ada name atau route system
            if (!$routeName || 
                str_starts_with($routeName, 'ignition.') ||
                str_starts_with($routeName, 'sanctum.') ||
                str_starts_with($routeName, 'filament.') ||
                str_starts_with($routeName, 'livewire.') ||
                str_starts_with($routeName, 'storage.') ||
                $routeName === 'login' ||
                $routeName === 'logout' ||
                $routeName === 'logout.get') {
                continue;
            }
            
            // Skip API routes yang tidak perlu permission
            if (str_starts_with($routeName, 'api.') && 
                !in_array($routeName, [
                    'api.gudang.inventory',
                    'api.permintaan.detail',
                    'api.distribusi.detail',
                ])) {
                continue;
            }

            // Cek apakah permission sudah ada
            $permission = Permission::where('name', $routeName)->first();
            
            if (!$permission) {
                $missingPermissions[] = $routeName;
                
                // Generate display name dan description dari route name
                $displayName = $this->generateDisplayName($routeName);
                $description = $this->generateDescription($routeName);
                $module = $this->extractModule($routeName);
                $group = $this->extractGroup($routeName);
                $sortOrder = $this->calculateSortOrder($routeName);

                $permissions[] = [
                    'name' => $routeName,
                    'display_name' => $displayName,
                    'module' => $module,
                    'group' => $group,
                    'description' => $description,
                    'sort_order' => $sortOrder,
                ];
            }
        }

        if (empty($permissions)) {
            $this->info('✓ Semua permission sudah terdaftar!');
            return Command::SUCCESS;
        }

        $this->warn('Ditemukan ' . count($permissions) . ' permission yang missing:');
        $this->newLine();

        // Tampilkan daftar permission yang akan ditambahkan
        $this->table(
            ['Name', 'Display Name', 'Module', 'Group'],
            array_map(function($p) {
                return [
                    $p['name'],
                    $p['display_name'],
                    $p['module'],
                    $p['group'],
                ];
            }, $permissions)
        );

        if ($this->confirm('Apakah Anda ingin menambahkan permission-permission ini ke database?', true)) {
            $this->newLine();
            $this->info('Menambahkan permission...');

            $bar = $this->output->createProgressBar(count($permissions));
            $bar->start();

            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission['name']],
                    $permission
                );
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info('✓ Berhasil menambahkan ' . count($permissions) . ' permission!');
        } else {
            $this->info('Dibatalkan.');
        }

        return Command::SUCCESS;
    }

    private function generateDisplayName(string $routeName): string
    {
        // Convert route name ke display name
        // transaction.permintaan-barang.index -> View Permintaan Barang
        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[count($parts) - 2] ?? $parts[0];
        
        $actionMap = [
            'index' => 'View',
            'create' => 'Create',
            'store' => 'Create',
            'show' => 'View Detail',
            'edit' => 'Edit',
            'update' => 'Update',
            'destroy' => 'Delete',
            'ajukan' => 'Ajukan',
            'mengetahui' => 'Mengetahui',
            'verifikasi' => 'Verifikasi',
            'kembalikan' => 'Kembalikan',
            'approve' => 'Approve',
            'reject' => 'Reject',
            'disposisi' => 'Disposisi',
            'kirim' => 'Kirim',
            'diagram' => 'View Diagram',
        ];

        $actionText = $actionMap[$action] ?? ucfirst($action);
        $resourceText = str_replace('-', ' ', $resource);
        $resourceText = ucwords($resourceText);

        return $actionText . ' ' . $resourceText;
    }

    private function generateDescription(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[count($parts) - 2] ?? $parts[0];
        
        $actionMap = [
            'index' => 'Melihat daftar',
            'create' => 'Membuat',
            'store' => 'Menyimpan',
            'show' => 'Melihat detail',
            'edit' => 'Mengedit',
            'update' => 'Memperbarui',
            'destroy' => 'Menghapus',
            'ajukan' => 'Mengajukan',
            'mengetahui' => 'Memberi status mengetahui pada',
            'verifikasi' => 'Memverifikasi',
            'kembalikan' => 'Mengembalikan',
            'approve' => 'Menyetujui',
            'reject' => 'Menolak',
            'disposisi' => 'Melakukan disposisi',
            'kirim' => 'Mengirim',
            'diagram' => 'Melihat diagram',
        ];

        $actionText = $actionMap[$action] ?? ucfirst($action);
        $resourceText = str_replace('-', ' ', $resource);
        $resourceText = str_replace('_', ' ', $resourceText);

        return $actionText . ' ' . $resourceText;
    }

    private function extractModule(string $routeName): string
    {
        $parts = explode('.', $routeName);
        
        // Ambil bagian pertama sebagai module
        $module = $parts[0];
        
        // Mapping khusus
        $moduleMap = [
            'user' => 'dashboard',
            'master-manajemen' => 'master-manajemen',
            'master' => 'master-manajemen',
            'master-data' => 'master-data',
            'inventory' => 'inventory',
            'transaction' => 'transaction',
            'asset' => 'asset',
            'planning' => 'planning',
            'procurement' => 'procurement',
            'finance' => 'finance',
            'admin' => 'admin',
            'reports' => 'reports',
        ];

        return $moduleMap[$module] ?? $module;
    }

    private function extractGroup(string $routeName): string
    {
        $parts = explode('.', $routeName);
        
        // Ambil 2 bagian pertama sebagai group
        if (count($parts) >= 2) {
            return $parts[0] . '.' . $parts[1];
        }
        
        return $parts[0];
    }

    private function calculateSortOrder(string $routeName): int
    {
        $parts = explode('.', $routeName);
        $module = $parts[0];
        
        $baseOrder = [
            'user' => 1,
            'master-manajemen' => 10,
            'master' => 10,
            'master-data' => 20,
            'inventory' => 100,
            'transaction' => 200,
            'asset' => 300,
            'planning' => 400,
            'procurement' => 500,
            'finance' => 600,
            'admin' => 700,
            'reports' => 800,
        ];

        $base = $baseOrder[$module] ?? 900;
        
        // Tambahkan berdasarkan resource dan action
        $resource = $parts[1] ?? '';
        $action = end($parts);
        
        $resourceOrder = [
            'permintaan-barang' => 0,
            'approval' => 10,
            'draft-distribusi' => 20,
            'compile-distribusi' => 30,
            'distribusi' => 40,
            'penerimaan-barang' => 50,
            'retur-barang' => 60,
        ];

        $actionOrder = [
            'index' => 0,
            'create' => 1,
            'store' => 1,
            'show' => 2,
            'edit' => 3,
            'update' => 3,
            'destroy' => 4,
            'ajukan' => 5,
            'mengetahui' => 6,
            'verifikasi' => 7,
            'kembalikan' => 8,
            'approve' => 9,
            'reject' => 10,
            'disposisi' => 11,
            'kirim' => 12,
            'diagram' => 13,
        ];

        $resourceOffset = $resourceOrder[$resource] ?? 0;
        $actionOffset = $actionOrder[$action] ?? 0;

        return $base + $resourceOffset + $actionOffset;
    }
}

