<?php

namespace App\Services\Rku;

use App\Models\RkuApprovalHistory;
use App\Models\RkuAuditLog;
use App\Models\RkuHeader;
use App\Models\User;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Support\Facades\DB;

class RkuWorkflowService
{
    public function __construct(
        private readonly WorkflowEngine $workflowEngine = new WorkflowEngine(),
    ) {}

    public function submit(RkuHeader $rku, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->transition($rku, 'submit', 'Submitted for approval', $user);
        }

        return $this->legacySubmit($rku, $user);
    }

    public function approve(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            $current = $this->workflowEngine->getCurrentStep($rku);
            $action = match ($current?->code) {
                'REVIEW_KASUBAG_TU' => 'forward',
                'REVIEW_KEPALA_PUSAT' => 'approve',
                default => 'approve',
            };

            return $this->workflowEngine->transition($rku, $action, $notes, $user);
        }

        return $this->legacyApprove($rku, $notes, $user);
    }

    public function reject(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->transition($rku, 'reject', $notes, $user);
        }

        return $this->legacyReject($rku, $notes, $user);
    }

    public function startReview(RkuHeader $rku, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->transition($rku, 'review', 'Started review', $user);
        }

        return $this->legacyStartReview($rku, $user);
    }

    public function cancel(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->transition($rku, 'cancel', $notes, $user);
        }

        return $this->legacyCancel($rku, $notes, $user);
    }

    public function revise(RkuHeader $rku, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            if ($rku->status_rku === RkuHeader::STATUS_DITOLAK) {
                return $this->workflowEngine->transition($rku, 'request_revision', 'Revision requested', $user);
            }

            return $this->workflowEngine->transition($rku, 'revise', 'Revised to draft', $user);
        }

        return $this->legacyRevise($rku, $user);
    }

    public function requestRevision(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->transition($rku, 'request_revision', $notes, $user);
        }

        return $this->legacyRevise($rku, $user);
    }

    public function canTransition(RkuHeader $rku, string $transition, ?User $user = null): bool
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->canTransition($rku, $transition, $user);
        }

        return $this->legacyCanTransition($rku, $transition, $user);
    }

    /**
     * @return array<string, array{label: string, action: string}>
     */
    public function getAvailableTransitions(RkuHeader $rku, ?User $user = null): array
    {
        if ($this->workflowEngine->isEnabled()) {
            $labels = [
                'submit' => 'Ajukan',
                'cancel' => 'Batalkan',
                'review' => 'Review',
                'forward' => 'Teruskan ke Kepala Pusat',
                'approve' => 'Setujui',
                'reject' => 'Tolak',
                'request_revision' => 'Minta Revisi',
                'revise' => 'Revisi ke Draft',
            ];

            $out = [];
            foreach ($this->workflowEngine->getAvailableTransitions($rku, $user) as $transition) {
                $out[$transition->action] = [
                    'label' => $labels[$transition->action] ?? ucfirst(str_replace('_', ' ', $transition->action)),
                    'action' => $transition->action,
                ];
            }

            if ($rku->status_rku === RkuHeader::STATUS_REVISION_REQUIRED
                && $this->workflowEngine->canTransition($rku, 'revise', $user)) {
                $out['revise'] = ['label' => 'Revisi ke Draft', 'action' => 'revise'];
            }

            return $out;
        }

        $legacy = $this->legacyGetAvailableTransitions($rku, $user);
        $mapped = [];
        foreach ($legacy as $key => $enabled) {
            if ($enabled) {
                $mapped[$key] = ['label' => $key, 'action' => $key];
            }
        }

        return $mapped;
    }

    public function getWorkflowHistory(RkuHeader $rku)
    {
        if ($this->workflowEngine->isEnabled()) {
            return $this->workflowEngine->getHistory($rku);
        }

        return $rku->approvalHistories()->with('approver')->orderByDesc('created_at')->get();
    }

    // --- Legacy fallback (rku_workflow_transitions) ---

    protected function legacySubmit(RkuHeader $rku, ?User $user = null): RkuHeader
    {
        $user = $user ?? auth()->user();
        if (! $this->legacyCanTransition($rku, 'submit', $user)) {
            throw new \RuntimeException('RKU tidak dapat disubmit.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $rku->status_rku;
            $rku->update(['status_rku' => RkuHeader::STATUS_DIAJUKAN, 'submitted_at' => now()]);
            $this->createApprovalHistory($rku, $oldStatus, RkuHeader::STATUS_DIAJUKAN, $user, 'Submitted for approval');
            RkuAuditLog::log($rku->id_rku, RkuAuditLog::ACTION_SUBMITTED, ['status_rku' => $oldStatus], ['status_rku' => RkuHeader::STATUS_DIAJUKAN], ['status_rku']);
            $rku->createVersionSnapshot('Submit for approval');
            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function legacyApprove(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        $user = $user ?? auth()->user();
        if (! $this->legacyCanTransition($rku, 'approve', $user)) {
            throw new \RuntimeException('RKU tidak dapat diapprove.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $rku->status_rku;
            $rku->update([
                'status_rku' => RkuHeader::STATUS_DISETUJUI,
                'id_approver' => $user->pegawai?->id,
                'tanggal_approval' => now(),
                'approved_at' => now(),
                'catatan_approval' => $notes,
                'notes' => $notes,
            ]);
            $this->createApprovalHistory($rku, $oldStatus, RkuHeader::STATUS_DISETUJUI, $user, $notes, true);
            RkuAuditLog::log($rku->id_rku, RkuAuditLog::ACTION_APPROVED, ['status_rku' => $oldStatus], ['status_rku' => RkuHeader::STATUS_DISETUJUI], ['status_rku']);
            $rku->createVersionSnapshot('Approved');
            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function legacyReject(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        $user = $user ?? auth()->user();
        if (! $this->legacyCanTransition($rku, 'reject', $user)) {
            throw new \RuntimeException('RKU tidak dapat ditolak.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $rku->status_rku;
            $rku->update([
                'status_rku' => RkuHeader::STATUS_DITOLAK,
                'id_approver' => $user->pegawai?->id,
                'tanggal_approval' => now(),
                'approved_at' => now(),
                'catatan_approval' => $notes,
                'notes' => $notes,
            ]);
            $this->createApprovalHistory($rku, $oldStatus, RkuHeader::STATUS_DITOLAK, $user, $notes, false);
            RkuAuditLog::log($rku->id_rku, RkuAuditLog::ACTION_REJECTED, ['status_rku' => $oldStatus], ['status_rku' => RkuHeader::STATUS_DITOLAK], ['status_rku']);
            $rku->createVersionSnapshot('Rejected');
            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function legacyStartReview(RkuHeader $rku, ?User $user = null): RkuHeader
    {
        $user = $user ?? auth()->user();
        if (! $this->legacyCanTransition($rku, 'start_review', $user)) {
            throw new \RuntimeException('RKU tidak dapat dimulai reviewnya.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $rku->status_rku;
            $rku->update(['status_rku' => RkuHeader::STATUS_DIPROSES]);
            $this->createApprovalHistory($rku, $oldStatus, RkuHeader::STATUS_DIPROSES, $user, 'Started review process');
            RkuAuditLog::log($rku->id_rku, RkuAuditLog::ACTION_UPDATED, ['status_rku' => $oldStatus], ['status_rku' => RkuHeader::STATUS_DIPROSES], ['status_rku']);
            $rku->createVersionSnapshot('Started review');
            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function legacyCancel(RkuHeader $rku, ?string $notes = null, ?User $user = null): RkuHeader
    {
        $user = $user ?? auth()->user();
        if (! $this->legacyCanTransition($rku, 'cancel', $user)) {
            throw new \RuntimeException('RKU tidak dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $rku->status_rku;
            $rku->update(['status_rku' => RkuHeader::STATUS_DRAFT, 'submitted_at' => null, 'notes' => $notes]);
            $this->createApprovalHistory($rku, $oldStatus, RkuHeader::STATUS_DRAFT, $user, $notes ?? 'Cancelled');
            RkuAuditLog::log($rku->id_rku, RkuAuditLog::ACTION_CANCELLED, ['status_rku' => $oldStatus], ['status_rku' => RkuHeader::STATUS_DRAFT], ['status_rku']);
            $rku->createVersionSnapshot('Cancelled');
            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function legacyRevise(RkuHeader $rku, ?User $user = null): RkuHeader
    {
        $user = $user ?? auth()->user();
        if (! $this->legacyCanTransition($rku, 'revise', $user)) {
            throw new \RuntimeException('RKU tidak dapat direvisi.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $rku->status_rku;
            $rku->update([
                'status_rku' => RkuHeader::STATUS_DRAFT,
                'id_approver' => null,
                'tanggal_approval' => null,
                'approved_at' => null,
                'catatan_approval' => null,
            ]);
            $this->createApprovalHistory($rku, $oldStatus, RkuHeader::STATUS_DRAFT, $user, 'Revised');
            RkuAuditLog::log($rku->id_rku, RkuAuditLog::ACTION_UPDATED, ['status_rku' => $oldStatus], ['status_rku' => RkuHeader::STATUS_DRAFT], ['status_rku']);
            $rku->createVersionSnapshot('Revised');
            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function legacyCanTransition(RkuHeader $rku, string $transition, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        $transitionMap = [
            'submit' => ['from' => RkuHeader::STATUS_DRAFT, 'to' => RkuHeader::STATUS_DIAJUKAN],
            'cancel' => ['from' => RkuHeader::STATUS_DIAJUKAN, 'to' => RkuHeader::STATUS_DRAFT],
            'start_review' => ['from' => RkuHeader::STATUS_DIAJUKAN, 'to' => RkuHeader::STATUS_DIPROSES],
            'approve' => ['from' => RkuHeader::STATUS_DIPROSES, 'to' => RkuHeader::STATUS_DISETUJUI],
            'reject' => ['from' => RkuHeader::STATUS_DIPROSES, 'to' => RkuHeader::STATUS_DITOLAK],
            'revise' => ['from' => RkuHeader::STATUS_DITOLAK, 'to' => RkuHeader::STATUS_DRAFT],
        ];

        if (! isset($transitionMap[$transition])) {
            return false;
        }

        $map = $transitionMap[$transition];
        if ($rku->status_rku !== $map['from']) {
            return false;
        }

        $permissionMap = [
            'submit' => 'planning.rku.submit',
            'cancel' => 'planning.rku.cancel',
            'start_review' => 'planning.rku.approve',
            'approve' => 'planning.rku.approve',
            'reject' => 'planning.rku.reject',
            'revise' => 'planning.rku.revise',
        ];

        return $user->hasPermission($permissionMap[$transition] ?? 'planning.rku.manage');
    }

    protected function legacyGetAvailableTransitions(RkuHeader $rku, ?User $user = null): array
    {
        $transitions = [];
        foreach (['submit', 'cancel', 'start_review', 'approve', 'reject', 'revise'] as $name) {
            $transitions[$name] = $this->legacyCanTransition($rku, $name, $user);
        }

        return $transitions;
    }

    protected function createApprovalHistory(
        RkuHeader $rku,
        string $fromStatus,
        string $toStatus,
        ?User $user,
        ?string $notes = null,
        bool $isApproved = true,
    ): void {
        RkuApprovalHistory::create([
            'id_rku' => $rku->id_rku,
            'approver_id' => $user?->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'is_approved' => $isApproved,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
