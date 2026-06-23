<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'accès à la console de support à une liste d'IP autorisées.
 *
 * Source : variable d'environnement SUPPORT_ALLOWED_IPS (IP séparées par des
 * virgules). Vide → aucune restriction (utile en développement local).
 * Supporte le joker « * » (ex. 192.168.1.* ) pour un préfixe simple.
 */
class SupportIpAllowlist
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = array_filter(array_map('trim', explode(',', (string) env('SUPPORT_ALLOWED_IPS', ''))));

        if (empty($allowed)) {
            return $next($request);
        }

        $ip = (string) $request->ip();

        foreach ($allowed as $rule) {
            if ($rule === $ip) {
                return $next($request);
            }

            // Joker de préfixe : « 10.0.0.* » autorise tout 10.0.0.x
            if (str_ends_with($rule, '*')) {
                $prefix = rtrim($rule, '*');
                if (str_starts_with($ip, $prefix)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Accès à la console de support non autorisé depuis cette adresse.');
    }
}
