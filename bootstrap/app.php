<?php

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
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LoadUserPermissions::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();