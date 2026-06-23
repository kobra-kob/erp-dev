<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Met à jour `last_seen_at` de l'utilisateur tenant connecté afin que la console
 * de support sache quels tenants sont actuellement actifs (« connectés »).
 *
 * Écriture limitée (au plus une fois par minute par utilisateur) pour ne pas
 * solliciter la base à chaque requête.
 */
class TrackUserPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user && (is_null($user->last_seen_at) || $user->last_seen_at->lt(now()->subMinute()))) {
            // updateQuietly : pas d'événements ni de touch inutile.
            $user->forceFill(['last_seen_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}
