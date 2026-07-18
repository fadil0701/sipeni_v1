<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsurePrintTemplatesFeatureEnabled;
use App\Http\Middleware\EnsureUnitScope;
use App\Http\Middleware\EnsureWorkflowAccess;
use App\Http\Middleware\AuditRequestActivity;
use App\Http\Middleware\LoadUserPermissions;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: null,
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            $prefix = (string) config('path.route_prefix', '');
            $webRoutes = base_path('routes/web.php');

            if ($prefix !== '') {
                Route::middleware('web')->prefix($prefix)->group($webRoutes);
            } else {
                Route::middleware('web')->group($webRoutes);
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $proxies = env('TRUSTED_PROXIES');
        if ($proxies !== null && $proxies !== '') {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        } elseif (env('APP_ENV', 'local') !== 'production') {
            $middleware->trustProxies(at: '*');
        }
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->alias([
            'role' => CheckRole::class,
            'permission' => EnsurePermission::class,
            'permission.any' => \App\Http\Middleware\EnsureAnyPermission::class,
            'scope.unit' => EnsureUnitScope::class,
            'workflow.access' => EnsureWorkflowAccess::class,
            'feature.print-templates' => EnsurePrintTemplatesFeatureEnabled::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->web(append: [
            LoadUserPermissions::class,
            AuditRequestActivity::class,
        ]);
    })
    ->withProviders([
        AppServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
