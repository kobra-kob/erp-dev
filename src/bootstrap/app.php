<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Derrière un reverse-proxy qui termine le TLS (ex. proxy + certificat
        // auto-signé) : faire confiance aux en-têtes X-Forwarded-* pour que
        // Laravel détecte bien le HTTPS d'origine (sinon cookies/CSRF cassés
        // au login → erreur 419). Restreindre `at` à l'IP du proxy si connue.
        $middleware->trustProxies(at: '*', headers:
            Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role'   => EnsureUserHasRole::class,
            'module' => CheckModuleAccess::class,
            'sector' => EnsureModuleActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
