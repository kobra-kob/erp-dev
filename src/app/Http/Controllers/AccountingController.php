<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingController extends Controller
{
    public function index(): View
    {
        $accounts = $this->balanceRows();

        $charges = $accounts->where('account.type', 'charge')->sum(fn ($r) => $r['credit'] === null ? 0 : $r['debit'] - $r['credit']);
        $produits = $accounts->where('account.type', 'produit')->sum(fn ($r) => $r['credit'] - $r['debit']);
        $tresorerie = $accounts->firstWhere('account.code', '512000');

        return view('accounting.index', [
            'entriesCount' => AccountingEntry::count(),
            'accountsCount' => Account::count(),
            'charges'    => round($charges, 2),
            'produits'   => round($produits, 2),
            'resultat'   => round($produits - $charges, 2),
            'tresorerie' => $tresorerie ? round($tresorerie['debit'] - $tresorerie['credit'], 2) : 0,
        ]);
    }

    public function rebuild(AccountingService $accounting): RedirectResponse
    {
        $result = $accounting->rebuild();

        $msg = "{$result['entries']} écriture(s) générée(s).";
        $msg .= $result['balanced'] ? ' Comptabilité équilibrée ✓' : ' ⚠️ Déséquilibre détecté.';

        return back()->with('status', $msg);
    }

    public function accounts(): View
    {
        return view('accounting.accounts', [
            'accounts' => Account::orderBy('code')->get(),
        ]);
    }

    public function journal(Request $request): View
    {
        $entries = AccountingEntry::with('journal', 'lines.account')
            ->when($request->query('journal'), fn ($q, $code) => $q->whereHas('journal', fn ($j) => $j->where('code', $code)))
            ->latest('entry_date')->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('accounting.journal', [
            'entries'  => $entries,
            'journals' => \App\Models\Journal::orderBy('code')->get(),
            'current'  => $request->query('journal'),
        ]);
    }

    public function ledger(Request $request): View
    {
        $accounts = Account::orderBy('code')->get();
        $accountId = $request->query('account', $accounts->first()?->id);

        $lines = collect();
        $account = $accounts->firstWhere('id', (int) $accountId);
        if ($account) {
            $lines = $account->lines()->with('entry')->get()
                ->sortBy(fn ($l) => [$l->entry->entry_date->timestamp, $l->id])
                ->values();
        }

        return view('accounting.ledger', compact('accounts', 'account', 'lines'));
    }

    public function balance(): View
    {
        $rows = $this->balanceRows();

        return view('accounting.balance', [
            'rows'        => $rows,
            'totalDebit'  => round($rows->sum('debit'), 2),
            'totalCredit' => round($rows->sum('credit'), 2),
        ]);
    }

    public function incomeStatement(): View
    {
        $rows = $this->balanceRows();
        $charges = $rows->where('account.type', 'charge')
            ->map(fn ($r) => ['account' => $r['account'], 'amount' => round($r['debit'] - $r['credit'], 2)])
            ->filter(fn ($r) => $r['amount'] != 0)->values();
        $produits = $rows->where('account.type', 'produit')
            ->map(fn ($r) => ['account' => $r['account'], 'amount' => round($r['credit'] - $r['debit'], 2)])
            ->filter(fn ($r) => $r['amount'] != 0)->values();

        return view('accounting.income', [
            'charges'        => $charges,
            'produits'       => $produits,
            'totalCharges'   => round($charges->sum('amount'), 2),
            'totalProduits'  => round($produits->sum('amount'), 2),
            'resultat'       => round($produits->sum('amount') - $charges->sum('amount'), 2),
        ]);
    }

    /** Déclaration de TVA (CA3 simplifiée) : collectée − déductible. */
    public function vatReturn(): View
    {
        $byCode = fn (string $code, string $col) => (float) AccountingEntryLine::whereHas(
            'account', fn ($q) => $q->where('code', $code)
        )->sum($col);

        $collectee  = round($byCode('445710', 'credit'), 2);
        $deductible = round($byCode('445660', 'debit'), 2);
        $net        = round($collectee - $deductible, 2);

        return view('accounting.vat', [
            'caHt'       => round($byCode('707000', 'credit'), 2),
            'collectee'  => $collectee,
            'deductible' => $deductible,
            'net'        => $net,
        ]);
    }

    /** Bilan simplifié (actif / passif). */
    public function balanceSheet(): View
    {
        $rows = $this->balanceRows();
        $solde = fn ($r) => round($r['debit'] - $r['credit'], 2);
        $starts = fn ($r, $p) => str_starts_with($r['account']->code, $p);

        $actif = collect([
            ['Créances clients (411)', $rows->filter(fn ($r) => $starts($r, '411'))->sum($solde)],
            ['Disponibilités (512/530)', $rows->filter(fn ($r) => $starts($r, '51') || $starts($r, '53'))->sum($solde)],
            ['TVA déductible (44566)', $rows->filter(fn ($r) => $starts($r, '44566'))->sum($solde)],
        ])->map(fn ($l) => ['label' => $l[0], 'amount' => round($l[1], 2)])->filter(fn ($l) => $l['amount'] != 0)->values();

        $resultat = round(
            $rows->where('account.type', 'produit')->sum(fn ($r) => $r['credit'] - $r['debit'])
            - $rows->where('account.type', 'charge')->sum(fn ($r) => $r['debit'] - $r['credit']),
            2
        );

        $passif = collect([
            ['Dettes fournisseurs (401)', -$rows->filter(fn ($r) => $starts($r, '401'))->sum($solde)],
            ['TVA collectée (44571)', -$rows->filter(fn ($r) => $starts($r, '44571'))->sum($solde)],
            ['Résultat de l\'exercice', $resultat],
        ])->map(fn ($l) => ['label' => $l[0], 'amount' => round($l[1], 2)])->filter(fn ($l) => $l['amount'] != 0)->values();

        return view('accounting.balance-sheet', [
            'actif'       => $actif,
            'passif'      => $passif,
            'totalActif'  => round($actif->sum('amount'), 2),
            'totalPassif' => round($passif->sum('amount'), 2),
        ]);
    }

    /** Export FEC (Fichier des Écritures Comptables) — format légal, séparateur tabulation. */
    public function fec(): StreamedResponse
    {
        $entries = AccountingEntry::with('journal', 'lines.account')->orderBy('entry_date')->orderBy('id')->get();

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'JournalCode', 'JournalLib', 'EcritureNum', 'EcritureDate', 'CompteNum', 'CompteLib',
                'PieceRef', 'PieceDate', 'EcritureLib', 'Debit', 'Credit', 'EcritureLet', 'DateLet',
                'ValidDate', 'Montantdevise', 'Idevise',
            ], "\t");

            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    fputcsv($out, [
                        $entry->journal->code,
                        $entry->journal->name,
                        $entry->id,
                        $entry->entry_date->format('Ymd'),
                        $line->account->code,
                        $line->account->name,
                        $entry->reference,
                        $entry->entry_date->format('Ymd'),
                        $line->label ?: $entry->label,
                        number_format((float) $line->debit, 2, ',', ''),
                        number_format((float) $line->credit, 2, ',', ''),
                        '', '', $entry->entry_date->format('Ymd'), '', '',
                    ], "\t");
                }
            }
            fclose($out);
        }, 'FEC-' . now()->format('Ymd') . '.txt', ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Agrège débit/crédit par compte (base de la balance et des autres états).
     *
     * @return \Illuminate\Support\Collection<int, array{account: Account, debit: float, credit: float}>
     */
    private function balanceRows()
    {
        return Account::orderBy('code')->get()->map(fn (Account $a) => [
            'account' => $a,
            'debit'   => (float) $a->lines()->sum('debit'),
            'credit'  => (float) $a->lines()->sum('credit'),
        ]);
    }
}
