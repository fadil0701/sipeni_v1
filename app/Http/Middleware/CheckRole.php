<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\PermissionHelper;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * Middleware ini sekarang menggunakan permission checking, bukan role checking.
     * Parameter roles masih digunakan untuk backward compatibility, tapi sebenarnya
     * permission akan diambil dari route name.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Admin selalu bisa akses semua
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Pastikan roles dan permissions ter-load
        if (!$user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        } else {
            foreach ($user->roles as $role) {
                if (!$role->relationLoaded('permissions')) {
                    $role->load('permissions');
                }
            }
        }

        // Cek permission berdasarkan route name
        $route = $request->route();
        if ($route) {
            $routeName = $route->getName();
            $permission = $this->getPermissionFromRoute($routeName);
            
            if ($permission) {
                // Gunakan permission checking
                if (PermissionHelper::canAccess($user, $permission)) {
                    return $next($request);
                } else {
                    // Permission check gagal, abort dengan pesan yang lebih jelas
                    abort(403, 'Anda tidak memiliki hak akses untuk: ' . $permission);
                }
            }
        }

        // Fallback ke role checking jika permission tidak ditemukan (backward compatibility)
        if (!empty($roles) && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'UNAUTHORIZED ACCESS. YOU DO NOT HAVE THE REQUIRED ROLE.');
    }

    /**
     * Konversi route name ke permission name
     * 
     * Contoh:
     * - transaction.permintaan-barang.index -> transaction.permintaan-barang.index
     * - transaction.permintaan-barang.create -> transaction.permintaan-barang.create
     * - transaction.approval.show -> transaction.approval.show
     */
    private function getPermissionFromRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        // Langsung return route name sebagai permission
        // Karena permission naming mengikuti route naming
        return $routeName;
    }
}
