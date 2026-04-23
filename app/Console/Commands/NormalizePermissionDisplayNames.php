<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Support\PermissionModule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizePermissionDisplayNames extends Command
{
    protected $signature = 'permission:normalize-display-names {--dry-run : Tampilkan preview tanpa update data} {--yes : Lewati konfirmasi}';

    protected $description = 'Normalkan display_name permission agar create/store dan edit/update lebih jelas.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $skipConfirm = (bool) $this->option('yes');

        $permissions = Permission::query()
            ->where('name', 'not like', '%.*')
            ->orderBy('name')
            ->get();

        if ($permissions->isEmpty()) {
            $this->info('Tidak ada permission yang perlu diproses.');
            return self::SUCCESS;
        }

        $changes = [];

        foreach ($permissions as $permission) {
            $newDisplayName = $this->generateDisplayName($permission->name);
            $newDescription = $this->generateDescription($permission->name);
            $newModule = $this->extractModule($permission->name);
            $newGroup = $this->extractGroup($permission->name);
            $newSortOrder = $this->calculateSortOrder($permission->name);

            $dirty = false;
            $from = [];
            $to = [];

            foreach ([
                'display_name' => $newDisplayName,
                'description' => $newDescription,
                'module' => $newModule,
                'group' => $newGroup,
                'sort_order' => $newSortOrder,
            ] as $field => $value) {
                if ($permission->{$field} !== $value) {
                    $dirty = true;
                    $from[$field] = $permission->{$field};
                    $to[$field] = $value;
                }
            }

            if ($dirty) {
                $changes[] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'from' => $from,
                    'to' => $to,
                ];
            }
        }

        if (empty($changes)) {
            $this->info('Semua permission sudah sesuai standar display name terbaru.');
            return self::SUCCESS;
        }

        $this->warn('Ditemukan ' . count($changes) . ' permission yang akan diperbarui.');
        $this->newLine();

        $previewRows = array_map(function (array $change) {
            return [
                $change['name'],
                $change['from']['display_name'] ?? '-',
                $change['to']['display_name'] ?? '-',
            ];
        }, array_slice($changes, 0, 20));

        $this->table(['Permission', 'Display Name Lama', 'Display Name Baru'], $previewRows);
        if (count($changes) > 20) {
            $this->line('... dan ' . (count($changes) - 20) . ' perubahan lain.');
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry run selesai. Tidak ada data yang diubah.');
            return self::SUCCESS;
        }

        if (!$skipConfirm && !$this->confirm('Lanjutkan update display_name permission?', true)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as $change) {
                Permission::where('id', $change['id'])->update($change['to']);
            }
        });

        $this->newLine();
        $this->info('Berhasil memperbarui ' . count($changes) . ' permission.');

        return self::SUCCESS;
    }

    private function generateDisplayName(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[count($parts) - 2] ?? $parts[0];

        $actionMap = [
            'index' => 'View',
            'create' => 'Open Create Form',
            'store' => 'Store New',
            'show' => 'View Detail',
            'edit' => 'Open Edit Form',
            'update' => 'Update Existing',
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
        $resourceText = (string) str_replace('-', ' ', $resource);
        $resourceText = (string) ucwords($resourceText);

        return $actionText . ' ' . $resourceText;
    }

    private function generateDescription(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[count($parts) - 2] ?? $parts[0];

        $actionMap = [
            'index' => 'Melihat daftar',
            'create' => 'Membuka form tambah',
            'store' => 'Menyimpan data baru',
            'show' => 'Melihat detail',
            'edit' => 'Membuka form edit',
            'update' => 'Menyimpan perubahan',
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
        $first = $parts[0] ?? '';

        return PermissionModule::moduleKeyFromRoutePrefix($first);
    }

    private function extractGroup(string $routeName): string
    {
        $parts = explode('.', $routeName);

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
            'maintenance' => 250,
            'asset' => 300,
            'planning' => 400,
            'procurement' => 500,
            'finance' => 600,
            'admin' => 700,
            'reports' => 800,
        ];

        $base = $baseOrder[$module] ?? 900;
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

