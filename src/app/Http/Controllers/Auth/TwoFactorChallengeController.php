<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

/**
 * Étape 2 de la connexion lorsque la double authentification est active.
 */
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.2fa.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('login.2fa.id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $valid = (new Google2FA)->verifyKey(
            Crypt::decryptString($user->two_factor_secret),
            preg_replace('/\s+/', '', $request->input('code'))
        );

        if (! $valid) {
            LoginAudit::log('2fa_failed', $user);

            throw ValidationException::withMessages([
                'code' => 'Le code de vérification est invalide.',
            ]);
        }

        $remember = (bool) $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.id');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        LoginAudit::log('login', $user);

        return redirect()->intended(route('dashboard'));
    }
}
