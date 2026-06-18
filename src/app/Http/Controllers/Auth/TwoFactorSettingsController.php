<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

/**
 * Activation / désactivation de la double authentification (TOTP)
 * depuis l'espace « Paramètres ».
 */
class TwoFactorSettingsController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('settings.index')
                ->with('status', 'La double authentification est déjà active.');
        }

        $google2fa = new Google2FA;

        // Génère (et mémorise) un secret tant qu'il n'est pas confirmé.
        if (! $user->two_factor_secret) {
            $user->two_factor_secret = Crypt::encryptString($google2fa->generateSecretKey());
            $user->save();
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        $otpauthUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('settings.two-factor', compact('secret', 'otpauthUrl'));
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user   = $request->user();
        $secret = Crypt::decryptString($user->two_factor_secret);

        $valid = (new Google2FA)->verifyKey(
            $secret,
            preg_replace('/\s+/', '', $request->input('code'))
        );

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide. Vérifiez l\'heure de votre téléphone et réessayez.',
            ]);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();
        LoginAudit::log('2fa_enabled', $user);

        return redirect()->route('settings.index')
            ->with('status', 'Double authentification activée avec succès.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        LoginAudit::log('2fa_disabled', $user);

        return redirect()->route('settings.index')
            ->with('status', 'Double authentification désactivée.');
    }
}
