<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        $remember = $request->boolean('remember');

        // Vérifie les identifiants SANS ouvrir la session (pour gérer la 2FA d'abord).
        if (! Auth::validate($credentials)) {
            LoginAudit::log('failed', null, $credentials['email']);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte est désactivé. Contactez votre administrateur.',
            ]);
        }

        // Compte protégé par 2FA → on diffère la connexion vers le challenge TOTP.
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login.2fa.id', $user->id);
            $request->session()->put('login.2fa.remember', $remember);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        LoginAudit::log('login', $user);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        LoginAudit::log('logout', $user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
