<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'accès si le module optionnel (vertical) n'est pas activé pour
 * l'entreprise de l'utilisateur.
 *
 * Usage : ->middleware('sector:batiment')
 */
class EnsureModuleActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $company = $request->user()?->company;

        if (! $company || ! $company->hasModule($module)) {
            abort(403, "Ce module n'est pas activé pour votre entreprise.");
        }

        return $next($request);
    }
}
