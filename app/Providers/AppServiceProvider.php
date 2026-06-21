<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use App\Models\DataInventory;
use App\Models\PermintaanBarang;
use App\Models\User;
use App\Observers\DataInventoryObserver;
use App\Observers\PermintaanBarangObserver;
use App\Support\SipeniPassword;
use App\Support\View\SharedLayoutData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;


class AppServiceProvider extends \Illuminate\Support\ServiceProvider
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
        Password::defaults(fn () => SipeniPassword::configureDefaults());

        if (! $this->app->runningInConsole() && $this->app->bound('request')) {
            $request = $this->app->make('request');

            if ($this->shouldUseRequestOrigin($request)) {
                $root = $request->getSchemeAndHttpHost();
                URL::forceRootUrl($root);
                URL::useAssetOrigin($root);
            } elseif (! $this->app->environment('local')) {
                $appUrl = config('app.url');
                if (is_string($appUrl) && $appUrl !== '') {
                    URL::forceRootUrl(rtrim($appUrl, '/'));
                    if (str_starts_with($appUrl, 'https://')) {
                        URL::forceScheme('https');
                    }
                }
            }
        }

        Gate::before(function ($user, string $ability = null): ?bool {
            if ($user instanceof User && PermissionHelper::hasEnterpriseBypassRole($user)) {
                return true;
            }

            return null;
        });

        Gate::define('permission', function (User $user, string $permission): bool {
            return PermissionHelper::canAccess($user, $permission);
        });

        Blade::if('canAccess', function (string $permission): bool {
            $user = auth()->user();
            if (! $user instanceof User) {
                return false;
            }

            return PermissionHelper::canAccess($user, $permission);
        });

        // Register observers
        \call_user_func([DataInventory::class, 'observe'], DataInventoryObserver::class);
        \call_user_func([PermintaanBarang::class, 'observe'], PermintaanBarangObserver::class);
        
        // Share variabel layout ke semua view — dihitung sekali per request (bukan per partial).
        \call_user_func('\\view')->composer('*', function ($view) {
            $view->with(SharedLayoutData::resolve(
                \call_user_func('\\auth')->check() ? \call_user_func('\\auth')->user() : null
            ));
        });
    }

    private function shouldUseRequestOrigin(Request $request): bool
    {
        if (config('app.use_request_url')) {
            return $request->getHost() !== '';
        }

        return (int) $request->getPort() === (int) config('app.port', 7001);
    }
}
