<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'accès à une route selon le module concerné, en fonction des
 * droits du rôle de l'utilisateur (intégré ou personnalisé).
 *
 * Usage : ->middleware('module:clients')
 */
class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessModule($module)) {
            abort(403, "Votre rôle n'a pas accès à ce module.");
        }

        return $next($request);
    }
}
