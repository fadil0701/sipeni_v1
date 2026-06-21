<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnyPermission
{
    /**
     * Izinkan akses jika user punya salah satu permission (route name) yang disebutkan.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (PermissionHelper::canAccess($user, $permission)) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak.');
    }
}
