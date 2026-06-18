<?php

namespace App\Models\Concerns;

/**
 * Recalcule et enregistre les totaux HT / TVA / TTC d'un document
 * (devis ou facture) à partir de ses lignes. La TVA est agrégée par ligne
 * pour gérer des taux différents au sein d'un même document.
 */
trait CalculatesTotals
{
    public function recalculateTotals(bool $save = true): void
    {
        $ht  = 0.0;
        $tax = 0.0;

        foreach ($this->lines as $line) {
            $ht  += $line->lineHt();
            $tax += $line->lineTax();
        }

        $this->subtotal_ht = round($ht, 2);
        $this->tax_amount  = round($tax, 2);
        $this->total_ttc   = round($ht + $tax, 2);

        if ($save) {
            $this->save();
        }
    }

    /**
     * Détail de la TVA par taux (utile pour le PDF).
     *
     * @return array<string, array{base: float, tax: float}>
     */
    public function taxBreakdown(): array
    {
        $rates = [];
        foreach ($this->lines as $line) {
            $key = number_format((float) $line->tax_rate, 2);
            $rates[$key] ??= ['base' => 0.0, 'tax' => 0.0];
            $rates[$key]['base'] += $line->lineHt();
            $rates[$key]['tax']  += $line->lineTax();
        }

        return $rates;
    }
}
