<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesLineItems;
use App\Mail\QuoteMail;
use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Services\QuoteConverter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteController extends Controller
{
    use ManagesLineItems;

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $quotes = Quote::with('client')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('quotes.index', compact('quotes', 'status'));
    }

    public function create(): View
    {
        $quote = new Quote([
            'issue_date'  => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'status'      => 'draft',
        ]);

        return view('quotes.create', [
            'quote'    => $quote,
            'clients'  => Client::orderBy('name')->get(),
            'catalog'  => $this->catalogItems(),
            'products' => $this->sellableProducts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateQuote($request);

        if ($errors = $this->stockErrors($request->input('lines', []))) {
            return back()->withErrors($errors)->withInput();
        }

        $quote = DB::transaction(function () use ($data, $request) {
            $quote = Quote::create([
                'client_id'   => $data['client_id'],
                'number'      => Quote::nextNumber(Auth::user()->company_id),
                'status'      => 'draft',
                'title'       => $data['title'] ?? null,
                'issue_date'  => $data['issue_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->syncLines($quote, $request->input('lines', []));

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Devis créé.');
    }

    public function show(Quote $quote): View
    {
        $quote->load('lines', 'client');

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $quote->load('lines');

        return view('quotes.edit', [
            'quote'    => $quote,
            'clients'  => Client::orderBy('name')->get(),
            'catalog'  => $this->catalogItems(),
            'products' => $this->sellableProducts(),
        ]);
    }

    /** Prestations du catalogue (si le module Bâtiment est activé), sinon vide. */
    private function catalogItems()
    {
        return Auth::user()->company?->hasModule('batiment')
            ? CatalogItem::orderBy('trade')->orderBy('label')->get()
            : collect();
    }

    /** Produits du stock proposables comme lignes de devis. */
    private function sellableProducts()
    {
        return Product::sellable()->orderBy('category')->orderBy('name')->get();
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $data = $this->validateQuote($request);

        if ($errors = $this->stockErrors($request->input('lines', []))) {
            return back()->withErrors($errors)->withInput();
        }

        DB::transaction(function () use ($quote, $data, $request) {
            $quote->update([
                'client_id'   => $data['client_id'],
                'title'       => $data['title'] ?? null,
                'issue_date'  => $data['issue_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->syncLines($quote, $request->input('lines', []));
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Devis mis à jour.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('quotes.index')->with('status', 'Devis supprimé.');
    }

    /** Change le statut du devis ; un passage en « accepté » génère la facture. */
    public function updateStatus(Request $request, Quote $quote, QuoteConverter $converter): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Quote::STATUSES))],
        ]);

        $quote->update(['status' => $validated['status']]);

        if ($validated['status'] === 'accepted') {
            $invoice = $converter->convert($quote);

            return redirect()->route('invoices.show', $invoice)
                ->with('status', 'Devis accepté — facture ' . $invoice->number . ' générée automatiquement.');
        }

        return back()->with('status', 'Statut du devis : ' . $quote->statusLabel() . '.');
    }

    /** Transforme un devis accepté en facture (action manuelle). */
    public function convertToInvoice(Quote $quote, QuoteConverter $converter): RedirectResponse
    {
        if (! $quote->isAccepted()) {
            return back()->withErrors(['quote' => 'Seul un devis accepté peut être transformé en facture.']);
        }

        $invoice = $converter->convert($quote);

        return redirect()->route('invoices.show', $invoice)
            ->with('status', 'Facture ' . $invoice->number . ' créée depuis le devis.');
    }

    /** Envoie le devis par e-mail au client (PDF joint) et le passe en « envoyé ». */
    public function send(Quote $quote): RedirectResponse
    {
        $quote->load('lines', 'client', 'company');

        if (! $quote->client?->email) {
            return back()->withErrors(['quote' => 'Aucune adresse e-mail n\'est rattachée au client « '
                . $quote->client?->name . ' ». Ajoutez-la dans sa fiche client avant d\'envoyer le devis.']);
        }

        Mail::to($quote->client->email)->send(new QuoteMail($quote));

        if ($quote->status === 'draft') {
            $quote->update(['status' => 'sent']);
        }

        return back()->with('status', 'Devis envoyé à ' . $quote->client->email . '.');
    }

    /** Génère le PDF du devis. */
    public function pdf(Quote $quote)
    {
        $quote->load('lines', 'client', 'company');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.quote', ['quote' => $quote]);

        return $pdf->stream($quote->number . '.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateQuote(Request $request): array
    {
        return $request->validate(array_merge([
            'client_id'   => ['required', Rule::exists('clients', 'id')->where('company_id', Auth::user()->company_id)],
            'title'       => ['nullable', 'string', 'max:255'],
            'issue_date'  => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes'       => ['nullable', 'string'],
        ], $this->lineRules()));
    }
}
