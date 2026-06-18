<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'method'  => ['required', Rule::in(array_keys(Payment::METHODS))],
            'note'    => ['nullable', 'string', 'max:255'],
        ]);

        $invoice->payments()->create($data + ['company_id' => $invoice->company_id]);
        $invoice->refreshPaymentStatus();

        return back()->with('status', 'Paiement enregistré.');
    }

    public function destroy(Invoice $invoice, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        $payment->delete();
        $invoice->refreshPaymentStatus();

        return back()->with('status', 'Paiement supprimé.');
    }
}
