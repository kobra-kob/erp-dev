<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AccountWelcomeMail;
use App\Models\Company;
use App\Models\LoginAudit;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Inscription : crée une ENTREPRISE + son premier utilisateur ADMIN.
 * C'est le point d'entrée du modèle multi-tenant.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $company = Company::create(['name' => $validated['company_name']]);

            $user = User::create([
                'company_id' => $company->id,
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role'       => User::ROLE_ADMIN,
            ]);

            // L'auteur de l'inscription est l'unique propriétaire (owner) du tenant.
            $company->update(['owner_id' => $user->id]);

            return $user;
        });

        event(new Registered($user));
        Notifier::send($user->email, new AccountWelcomeMail($user, createdByAdmin: false));
        Auth::login($user);
        $request->session()->regenerate();
        LoginAudit::log('register', $user);

        // Premier paramétrage : choix des modules à activer.
        return redirect()->route('onboarding');
    }
}
