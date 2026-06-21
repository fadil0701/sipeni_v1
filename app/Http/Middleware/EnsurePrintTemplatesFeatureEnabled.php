<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menonaktifkan rute admin template cetak dan cetak SBBK berbasis template
 * bila fitur dimatikan di konfigurasi (KIR / alur lain tidak memakai ini).
 */
class EnsurePrintTemplatesFeatureEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('sipeni.feature_print_templates', false)) {
            abort(404);
        }

        return $next($request);
    }
}
