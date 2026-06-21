<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkflowAccess
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $ability, string $statusCodeParam = 'status'): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : '';

        $modulePermission = $this->resolveWorkflowPermission($routeName, $ability);

        if ($modulePermission && PermissionHelper::canAccess($user, $modulePermission)) {
            return $next($request);
        }

        $generalPermission = 'approval.'.$ability;
        if (PermissionHelper::canAccess($user, $generalPermission)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Resolve workflow permission dari route name dan ability.
     */
    private function resolveWorkflowPermission(string $routeName, string $ability): ?string
    {
        $mapping = [
            'transaction.permintaan-barang.' => 'permintaan.barang.',
            'transaction.peminjaman-barang.' => 'peminjaman.barang.',
            'transaction.approval.' => 'approval.',
            'transaction.distribusi.' => 'distribusi.',
            'transaction.penerimaan-barang.' => 'distribusi.',
            'transaction.retur-barang.' => 'distribusi.',
            'planning.rku.' => 'rku.',
            'asset.register-aset.' => 'aset.register.',
            'asset.mutasi-aset.' => 'aset.mutasi.',
            'procurement.' => 'pengadaan.',
            'finance.' => 'keuangan.',
        ];

        foreach ($mapping as $prefix => $permPrefix) {
            if (str_starts_with($routeName, $prefix)) {
                return $permPrefix.$ability;
            }
        }

        return null;
    }
}
