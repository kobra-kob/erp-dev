<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingEntry;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de comptabilité en partie double.
 *
 * Génère les écritures comptables à partir des documents de gestion
 * (factures, paiements, dépenses). Le plan comptable et les journaux sont
 * provisionnés à la volée (PCG simplifié). `rebuild()` est idempotent :
 * il régénère toutes les écritures automatiques sans toucher aux écritures
 * saisies manuellement.
 */
class AccountingService
{
    /** Comptes de charge par catégorie de dépense. */
    private const EXPENSE_ACCOUNTS = [
        'carburant'      => ['606100', 'Carburant'],
        'materiel'       => ['606000', 'Achats de matériel'],
        'fournitures'    => ['606300', 'Fournitures'],
        'sous_traitance' => ['604000', 'Sous-traitance'],
        'deplacement'    => ['625000', 'Déplacements'],
        'autre'          => ['606800', 'Autres achats'],
    ];

    private function companyId(): int
    {
        return (int) Auth::user()->company_id;
    }

    public function account(string $code, string $name, string $type): Account
    {
        return Account::firstOrCreate(
            ['company_id' => $this->companyId(), 'code' => $code],
            ['name' => $name, 'type' => $type],
        );
    }

    public function journal(string $code, string $name, string $type): Journal
    {
        return Journal::firstOrCreate(
            ['company_id' => $this->companyId(), 'code' => $code],
            ['name' => $name, 'type' => $type],
        );
    }

    /**
     * Régénère toutes les écritures automatiques de l'entreprise courante.
     *
     * @return array{entries:int, balanced:bool}
     */
    public function rebuild(): array
    {
        return DB::transaction(function () {
            // Purge des écritures auto (les écritures manuelles sont conservées).
            AccountingEntry::where('source_type', '!=', 'manual')->delete();

            foreach (Invoice::with('lines')->get() as $invoice) {
                $this->postInvoice($invoice);
            }
            foreach (Payment::with('invoice')->get() as $payment) {
                $this->postPayment($payment);
            }
            foreach (Expense::get() as $expense) {
                $this->postExpense($expense);
            }

            $entries = AccountingEntry::with('lines')->get();

            return [
                'entries'  => $entries->count(),
                'balanced' => $entries->every(fn ($e) => $e->isBalanced()),
            ];
        });
    }

    /** Facture client : D 411 (TTC) / C 707 (HT) + C 445710 (TVA). */
    public function postInvoice(Invoice $invoice): AccountingEntry
    {
        $client = $this->account('411000', 'Clients', 'tiers');
        $sales  = $this->account('707000', 'Ventes de prestations', 'produit');
        $vat    = $this->account('445710', 'TVA collectée', 'tva');

        $lines = [
            [$client->id, (float) $invoice->total_ttc, 0, 'Facture ' . $invoice->number],
            [$sales->id, 0, (float) $invoice->subtotal_ht, 'Prestations HT'],
        ];
        if ((float) $invoice->tax_amount > 0) {
            $lines[] = [$vat->id, 0, (float) $invoice->tax_amount, 'TVA collectée'];
        }

        return $this->createEntry(
            $this->journal('VT', 'Ventes', 'sale'),
            $invoice->issue_date, 'Facture ' . $invoice->number, $invoice->number,
            'invoice', $invoice->id, $lines,
        );
    }

    /** Encaissement : D 512 (banque) / C 411 (clients). */
    public function postPayment(Payment $payment): AccountingEntry
    {
        $bank   = $this->account('512000', 'Banque', 'tresorerie');
        $client = $this->account('411000', 'Clients', 'tiers');
        $ref    = $payment->invoice?->number;

        return $this->createEntry(
            $this->journal('BQ', 'Banque', 'bank'),
            $payment->paid_at, 'Règlement ' . $ref, $ref,
            'payment', $payment->id,
            [
                [$bank->id, (float) $payment->amount, 0, 'Encaissement'],
                [$client->id, 0, (float) $payment->amount, 'Règlement client'],
            ],
        );
    }

    /** Dépense : D charge (HT) + D 445660 (TVA déductible) / C 512 (TTC). */
    public function postExpense(Expense $expense): AccountingEntry
    {
        [$code, $name] = self::EXPENSE_ACCOUNTS[$expense->category] ?? self::EXPENSE_ACCOUNTS['autre'];
        $charge = $this->account($code, $name, 'charge');
        $bank   = $this->account('512000', 'Banque', 'tresorerie');

        $ht  = $expense->amountHt();
        $vat = $expense->vatAmount();

        $lines = [[$charge->id, $ht, 0, $expense->label]];
        if ($vat > 0) {
            $deductible = $this->account('445660', 'TVA déductible', 'tva');
            $lines[] = [$deductible->id, $vat, 0, 'TVA déductible'];
        }
        $lines[] = [$bank->id, 0, (float) $expense->amount, 'Paiement dépense'];

        return $this->createEntry(
            $this->journal('AC', 'Achats', 'purchase'),
            $expense->spent_at, $expense->label, null,
            'expense', $expense->id, $lines,
        );
    }

    /**
     * @param  array<int, array{0:int,1:float,2:float,3:?string}>  $lines  [account_id, debit, credit, label]
     */
    public function createEntry(Journal $journal, $date, string $label, ?string $reference, string $sourceType, ?int $sourceId, array $lines): AccountingEntry
    {
        $entry = AccountingEntry::create([
            'company_id'  => $this->companyId(),
            'journal_id'  => $journal->id,
            'entry_date'  => $date,
            'label'       => $label,
            'reference'   => $reference,
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
        ]);

        foreach ($lines as [$accountId, $debit, $credit, $lineLabel]) {
            $entry->lines()->create([
                'account_id' => $accountId,
                'label'      => $lineLabel,
                'debit'      => round((float) $debit, 2),
                'credit'     => round((float) $credit, 2),
            ]);
        }

        return $entry->load('lines');
    }
}
