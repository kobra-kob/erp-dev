<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesLineItems;
use App\Mail\InvoiceMail;
use App\Mail\InvoiceReminderMail;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use ManagesLineItems;

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $invoices = Invoice::with('client')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'status'));
    }

    public function create(): View
    {
        $invoice = new Invoice([
            'issue_date' => now()->toDateString(),
            'due_date'   => now()->addDays(30)->toDateString(),
        ]);

        return view('invoices.create', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInvoice($request);

        $invoice = DB::transaction(function () use ($data, $request) {
            $invoice = Invoice::create([
                'client_id'  => $data['client_id'],
                'number'     => Invoice::nextNumber(Auth::user()->company_id),
                'status'     => 'unpaid',
                'title'      => $data['title'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date'   => $data['due_date'] ?? null,
                'notes'      => $data['notes'] ?? null,
            ]);

            $this->syncLines($invoice, $request->input('lines', []));

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Facture créée.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('lines', 'client', 'payments', 'quote');

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('lines');

        return view('invoices.edit', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $this->validateInvoice($request);

        DB::transaction(function () use ($invoice, $data, $request) {
            $invoice->update([
                'client_id'  => $data['client_id'],
                'title'      => $data['title'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date'   => $data['due_date'] ?? null,
                'notes'      => $data['notes'] ?? null,
            ]);

            $this->syncLines($invoice, $request->input('lines', []));
            $invoice->refreshPaymentStatus(); // le total a pu changer
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Facture mise à jour.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Facture supprimée.');
    }

    /** Envoie la facture par e-mail au client (PDF joint) et l'horodate « envoyée ». */
    public function send(Invoice $invoice): RedirectResponse
    {
        $invoice->load('lines', 'client', 'company');

        if (! $invoice->client?->email) {
            return back()->withErrors(['invoice' => 'Aucune adresse e-mail n\'est rattachée au client « '
                . $invoice->client?->name . ' ». Ajoutez-la dans sa fiche client avant d\'envoyer la facture.']);
        }

        Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));
        $invoice->update(['sent_at' => now()]);

        return back()->with('status', 'Facture envoyée à ' . $invoice->client->email . '.');
    }

    /** Envoie une relance manuelle au client. */
    public function remind(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return back()->withErrors(['invoice' => 'Cette facture est déjà réglée.']);
        }

        if ($invoice->client?->email) {
            Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));
            $invoice->markReminded();

            return back()->with('status', 'Relance envoyée à ' . $invoice->client->email . '.');
        }

        return back()->withErrors(['invoice' => 'Aucune adresse e-mail n\'est rattachée au client « '
            . $invoice->client?->name . ' ». Ajoutez-la dans sa fiche client pour pouvoir le relancer.']);
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('lines', 'client', 'company', 'payments');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return $pdf->stream($invoice->number . '.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInvoice(Request $request): array
    {
        return $request->validate(array_merge([
            'client_id'  => ['required', Rule::exists('clients', 'id')->where('company_id', Auth::user()->company_id)],
            'title'      => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'due_date'   => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes'      => ['nullable', 'string'],
        ], $this->lineRules()));
    }
}
