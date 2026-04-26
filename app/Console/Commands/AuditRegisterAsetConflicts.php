<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\KartuInventarisRuangan;
use App\Models\MasterPegawai;
use App\Models\MasterRuangan;
use App\Models\RegisterAset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class AuditRegisterAsetConflicts extends Command
{
    protected $signature = 'register-aset:audit-conflicts {--fix : Perbaiki konflik yang bisa di-resolve otomatis}';

    protected $description = 'Audit konsistensi Register Aset, Inventory Item, dan KIR. Opsi --fix hanya memperbaiki mismatch aman.';

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
            'duplicate_id_item' => 0,
            'duplicate_nomor_register' => 0,
            'kir_ruangan_mismatch' => 0,
            'kir_pj_unit_mismatch' => 0,
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

        $duplicateNomorRegisters = RegisterAset::query()
            ->whereNotNull('nomor_register')
            ->groupBy('nomor_register')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nomor_register')
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

            if (!empty($register->nomor_register) && in_array((string) $register->nomor_register, $duplicateNomorRegisters, true)) {
                $report['duplicate_nomor_register']++;
                $hasConflict = true;
            }

            if (! empty($register->id_item) && in_array((int) $register->id_item, $duplicateItemIds, true)) {
                $report['duplicate_id_item']++;
                $hasConflict = true;
            }

            $kir = KartuInventarisRuangan::query()->where('id_register_aset', $register->id_register_aset)->first();
            if ($kir && (int) $register->id_ruangan !== (int) $kir->id_ruangan) {
                $report['kir_ruangan_mismatch']++;
                $hasConflict = true;
            }
            if ($kir && !empty($kir->id_penanggung_jawab) && !empty($register->id_unit_kerja)) {
                $pj = MasterPegawai::query()->select('id', 'id_unit_kerja')->find($kir->id_penanggung_jawab);
                if ($pj && (int) $pj->id_unit_kerja !== (int) $register->id_unit_kerja) {
                    $report['kir_pj_unit_mismatch']++;
                    $hasConflict = true;
                }
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
                ['duplicate id_item', $report['duplicate_id_item']],
                ['duplicate nomor_register', $report['duplicate_nomor_register']],
                ['KIR ruangan mismatch', $report['kir_ruangan_mismatch']],
                ['KIR penanggung jawab beda unit', $report['kir_pj_unit_mismatch']],
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
        $changed = false;

        if ($register->id_item) {
            $candidate = InventoryItem::query()->find($register->id_item);
        }

        if (! $candidate && $register->id_inventory) {
            if (! empty($register->nomor_register)) {
                $candidate = InventoryItem::query()
                    ->where('id_inventory', $register->id_inventory)
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

        if ((int) $register->id_item !== (int) $candidate->id_item) {
            $register->id_item = $candidate->id_item;
            $changed = true;
        }
        if ((int) $register->id_inventory !== (int) $candidate->id_inventory) {
            $register->id_inventory = $candidate->id_inventory;
            $changed = true;
        }

        $kir = KartuInventarisRuangan::query()->where('id_register_aset', $register->id_register_aset)->first();
        if ($kir && (int) $register->id_ruangan !== (int) $kir->id_ruangan) {
            $register->id_ruangan = $kir->id_ruangan;
            $changed = true;
        }
        if ($kir && $register->id_ruangan) {
            $ruangan = MasterRuangan::query()->select('id_ruangan', 'id_unit_kerja')->find($register->id_ruangan);
            if ($ruangan && (int) $register->id_unit_kerja !== (int) $ruangan->id_unit_kerja) {
                $register->id_unit_kerja = $ruangan->id_unit_kerja;
                $changed = true;
            }
        }

        if ($changed) {
            $register->save();
        }

        return $changed;
    }
}