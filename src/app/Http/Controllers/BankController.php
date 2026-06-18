<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Banque : comptes, import de relevés (CSV) et rapprochement bancaire (lettrage).
 *
 * NOTE : la connexion bancaire temps réel (agrégation DSP2 type Powens/Bridge/
 * GoCardless) n'est pas activée — elle nécessite un prestataire agréé et les
 * identifiants de la banque. L'import de relevé ci-dessous est le point
 * d'extension prévu pour brancher un tel agrégateur.
 */
class BankController extends Controller
{
    public function index(): View
    {
        return view('bank.index', [
            'accounts' => BankAccount::withCount(['transactions as unreconciled_count' => fn ($q) => $q->where('reconciled', false)])->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'bank_name'       => ['nullable', 'string', 'max:255'],
            'iban'            => ['nullable', 'string', 'max:34'],
            'opening_balance' => ['nullable', 'numeric'],
        ]);

        BankAccount::create($data + ['company_id' => $request->user()->company_id]);

        return back()->with('status', 'Compte bancaire ajouté.');
    }

    public function show(BankAccount $bankAccount): View
    {
        return view('bank.show', [
            'account'      => $bankAccount,
            'transactions' => $bankAccount->transactions()->paginate(30),
        ]);
    }

    /** Import d'un relevé CSV : lignes « date;libellé;montant » (montant signé). */
    public function import(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $request->validate(['statement' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $rows = array_filter(array_map('trim', file($request->file('statement')->getRealPath())));
        $imported = 0;

        foreach ($rows as $row) {
            $cols = str_getcsv($row, ';');
            if (count($cols) < 3) {
                continue;
            }
            [$date, $label, $amount] = $cols;
            $amount = (float) str_replace([' ', ','], ['', '.'], $amount);

            try {
                $parsedDate = Carbon::parse(trim($date));
            } catch (\Throwable) {
                continue; // ligne d'en-tête ou format invalide → ignorée
            }

            $bankAccount->transactions()->create([
                'company_id'       => $bankAccount->company_id,
                'transaction_date' => $parsedDate->toDateString(),
                'label'            => $label,
                'amount'           => $amount,
            ]);
            $imported++;
        }

        return back()->with('status', "{$imported} ligne(s) de relevé importée(s).");
    }

    /** Rapprochement automatique : associe les crédits aux paiements de même montant. */
    public function reconcileAuto(BankAccount $bankAccount): RedirectResponse
    {
        $matched = 0;
        $unreconciled = $bankAccount->transactions()->where('reconciled', false)->where('amount', '>', 0)->get();

        foreach ($unreconciled as $tx) {
            $usedPaymentIds = BankTransaction::whereNotNull('payment_id')->pluck('payment_id');
            $payment = Payment::whereNotIn('id', $usedPaymentIds)
                ->whereRaw('ABS(amount - ?) < 0.01', [(float) $tx->amount])
                ->first();

            if ($payment) {
                $tx->update(['reconciled' => true, 'payment_id' => $payment->id]);
                $matched++;
            }
        }

        return back()->with('status', "{$matched} transaction(s) rapprochée(s) automatiquement.");
    }

    public function toggleReconcile(BankAccount $bankAccount, BankTransaction $transaction): RedirectResponse
    {
        abort_unless($transaction->bank_account_id === $bankAccount->id, 404);

        $transaction->update([
            'reconciled' => ! $transaction->reconciled,
            'payment_id' => $transaction->reconciled ? null : $transaction->payment_id,
        ]);

        return back()->with('status', 'Rapprochement mis à jour.');
    }
}
