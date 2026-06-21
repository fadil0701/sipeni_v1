<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditLogService;
use App\Support\Audit\AuditDataSanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditRequestActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user()) {
            return $response;
        }

        $method = strtoupper($request->getMethod());
        $routeName = (string) optional($request->route())->getName();
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $action = $this->resolveAction($method, $routeName);

        AuditLogService::logAction(
            module: $this->resolveModuleKey($routeName),
            action: $action,
            description: "HTTP {$method} {$routeName}",
            metadata: AuditDataSanitizer::sanitizeArray([
                'route' => $routeName,
                'status' => $response->getStatusCode(),
                'payload' => $request->except(['password', 'password_confirmation', '_token']),
            ]),
        );

        return $response;
    }

    private function resolveAction(string $method, string $routeName): string
    {
        if (str_contains($routeName, '.approve')) {
            return 'approve';
        }
        if (str_contains($routeName, '.reject')) {
            return 'reject';
        }
        if ($method === 'DELETE') {
            return 'deleted';
        }
        if (in_array($method, ['PUT', 'PATCH'], true)) {
            return 'updated';
        }

        return 'created';
    }

    private function resolveModuleKey(string $routeName): string
    {
        if (str_starts_with($routeName, 'admin.')) {
            return AuditLogService::MODULE_USER_MANAGEMENT;
        }
        if (str_starts_with($routeName, 'master-manajemen.') || str_starts_with($routeName, 'master-data.')) {
            return AuditLogService::MODULE_MASTER_DATA;
        }
        if (str_starts_with($routeName, 'planning.') || str_starts_with($routeName, 'procurement.')) {
            return 'pengadaan-rku';
        }
        if (str_starts_with($routeName, 'finance.')) {
            return 'pengadaan-rku';
        }
        if (str_starts_with($routeName, 'maintenance.')) {
            return 'pemeliharaan-aset';
        }
        if (str_starts_with($routeName, 'inventory.') || str_starts_with($routeName, 'asset.')) {
            return 'aset-tetap-kir';
        }
        if (str_starts_with($routeName, 'transaction.')) {
            return 'distribusi';
        }

        return AuditLogService::MODULE_AUDIT_TRAIL;
    }
}
