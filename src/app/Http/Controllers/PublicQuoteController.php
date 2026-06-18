<?php

namespace App\Http\Controllers;

use App\Mail\QuoteAnsweredMail;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteConverter;
use App\Support\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Circuit public de validation d'un devis par le client (sans authentification).
 * Accès via un jeton non devinable transmis dans l'e-mail du devis.
 */
class PublicQuoteController extends Controller
{
    public function show(string $token): View
    {
        $quote = $this->resolve($token);
        $quote->load('lines', 'client', 'company');

        return view('public.quote', compact('quote'));
    }

    public function accept(string $token, QuoteConverter $converter): RedirectResponse
    {
        $quote = $this->resolve($token);

        if (! $quote->awaitingClient()) {
            return redirect()->route('quotes.public', $token)
                ->with('status', 'Ce devis a déjà été traité (' . $quote->statusLabel() . ').');
        }

        $quote->update(['status' => 'accepted']);
        $invoice = $converter->convert($quote);          // facturation automatique
        $this->notifyCompany($quote, $invoice->number);

        return redirect()->route('quotes.public', $token)
            ->with('status', 'Merci ! Vous avez accepté le devis. Une facture a été générée.');
    }

    public function refuse(string $token): RedirectResponse
    {
        $quote = $this->resolve($token);

        if (! $quote->awaitingClient()) {
            return redirect()->route('quotes.public', $token)
                ->with('status', 'Ce devis a déjà été traité (' . $quote->statusLabel() . ').');
        }

        $quote->update(['status' => 'refused']);
        $this->notifyCompany($quote);

        return redirect()->route('quotes.public', $token)
            ->with('status', 'Vous avez refusé ce devis. Merci de votre retour.');
    }

    private function resolve(string $token): Quote
    {
        return Quote::withoutGlobalScopes()->where('public_token', $token)->firstOrFail();
    }

    private function notifyCompany(Quote $quote, ?string $invoiceNumber = null): void
    {
        $managers = User::where('company_id', $quote->company_id)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_GERANT])
            ->where('is_active', true)
            ->pluck('email')->all();

        Notifier::send($managers, new QuoteAnsweredMail($quote->loadMissing('client'), $invoiceNumber));
    }
}
