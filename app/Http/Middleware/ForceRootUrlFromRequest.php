<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saat akses lewat IP LAN (tanpa mengubah APP_URL di .env), paksa URL generator
 * memakai host dari request aktual agar redirect/asset tidak mengarah ke localhost.
 */
class ForceRootUrlFromRequest
{
    public function handle($request, Closure $next): Response
    {
        if (\call_user_func('\\app')->environment('local')) {
            \call_user_func('\\url')->forceRootUrl($request->getSchemeAndHttpHost());
        }

        return $next($request);
    }
}
