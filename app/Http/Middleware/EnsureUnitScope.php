<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use App\Services\ScopeAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUnitScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $bypassScope = (bool) config('sipeni.auth.superadmin_bypass_scope', true);
        if ($bypassScope && PermissionHelper::hasEnterpriseBypassRole($user)) {
            $request->attributes->set('access.level', 'pusat');
            $request->attributes->set('access.unit_kerja_id', null);
            return $next($request);
        }

        $request->attributes->set('access.level', ScopeAccessService::isPusat($user) ? 'pusat' : 'unit');
        $request->attributes->set('access.unit_kerja_id', ScopeAccessService::userUnitKerjaId($user));

        return $next($request);
    }
}
