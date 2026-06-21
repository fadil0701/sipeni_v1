<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoadUserPermissions
{
    /**
     * Handle an incoming request.
     * Memastikan roles dan permissions ter-load untuk user yang login
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->loadMissing('roles.permissions');
        }

        return $next($request);
    }
}


