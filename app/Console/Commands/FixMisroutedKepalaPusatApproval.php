<?php

namespace App\Console\Commands;

use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\Role;
use App\Services\ApprovalPermintaanService;
use Illuminate\Console\Command;

class FixMisroutedKepalaPusatApproval extends Command
{
    protected $signature = 'approval:fix-misrouted-kepala-pusat {--id= : ID permintaan tertentu}';

    protected $description = 'Perbaiki routing approval: hapus Kepala Pusat yang tidak perlu, tambah disposisi gudang untuk item master';

    public function handle(ApprovalPermintaanService $service): int
    {
        $kepalaRole = Role::where('name', 'kepala_pusat')->first();
        if (! $kepalaRole) {
            $this->warn('Role kepala_pusat tidak ditemukan.');

            return self::FAILURE;
        }

        $kepalaFlowIds = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->where('role_id', $kepalaRole->id)
            ->pluck('id');

        $query = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->whereIn('id_approval_flow', $kepalaFlowIds)
            ->where('status', 'MENUNGGU');

        if ($id = $this->option('id')) {
            $query->where('id_referensi', (int) $id);
        }

        $permintaanIds = $query->pluck('id_referensi')->unique()->values();
        $fixedKepala = 0;

        foreach ($permintaanIds as $idPermintaan) {
            $permintaan = PermintaanBarang::with('detailPermintaan')->find($idPermintaan);
            if (! $permintaan) {
                continue;
            }

            if ($service->repairMisroutedKepalaPusat($permintaan)) {
                $fixedKepala++;
                $this->info("✓ Permintaan #{$idPermintaan} dialihkan ke disposisi gudang (hapus Kepala Pusat).");
            }
        }

        $mixedQuery = PermintaanBarang::query()->with('detailPermintaan');
        if ($id = $this->option('id')) {
            $mixedQuery->where('id_permintaan', (int) $id);
        } else {
            $mixedQuery->whereIn('status', [
                'diverifikasi',
                'menunggu_pengadaan',
                'proses_pengadaan',
                'barang_tersedia',
                'proses_distribusi',
            ]);
        }

        $fixedGudang = 0;
        foreach ($mixedQuery->get() as $permintaan) {
            if ($service->repairMissingGudangDisposisi($permintaan)) {
                $fixedGudang++;
                $this->info("✓ Permintaan #{$permintaan->id_permintaan} ditambahkan disposisi gudang untuk item master.");
            }
        }

        $total = $fixedKepala + $fixedGudang;
        $this->info($total > 0
            ? "Selesai. {$fixedKepala} diperbaiki (Kepala Pusat), {$fixedGudang} ditambah disposisi gudang."
            : 'Tidak ada permintaan yang perlu diperbaiki.');

        return self::SUCCESS;
    }
}
