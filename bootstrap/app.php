<?php

use App\Http\Middleware\CheckBusinessSubsription;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            TrustProxies::class
        ]);
        
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
        
        $middleware->alias([
            'check.business' => CheckBusinessSubsription::class
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'paddle/*',
        ]);
        
        $middleware->trustProxies(at: '*');
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
