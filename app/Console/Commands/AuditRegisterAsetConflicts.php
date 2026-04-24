<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\RegisterAset;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AuditRegisterAsetConflicts extends Command
{
    protected $signature = 'register-aset:audit-conflicts {--fix : Perbaiki konflik yang bisa di-resolve otomatis}';

    protected $description = 'Audit konflik register_aset vs inventory_item dan opsional fix sinkronisasi single source';

    public function handle(): int
    {
        if (! Schema::hasColumn('register_aset', 'id_item')) {
            $this->error('Kolom register_aset.id_item belum ada. Tidak bisa audit konflik single source.');
            return self::FAILURE;
        }

        $registers = RegisterAset::query()->orderBy('id_register_aset')->get();
        $isFix = (bool) $this->option('fix');

        $report = [
            'total' => $registers->count(),
            'id_item_null' => 0,
            'id_item_missing' => 0,
            'inventory_mismatch' => 0,
            'nomor_mismatch' => 0,
            'duplicate_id_item' => 0,
            'fixed' => 0,
            'unresolved' => 0,
        ];

        $duplicateItemIds = RegisterAset::query()
            ->whereNotNull('id_item')
            ->groupBy('id_item')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('id_item')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($registers as $register) {
            $hasConflict = false;
            $inventoryItem = null;

            if (empty($register->id_item)) {
                $report['id_item_null']++;
                $hasConflict = true;
            } else {
                $inventoryItem = InventoryItem::query()->find($register->id_item);
                if (! $inventoryItem) {
                    $report['id_item_missing']++;
                    $hasConflict = true;
                }
            }

            if ($inventoryItem && (int) $register->id_inventory !== (int) $inventoryItem->id_inventory) {
                $report['inventory_mismatch']++;
                $hasConflict = true;
            }

            if ($inventoryItem && (string) $register->nomor_register !== (string) $inventoryItem->kode_register) {
                $report['nomor_mismatch']++;
                $hasConflict = true;
            }

            if (! empty($register->id_item) && in_array((int) $register->id_item, $duplicateItemIds, true)) {
                $report['duplicate_id_item']++;
                $hasConflict = true;
            }

            if (! $hasConflict) {
                continue;
            }

            if (! $isFix) {
                $report['unresolved']++;
                continue;
            }

            $fixed = $this->fixRegister($register, $duplicateItemIds);
            if ($fixed) {
                $report['fixed']++;
            } else {
                $report['unresolved']++;
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total register', $report['total']],
                ['id_item null', $report['id_item_null']],
                ['id_item tidak ditemukan', $report['id_item_missing']],
                ['id_inventory mismatch', $report['inventory_mismatch']],
                ['nomor_register mismatch', $report['nomor_mismatch']],
                ['duplicate id_item', $report['duplicate_id_item']],
                ['Fixed', $report['fixed']],
                ['Unresolved', $report['unresolved']],
            ]
        );

        if (! $isFix) {
            $this->info('Jalankan ulang dengan --fix untuk memperbaiki konflik yang bisa disinkronkan otomatis.');
        }

        return self::SUCCESS;
    }

    private function fixRegister(RegisterAset $register, array $duplicateItemIds): bool
    {
        $candidate = null;

        if ($register->id_item) {
            $candidate = InventoryItem::query()->find($register->id_item);
        }

        if (! $candidate && $register->id_inventory) {
            if (! empty($register->nomor_register)) {
                $candidate = InventoryItem::query()
                    ->where('id_inventory', $register->id_inventory)
                    ->where('kode_register', $register->nomor_register)
                    ->first();
            }

            if (! $candidate) {
                $usedItemIds = RegisterAset::query()
                    ->whereNotNull('id_item')
                    ->where('id_register_aset', '!=', $register->id_register_aset)
                    ->pluck('id_item')
                    ->map(fn ($v) => (int) $v)
                    ->all();

                $candidate = InventoryItem::query()
                    ->where('id_inventory', $register->id_inventory)
                    ->when(! empty($usedItemIds), fn ($q) => $q->whereNotIn('id_item', $usedItemIds))
                    ->orderBy('id_item')
                    ->first();
            }
        }

        if (! $candidate) {
            return false;
        }

        if (in_array((int) $candidate->id_item, $duplicateItemIds, true)) {
            $owner = RegisterAset::query()
                ->where('id_item', $candidate->id_item)
                ->orderBy('id_register_aset')
                ->first();

            if ($owner && (int) $owner->id_register_aset !== (int) $register->id_register_aset) {
                return false;
            }
        }

        $register->id_item = $candidate->id_item;
        $register->id_inventory = $candidate->id_inventory;
        $register->nomor_register = $candidate->kode_register;
        $register->save();

        return true;
    }
}