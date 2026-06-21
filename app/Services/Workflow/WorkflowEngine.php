<?php

namespace App\Services\Workflow;

use App\Helpers\PermissionHelper;
use App\Models\User;
use App\Models\Workflow\WorkflowDefinition;
use App\Models\Workflow\WorkflowHistory;
use App\Models\Workflow\WorkflowStep;
use App\Models\Workflow\WorkflowTransition;
use App\Services\Audit\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkflowEngine
{
    public const MODULE_PROCUREMENT_RKU = 'pengadaan-rku';

    /** @var array<string, string> */
    private const RKU_STATUS_ALIASES = [
        'DIPROSES' => 'REVIEW_KASUBAG_TU',
    ];

    /** @var callable|null */
    private static $afterTransitionHook;

    public function __construct(
        private readonly string $moduleKey = self::MODULE_PROCUREMENT_RKU,
    ) {}

    public static function afterTransition(?callable $hook): void
    {
        self::$afterTransitionHook = $hook;
    }

    public function definition(): ?WorkflowDefinition
    {
        return WorkflowDefinition::query()
            ->where('module_key', $this->moduleKey)
            ->where('is_active', true)
            ->first();
    }

    public function isEnabled(): bool
    {
        return $this->definition() !== null;
    }

    public function getCurrentStep(Model $entity): ?WorkflowStep
    {
        $definition = $this->definition();
        if (! $definition) {
            return null;
        }

        $status = $this->resolveEntityStatus($entity);

        return WorkflowStep::query()
            ->where('workflow_definition_id', $definition->id)
            ->where(function ($q) use ($status): void {
                $q->where('entity_status', $status)
                    ->orWhere('code', $status);
            })
            ->first();
    }

    /**
     * @return Collection<int, WorkflowTransition>
     */
    public function getAvailableTransitions(Model $entity, ?User $user = null): Collection
    {
        $user = $user ?? Auth::user();
        $current = $this->getCurrentStep($entity);
        if (! $current || ! $user) {
            return collect();
        }

        return WorkflowTransition::query()
            ->with(['toStep'])
            ->where('workflow_definition_id', $current->workflow_definition_id)
            ->where('from_step_id', $current->id)
            ->get()
            ->filter(fn (WorkflowTransition $t) => $this->canTransition($entity, $t->action, $user));
    }

    public function canTransition(Model $entity, string $action, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (! $user) {
            return false;
        }

        $transition = $this->findTransition($entity, $action);
        if (! $transition) {
            return false;
        }

        return $this->userMayRunTransition($user, $transition);
    }

    public function transition(
        Model $entity,
        string $action,
        ?string $comment = null,
        ?User $user = null,
        array $metadata = [],
    ): Model {
        $user = $user ?? Auth::user();
        $transition = $this->findTransition($entity, $action);

        if (! $transition) {
            throw new \RuntimeException("Transisi workflow '{$action}' tidak valid untuk status saat ini.");
        }

        if (! $user || ! $this->userMayRunTransition($user, $transition)) {
            throw new \RuntimeException('Anda tidak memiliki wewenang untuk aksi workflow ini.');
        }

        $fromStep = $this->getCurrentStep($entity);
        $toStep = $transition->toStep;
        $oldStatus = $this->resolveEntityStatus($entity);

        DB::beginTransaction();

        try {
            $this->applyEntityStatus($entity, (string) $toStep->entity_status);
            $this->applyRkuSideEffects($entity, $action, $user, $comment, $oldStatus);

            WorkflowHistory::create([
                'module_key' => $this->moduleKey,
                'entity_type' => $entity::class,
                'entity_id' => (int) $entity->getKey(),
                'workflow_step_id' => $toStep->id,
                'action' => $action,
                'user_id' => $user->id,
                'comment' => $comment,
                'metadata' => array_merge($metadata, [
                    'from_step' => $fromStep?->code,
                    'to_step' => $toStep->code,
                    'from_status' => $oldStatus,
                    'to_status' => $toStep->entity_status,
                ]),
                'created_at' => now(),
            ]);

            $this->writeActivityLog($entity, $action, $oldStatus, (string) $toStep->entity_status, $comment);

            if (self::$afterTransitionHook) {
                (self::$afterTransitionHook)($entity, $action, $user, $fromStep, $toStep);
            }

            DB::commit();

            return $entity->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @return Collection<int, WorkflowHistory>
     */
    public function getHistory(Model $entity): Collection
    {
        return WorkflowHistory::query()
            ->with(['user', 'step'])
            ->where('module_key', $this->moduleKey)
            ->where('entity_type', $entity::class)
            ->where('entity_id', $entity->getKey())
            ->orderByDesc('created_at')
            ->get();
    }

    protected function findTransition(Model $entity, string $action): ?WorkflowTransition
    {
        $current = $this->getCurrentStep($entity);
        if (! $current) {
            return null;
        }

        return WorkflowTransition::query()
            ->with(['toStep', 'fromStep'])
            ->where('workflow_definition_id', $current->workflow_definition_id)
            ->where('from_step_id', $current->id)
            ->where('action', $action)
            ->first();
    }

    protected function userMayRunTransition(User $user, WorkflowTransition $transition): bool
    {
        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return true;
        }

        $permission = $transition->permission_name
            ?? $transition->fromStep?->permission_name;

        if ($permission && PermissionHelper::canAccess($user, $permission)) {
            return true;
        }

        $roleName = $transition->toStep?->role_name;
        if ($roleName && method_exists($user, 'hasRole') && $user->hasRole($roleName)) {
            return true;
        }

        return false;
    }

    protected function resolveEntityStatus(Model $entity): string
    {
        $status = (string) ($entity->status_rku ?? $entity->getAttribute('status') ?? '');

        return self::RKU_STATUS_ALIASES[$status] ?? $status;
    }

    protected function applyEntityStatus(Model $entity, string $entityStatus): void
    {
        if (! isset($entity->status_rku)) {
            return;
        }

        $entity->status_rku = $entityStatus;
        $entity->save();
    }

    protected function applyRkuSideEffects(Model $entity, string $action, User $user, ?string $comment, string $fromStatus): void
    {
        if (! $entity instanceof \App\Models\RkuHeader) {
            return;
        }
        $updates = ['updated_by' => $user->id];

        if ($action === 'submit') {
            $updates['submitted_at'] = now();
        } elseif (in_array($action, ['approve', 'forward'], true)) {
            $updates['id_approver'] = $user->pegawai?->id;
            $updates['tanggal_approval'] = now();
            $updates['approved_at'] = now();
            $updates['catatan_approval'] = $comment;
            $updates['notes'] = $comment;
        } elseif ($action === 'reject') {
            $updates['id_approver'] = $user->pegawai?->id;
            $updates['tanggal_approval'] = now();
            $updates['approved_at'] = now();
            $updates['catatan_approval'] = $comment;
            $updates['notes'] = $comment;
        } elseif (in_array($action, ['revise', 'cancel'], true)) {
            $updates['submitted_at'] = null;
            $updates['id_approver'] = null;
            $updates['tanggal_approval'] = null;
            $updates['approved_at'] = null;
        }

        $entity->update($updates);

        if (method_exists($entity, 'createVersionSnapshot')) {
            $entity->createVersionSnapshot('Workflow: '.$action);
        }

        if (class_exists(\App\Models\RkuApprovalHistory::class)) {
            \App\Models\RkuApprovalHistory::create([
                'id_rku' => $entity->id_rku,
                'approver_id' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $entity->status_rku,
                'notes' => $comment,
                'is_approved' => in_array($action, ['approve', 'forward', 'review'], true),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        }
    }

    protected function writeActivityLog(Model $entity, string $action, string $from, string $to, ?string $comment): void
    {
        $auditAction = match ($action) {
            'submit' => 'rku_submitted',
            'approve', 'forward' => 'rku_approved',
            'reject' => 'rku_rejected',
            'request_revision' => 'rku_revision_requested',
            'revise' => 'rku_revised',
            'review' => 'rku_reviewed',
            'cancel' => 'rku_cancelled',
            default => 'rku_workflow_'.$action,
        };

        AuditLogService::logAction(
            module: \App\Services\Audit\AuditLogService::MODULE_PROCUREMENT_RKU,
            action: $auditAction,
            description: 'RKU workflow transition: '.$action,
            entity: $entity,
            old: ['status_rku' => $from],
            new: ['status_rku' => $to],
            metadata: ['comment' => $comment],
        );
    }
}
