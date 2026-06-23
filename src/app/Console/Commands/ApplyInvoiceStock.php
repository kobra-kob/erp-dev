<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Product;
use App\Services\StockManager;
use Illuminate\Console\Command;

/**
 * Régularisation rétroactive du stock : décompte les produits des factures dont
 * le stock n'a jamais été appliqué (factures créées avant l'activation du
 * décompte automatique). Idempotent grâce au marqueur invoices.stock_applied_at.
 *
 * Les anciennes lignes n'ont pas de product_id : on retrouve le produit par son
 * libellé (nom, ou « nom (référence) »), exactement comme l'insérait le sélecteur.
 *
 *   php artisan stock:apply-invoices            # toutes les entreprises
 *   php artisan stock:apply-invoices --company=4
 *   php artisan stock:apply-invoices --dry-run  # simulation, sans rien modifier
 */
class ApplyInvoiceStock extends Command
{
    protected $signature = 'stock:apply-invoices
        {--company= : Limiter à une entreprise (id)}
        {--dry-run : Simuler sans modifier le stock}';

    protected $description = "Décompte le stock des factures passées non encore appliquées (régularisation).";

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $stock = new StockManager();

        $query = Invoice::withoutGlobalScopes()->whereNull('stock_applied_at')->with('lines');
        if ($companyId = $this->option('company')) {
            $query->where('company_id', $companyId);
        }
        $invoices = $query->orderBy('id')->get();

        if ($invoices->isEmpty()) {
            $this->info('Aucune facture à régulariser. ✅');

            return self::SUCCESS;
        }

        $this->info(($dry ? '[SIMULATION] ' : '') . "{$invoices->count()} facture(s) à traiter…");

        $maps = [];          // company_id => [libellé minuscule => Product]
        $invoicesDone = 0;
        $linesMatched = 0;
        $unmatched = [];

        foreach ($invoices as $invoice) {
            $map = $maps[$invoice->company_id] ??= $this->productMap($invoice->company_id);
            $consumed = [];

            foreach ($invoice->lines as $line) {
                $pid = $line->product_id;

                // Ancienne ligne sans lien produit : tenter de la rattacher par libellé.
                if (! $pid && $line->type === 'materiel') {
                    $key = mb_strtolower(trim((string) $line->description));
                    if (isset($map[$key])) {
                        $pid = $map[$key]->id;
                        if (! $dry) {
                            $line->product_id = $pid;
                            $line->save();
                        }
                        $linesMatched++;
                    } else {
                        $unmatched[] = "{$invoice->number} — « {$line->description} »";
                    }
                }

                if ($pid) {
                    $consumed[$pid] = ($consumed[$pid] ?? 0) + (float) $line->quantity;
                }
            }

            if (! $dry) {
                if ($consumed) {
                    $stock->reconcile($invoice->company_id, [], $consumed);
                }
                // Marqueur posé même sans produit, pour ne plus retraiter la facture.
                $invoice->update(['stock_applied_at' => now()]);
            }

            if ($consumed) {
                $invoicesDone++;
            }
        }

        $this->newLine();
        $this->info(($dry ? '[SIMULATION] ' : '') . "Factures avec décompte stock : {$invoicesDone}");
        $this->info("Lignes rattachées par libellé : {$linesMatched}");

        if ($unmatched) {
            $this->warn(count($unmatched) . ' ligne(s) « matériel » non rattachées à un produit (ignorées) :');
            foreach (array_slice($unmatched, 0, 30) as $u) {
                $this->line('  - ' . $u);
            }
            if (count($unmatched) > 30) {
                $this->line('  … (' . (count($unmatched) - 30) . ' de plus)');
            }
        }

        if ($dry) {
            $this->newLine();
            $this->comment('Simulation terminée — relancez sans --dry-run pour appliquer.');
        }

        return self::SUCCESS;
    }

    /** @return array<string, Product> libellé (minuscule) => produit */
    private function productMap(int $companyId): array
    {
        $map = [];
        foreach (Product::withoutGlobalScopes()->where('company_id', $companyId)->get() as $p) {
            $map[mb_strtolower(trim($p->name))] = $p;
            if ($p->reference) {
                $map[mb_strtolower(trim($p->name . ' (' . $p->reference . ')'))] = $p;
            }
        }

        return $map;
    }
}
