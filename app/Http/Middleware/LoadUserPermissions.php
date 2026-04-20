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
            
            // Pastikan roles ter-load
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }
            
            // Pastikan permissions ter-load untuk setiap role
            foreach ($user->roles as $role) {
                if (!$role->relationLoaded('permissions')) {
                    $role->load('permissions');
                }
            }
        }

        return $next($request);
    }
}


