<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use Mythos\Core\Identity\Http\Middleware\EnsureUserIsAdmin;
use Mythos\Core\Security\Http\Middleware\AddSecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(AddSecurityHeaders::class);

        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES'),
        );

        $middleware->trustHosts(
            at: fn (): array => array_filter(array_map(
                'trim',
                explode(',', (string) env('TRUSTED_HOSTS', parse_url((string) config('app.url'), PHP_URL_HOST))),
            )),
            subdomains: false,
        );

        $middleware->redirectGuestsTo(
            fn () => route('filament.admin.auth.login'),
        );

        // Alias des middlewares transverses (Admin, Feature Flags applicatifs).
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'feature' => EnsureFeaturesAreActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
