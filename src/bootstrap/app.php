<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SupportIpAllowlist;
use App\Http\Middleware\TrackUserPresence;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Une seule image Docker, deux rôles : on ne charge que le jeu de
            // routes correspondant au conteneur (APP_ROLE). La console de support
            // n'existe donc tout simplement pas sur le conteneur applicatif, et
            // inversement.
            if (config('app.role') === 'support') {
                Route::middleware('web')->group(__DIR__.'/../routes/support.php');
            } else {
                Route::middleware('web')->group(__DIR__.'/../routes/web.php');
            }
        },
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
            TrackUserPresence::class,
        ]);

        $middleware->alias([
            'role'       => EnsureUserHasRole::class,
            'module'     => CheckModuleAccess::class,
            'sector'     => EnsureModuleActive::class,
            'support.ip' => SupportIpAllowlist::class,
        ]);

        // Sur le conteneur support, les routes tenant (dont « login ») ne sont pas
        // chargées : on aiguille les redirections d'auth vers la console.
        if (env('APP_ROLE') === 'support') {
            $middleware->redirectGuestsTo(fn () => route('support.login'));
            $middleware->redirectUsersTo(fn () => route('support.tenants.index'));
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
