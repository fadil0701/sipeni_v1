<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Services\ModuleRegistry;
use App\Support\Audit\AuditDataSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public const MODULE_USER_MANAGEMENT = 'manajemen-user-role';

    public const MODULE_MASTER_DATA = 'master-data';

    public const MODULE_AUDIT_TRAIL = 'audit-trail';

    public const MODULE_PROCUREMENT_RKU = 'pengadaan-rku';

    /**
     * @param  array<string, mixed>|Model|null  $old
     * @param  array<string, mixed>|Model|null  $new
     * @param  array<string, mixed>  $metadata
     */
    public static function log(
        string $module,
        string $action,
        ?string $description = null,
        array|Model|null $old = null,
        array|Model|null $new = null,
        ?object $entity = null,
        ?string $entityType = null,
        int|string|null $entityId = null,
        array $metadata = [],
    ): ActivityLog {
        self::assertModuleKey($module);

        [$resolvedType, $resolvedId] = self::resolveEntity($entity, $entityType, $entityId);

        $request = self::safeRequest();

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'module_key' => $module,
            'action' => $action,
            'entity_type' => $resolvedType,
            'entity_id' => $resolvedId,
            'description' => $description,
            'old_values' => AuditDataSanitizer::sanitizeAuditData($old),
            'new_values' => AuditDataSanitizer::sanitizeAuditData($new),
            'metadata' => $metadata === [] ? null : AuditDataSanitizer::sanitizeArray($metadata),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? (string) $request->userAgent() : null,
            'request_url' => $request ? (string) $request->fullUrl() : null,
            'method' => $request ? strtoupper($request->method()) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>|Model  $attributes
     */
    public static function logCreate(
        string $module,
        object $entity,
        array|Model $attributes,
        string $action = 'created',
        ?string $description = null,
        array $metadata = [],
    ): ActivityLog {
        return self::log(
            module: $module,
            action: $action,
            description: $description ?? 'Record created',
            old: null,
            new: $attributes,
            entity: $entity,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>|Model  $old
     * @param  array<string, mixed>|Model  $new
     */
    public static function logUpdate(
        string $module,
        object $entity,
        array|Model $old,
        array|Model $new,
        string $action = 'updated',
        ?string $description = null,
        array $metadata = [],
    ): ActivityLog {
        $oldArray = $old instanceof Model ? $old->attributesToArray() : $old;
        $newArray = $new instanceof Model ? $new->attributesToArray() : $new;
        [$oldChanged, $newChanged] = AuditDataSanitizer::diffChanged($oldArray, $newArray);

        return self::log(
            module: $module,
            action: $action,
            description: $description ?? 'Record updated',
            old: $oldChanged === [] ? null : $oldChanged,
            new: $newChanged === [] ? null : $newChanged,
            entity: $entity,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>|Model|null  $snapshot
     */
    public static function logDelete(
        string $module,
        object $entity,
        array|Model|null $snapshot = null,
        string $action = 'deleted',
        ?string $description = null,
        array $metadata = [],
    ): ActivityLog {
        return self::log(
            module: $module,
            action: $action,
            description: $description ?? 'Record deleted',
            old: $snapshot ?? (method_exists($entity, 'attributesToArray') ? $entity->attributesToArray() : null),
            new: null,
            entity: $entity,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>|Model|null  $old
     * @param  array<string, mixed>|Model|null  $new
     * @param  array<string, mixed>  $metadata
     */
    public static function logAction(
        string $module,
        string $action,
        ?string $description = null,
        array|Model|null $old = null,
        array|Model|null $new = null,
        ?object $entity = null,
        array $metadata = [],
    ): ActivityLog {
        return self::log(
            module: $module,
            action: $action,
            description: $description,
            old: $old,
            new: $new,
            entity: $entity,
            metadata: $metadata,
        );
    }

    public static function assertModuleKey(string $module): void
    {
        $registry = app(ModuleRegistry::class);
        if (! $registry->hasModule($module) && config('app.debug')) {
            logger()->warning('Audit module_key not registered in module_permissions', [
                'module_key' => $module,
            ]);
        }
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    private static function resolveEntity(?object $entity, ?string $entityType, int|string|null $entityId): array
    {
        if ($entity !== null) {
            return [$entity::class, (int) $entity->getKey()];
        }

        if ($entityType !== null) {
            return [$entityType, $entityId === null ? null : (int) $entityId];
        }

        return [null, null];
    }

    private static function safeRequest(): ?Request
    {
        if (! app()->runningInConsole() && app()->bound('request')) {
            $request = request();
            if ($request instanceof Request) {
                return $request;
            }
        }

        return null;
    }
}
