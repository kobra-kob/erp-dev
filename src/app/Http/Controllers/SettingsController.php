<?php

namespace App\Http\Controllers;

use App\Models\LoginAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user    = Auth::user();
        $company = $user->company;

        $audits = LoginAudit::where('company_id', $user->company_id)
            ->latest()
            ->limit(20)
            ->get();

        return view('settings.index', compact('user', 'company', 'audits'));
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'siret'   => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:255'],
            'city'    => ['nullable', 'string', 'max:120'],
            'zip'     => ['nullable', 'string', 'max:10'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:255'],
        ]);

        $request->user()->company->update($data);

        return back()->with('status', 'Informations de l\'entreprise mises à jour.');
    }

    /** Logo + apparence des devis/factures (couleurs, forme). Réservé aux administrateurs. */
    public function updateBranding(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'logo'           => ['nullable', 'image', 'max:2048'],
            'brand_color'    => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_accent'   => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'document_shape' => ['required', Rule::in(array_keys(\App\Models\Company::SHAPES))],
        ]);

        $company = $request->user()->company;

        $update = [
            'brand_color'    => $data['brand_color'],
            'brand_accent'   => $data['brand_accent'],
            'document_shape' => $data['document_shape'],
        ];

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $update['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($update);

        return back()->with('status', 'Apparence des documents mise à jour.');
    }

    /** Envoie un lien de réinitialisation du mot de passe à l'utilisateur connecté. */
    public function sendPasswordReset(Request $request): RedirectResponse
    {
        Password::sendResetLink(['email' => $request->user()->email]);

        // Message neutre (on ne révèle pas l'état du compte).
        return back()->with('status', 'Un lien de changement de mot de passe vient de vous être envoyé par e-mail.');
    }
}
