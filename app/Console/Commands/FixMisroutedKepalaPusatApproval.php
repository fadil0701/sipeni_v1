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

    protected $description = 'Perbaiki permintaan yang salah menunggu Kepala Pusat padahal stok tersedia';

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
        $fixed = 0;

        foreach ($permintaanIds as $idPermintaan) {
            $permintaan = PermintaanBarang::with('detailPermintaan')->find($idPermintaan);
            if (! $permintaan) {
                continue;
            }

            if ($service->repairMisroutedKepalaPusat($permintaan)) {
                $fixed++;
                $this->info("✓ Permintaan #{$idPermintaan} dialihkan ke disposisi gudang.");
            }
        }

        $this->info($fixed > 0
            ? "Selesai. {$fixed} permintaan diperbaiki."
            : 'Tidak ada permintaan yang perlu diperbaiki.');

        return self::SUCCESS;
    }
}
