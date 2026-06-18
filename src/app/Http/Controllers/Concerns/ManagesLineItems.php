<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Validation\Rule;

/**
 * Mutualise la validation et la synchronisation des lignes de prestation
 * entre les devis et les factures.
 */
trait ManagesLineItems
{
    /**
     * @return array<string, mixed>
     */
    protected function lineRules(): array
    {
        return [
            'lines'              => ['required', 'array', 'min:1'],
            'lines.*.type'       => ['required', Rule::in(['main_oeuvre', 'materiel', 'deplacement', 'autre'])],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity'   => ['required', 'numeric', 'min:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.tax_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Remplace les lignes d'un document puis recalcule ses totaux.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncLines($document, array $lines): void
    {
        $document->lines()->delete();

        foreach (array_values($lines) as $position => $line) {
            $document->lines()->create([
                'type'        => $line['type'],
                'description' => $line['description'],
                'quantity'    => $line['quantity'],
                'unit_price'  => $line['unit_price'],
                'tax_rate'    => $line['tax_rate'],
                'position'    => $position,
            ]);
        }

        $document->load('lines');
        $document->recalculateTotals();
    }
}
