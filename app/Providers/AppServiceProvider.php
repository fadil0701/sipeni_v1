<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use App\Models\DataInventory;
use App\Models\PermintaanBarang;
use App\Observers\DataInventoryObserver;
use App\Observers\PermintaanBarangObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($rootUrl = config('app.url')) {
            URL::forceRootUrl($rootUrl);
        }

        // Register observers
        DataInventory::observe(DataInventoryObserver::class);
        PermintaanBarang::observe(PermintaanBarangObserver::class);
        
        // Share variabel akses user dan role ke semua view (konsisten di seluruh app)
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                // Eager load roles dan modules untuk menghindari N+1 dan memastikan menu/permission akurat
                if (!$user->relationLoaded('roles')) {
                    $user->load('roles');
                }
                if (!$user->relationLoaded('modules')) {
                    $user->load('modules');
                }
                $view->with('currentUser', $user);
                $view->with('accessibleMenus', PermissionHelper::getAccessibleMenus($user));
                $view->with('userRoles', $user->roles->pluck('name')->toArray());
                $view->with('userRoleIds', $user->roles->pluck('id')->toArray());
                $view->with('userPrimaryRole', $user->roles->first());
            } else {
                $view->with('currentUser', null);
                $view->with('accessibleMenus', []);
                $view->with('userRoles', []);
                $view->with('userRoleIds', []);
                $view->with('userPrimaryRole', null);
            }
        });
    }
}
