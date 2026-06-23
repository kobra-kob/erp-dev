<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Authentification de la console de support (guard « support »).
 */
class SupportAuthController extends Controller
{
    public function create(): View
    {
        return view('support.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('support')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants invalides.',
            ]);
        }

        $user = Auth::guard('support')->user();

        if (! $user->is_active) {
            Auth::guard('support')->logout();

            throw ValidationException::withMessages([
                'email' => 'Ce compte de support est désactivé.',
            ]);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        SupportAuditLog::record('support.login');

        return redirect()->intended(route('support.tenants.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        SupportAuditLog::record('support.logout');

        Auth::guard('support')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('support.login');
    }
}
