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

            $pendingLog->loadMissing('approvalFlow');
            $resultStatus = $this->resolveApprovedStatus($pendingLog);

            $pendingLog->update([
                'status' => $resultStatus,
                'catatan' => $catatan,
                'approved_at' => now(),
                'user_id' => $actor->id,
            ]);

            $this->createNextPendingLogs($pendingLog);
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

    private function resolveApprovedStatus(ApprovalLog $pendingLog): string
    {
        $step = (int) ($pendingLog->approvalFlow?->step_order ?? 0);

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
            ApprovalLog::firstOrCreate(
                [
                    'modul_approval' => $currentLog->modul_approval,
                    'id_referensi' => $currentLog->id_referensi,
                    'id_approval_flow' => $nextFlow->id,
                ],
                [
                    'user_id' => null,
                    'role_id' => $nextFlow->role_id,
                    'status' => 'MENUNGGU',
                    'catatan' => null,
                    'approved_at' => null,
                ]
            );
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
        $status = match ($step) {
            2 => PermintaanBarangStatus::Diajukan,
            3 => PermintaanBarangStatus::Diverifikasi,
            default => PermintaanBarangStatus::ProsesDistribusi,
        };

        $this->permintaanStatusService->setStatus($permintaan, $status);
    }

}
