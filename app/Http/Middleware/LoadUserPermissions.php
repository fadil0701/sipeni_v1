<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoadUserPermissions
{
    /**
     * Handle an incoming request.
     * Load roles saja (tanpa hydrate permissions Eloquent).
     * Nama permission di-cache sebagai string via PermissionHelper.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Jangan loadMissing('roles.permissions') — itu memuat ratusan model Permission.
            $user->loadMissing(['roles:id,name,guard_name']);
            PermissionHelper::warmPermissionCache($user);
        }

        return $next($request);
    }
}
