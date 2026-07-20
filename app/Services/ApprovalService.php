<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        private readonly PermintaanBarangStatusService $permintaanStatusService
    ) {}

    public function approve(ApprovalLog $pendingLog, User $actor, ?string $catatan = null): ApprovalLog
    {
        return DB::transaction(function () use ($pendingLog, $actor, $catatan): ApprovalLog {
            $pendingLog = ApprovalLog::whereKey($pendingLog->id)->lockForUpdate()->firstOrFail();
            if ($pendingLog->status !== 'MENUNGGU') {
                throw new \RuntimeException('Approval ini sudah diproses.');
            }

            $pendingLog->loadMissing('approvalFlow.role');
            $resultStatus = $this->resolveApprovedStatus($pendingLog);

            $pendingLog->update([
                'status' => $resultStatus,
                'catatan' => $catatan,
                'approved_at' => now(),
                'user_id' => $actor->id,
            ]);

            // Step 3 & Kepala Pusat untuk PERMINTAAN_BARANG: next logs dibuat secara selektif
            // di ApprovalPermintaanService (berdasarkan stok / jalur pengadaan).
            if (! $this->shouldSkipAutoNextLogs($pendingLog)) {
                $this->createNextPendingLogs($pendingLog);
            }

            $this->syncPermintaanStatus($pendingLog, true);

            return $pendingLog->fresh(['approvalFlow', 'user', 'role']);
        });
    }

    public function reject(ApprovalLog $pendingLog, User $actor, string $catatan): ApprovalLog
    {
        return DB::transaction(function () use ($pendingLog, $actor, $catatan): ApprovalLog {
            $pendingLog = ApprovalLog::whereKey($pendingLog->id)->lockForUpdate()->firstOrFail();
            if ($pendingLog->status !== 'MENUNGGU') {
                throw new \RuntimeException('Approval ini sudah diproses.');
            }

            $pendingLog->update([
                'status' => 'DITOLAK',
                'catatan' => $catatan,
                'approved_at' => now(),
                'user_id' => $actor->id,
            ]);

            $this->syncPermintaanStatus($pendingLog, false);

            return $pendingLog->fresh(['approvalFlow', 'user', 'role']);
        });
    }

    public function history(string $modulApproval, int $idReferensi): Collection
    {
        return ApprovalLog::where('modul_approval', $modulApproval)
            ->where('id_referensi', $idReferensi)
            ->with(['approvalFlow.role', 'user', 'role'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Buat satu approval log MENUNGGU untuk flow tertentu (idempotent).
     */
    public function createPendingLog(
        ApprovalFlowDefinition $flow,
        string $modulApproval,
        int $idReferensi,
        ?string $catatan = null
    ): ApprovalLog {
        $log = ApprovalLog::firstOrCreate(
            [
                'modul_approval' => $modulApproval,
                'id_referensi' => $idReferensi,
                'id_approval_flow' => $flow->id,
            ],
            [
                'user_id' => null,
                'role_id' => $flow->role_id,
                'status' => 'MENUNGGU',
                'catatan' => $catatan,
                'approved_at' => null,
            ]
        );

        if ($log->wasRecentlyCreated) {
            AppNotificationService::notifyApprovalPending($log);
        }

        return $log;
    }

    private function shouldSkipAutoNextLogs(ApprovalLog $pendingLog): bool
    {
        if ($pendingLog->modul_approval !== 'PERMINTAAN_BARANG') {
            return false;
        }

        $step = (int) ($pendingLog->approvalFlow?->step_order ?? 0);
        $roleName = $pendingLog->approvalFlow?->role?->name;

        // Setelah verifikasi Kasubbag TU / persetujuan Kepala Pusat: next step dibuat selektif.
        return $step === 3 || $roleName === 'kepala_pusat';
    }

    private function resolveApprovedStatus(ApprovalLog $pendingLog): string
    {
        $step = (int) ($pendingLog->approvalFlow?->step_order ?? 0);
        $roleName = $pendingLog->approvalFlow?->role?->name;

        if ($roleName === 'kepala_pusat') {
            return 'DISETUJUI';
        }

        return match ($step) {
            2 => 'DIKETAHUI',
            3 => 'DIVERIFIKASI',
            default => 'DISETUJUI',
        };
    }

    private function createNextPendingLogs(ApprovalLog $currentLog): void
    {
        $flow = $currentLog->approvalFlow;
        if (! $flow) {
            return;
        }

        $nextStepOrder = ApprovalFlowDefinition::where('modul_approval', $flow->modul_approval)
            ->where('step_order', '>', $flow->step_order)
            ->min('step_order');

        if (! $nextStepOrder) {
            return;
        }

        $nextFlows = ApprovalFlowDefinition::where('modul_approval', $flow->modul_approval)
            ->where('step_order', $nextStepOrder)
            ->get();

        foreach ($nextFlows as $nextFlow) {
            $this->createPendingLog($nextFlow, $currentLog->modul_approval, (int) $currentLog->id_referensi);
        }
    }

    private function syncPermintaanStatus(ApprovalLog $log, bool $approved): void
    {
        if ($log->modul_approval !== 'PERMINTAAN_BARANG') {
            return;
        }

        $permintaan = PermintaanBarang::find($log->id_referensi);
        if (! $permintaan) {
            return;
        }

        if (! $approved) {
            $this->permintaanStatusService->setStatus($permintaan, PermintaanBarangStatus::Ditolak);

            return;
        }

        $step = (int) ($log->approvalFlow?->step_order ?? 0);
        $roleName = $log->approvalFlow?->role?->name;

        // Status akhir setelah Kepala Pusat / disposisi gudang diatur di ApprovalPermintaanService.
        if ($roleName === 'kepala_pusat') {
            return;
        }

        $status = match ($step) {
            2 => PermintaanBarangStatus::Diajukan,
            3 => PermintaanBarangStatus::Diverifikasi,
            default => PermintaanBarangStatus::ProsesDistribusi,
        };

        $this->permintaanStatusService->setStatus($permintaan, $status);
    }
}
