<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quote;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;


/**
 * Transforme un devis accepté en facture. Idempotent : si une facture existe
 * déjà pour ce devis, elle est renvoyée sans en recréer. Indépendant de
 * l'utilisateur connecté (utilisable depuis le circuit public client).
 */
class QuoteConverter
{
    public function convert(Quote $quote): Invoice
    {
        $existing = Invoice::withoutGlobalScopes()->where('quote_id', $quote->id)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($quote) {
            $quote->loadMissing('lines');

            $invoice = Invoice::create([
                'company_id' => $quote->company_id,
                'client_id'  => $quote->client_id,
                'quote_id'   => $quote->id,
                'number'     => Invoice::nextNumber($quote->company_id),
                'status'     => 'unpaid',
                'title'      => $quote->title,
                'issue_date' => now()->toDateString(),
                'due_date'   => now()->addDays(30)->toDateString(),
                'notes'      => $quote->notes,
            ]);

            foreach ($quote->lines as $line) {
                $invoice->lines()->create($line->only([
                    'product_id', 'type', 'description', 'quantity', 'unit_price', 'tax_rate', 'position',
                ]));
            }

            $invoice->load('lines');
            $invoice->recalculateTotals();

            // La facture issue du devis décompte le stock (le devis ne l'avait pas fait).
            $consumed = [];
            foreach ($invoice->lines as $l) {
                if ($l->product_id) {
                    $consumed[$l->product_id] = ($consumed[$l->product_id] ?? 0) + (float) $l->quantity;
                }
            }
            (new StockManager())->reconcile($invoice->company_id, [], $consumed);
            $invoice->update(['stock_applied_at' => now()]);

            return $invoice;
        });
    }
}
