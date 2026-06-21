<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return $next($request);
        }

        if (! $user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        }

        $route = $request->route();
        if ($route) {
            $routeName = $route->getName();
            if ($routeName && PermissionHelper::canAccess($user, $routeName)) {
                return $next($request);
            }
        }

        if (! empty($roles) && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak.');
    }
}
