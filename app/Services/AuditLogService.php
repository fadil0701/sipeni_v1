<?php

namespace App\Services;

use App\Services\Audit\AuditLogService as ActivityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Backward-compatible facade for legacy audit_logs writers.
 * New code should use App\Services\Audit\AuditLogService (activity_logs).
 */
class AuditLogService
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public static function log(string $action, ?Request $request = null, ?string $tableName = null, int|string|null $dataId = null, ?array $before = null, ?array $after = null): void
    {
        $request ??= request();

        ActivityAuditService::log(
            module: ActivityAuditService::MODULE_AUDIT_TRAIL,
            action: $action,
            description: $tableName ? "{$action} on {$tableName}" : $action,
            old: $before,
            new: $after,
            entity: null,
            entityType: $tableName,
            entityId: $dataId,
            metadata: [
                'legacy_audit_logs' => true,
            ],
        );

        if (Schema::hasTable('audit_logs')) {
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'table_name' => $tableName,
                'data_id' => $dataId === null ? null : (int) $dataId,
                'before_data' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
                'after_data' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => (string) $request?->userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}
