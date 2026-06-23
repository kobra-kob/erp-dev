<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
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
            'lines.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('company_id', Auth::user()->company_id)],
        ];
    }

    /**
     * Quantités demandées par produit dans une liste de lignes (saisie ou modèles).
     *
     * @return array<int, float>  product_id => quantité cumulée
     */
    protected function productQuantities(iterable $lines): array
    {
        $out = [];
        foreach ($lines as $line) {
            $pid = is_array($line) ? ($line['product_id'] ?? null) : ($line->product_id ?? null);
            if (! $pid) {
                continue;
            }
            $qty = (float) (is_array($line) ? ($line['quantity'] ?? 0) : $line->quantity);
            $out[(int) $pid] = ($out[(int) $pid] ?? 0) + $qty;
        }

        return $out;
    }

    /**
     * Vérifie que les quantités produits demandées ne dépassent pas le stock.
     * `$alreadyConsumed` = quantités déjà décomptées par CE document (édition de
     * facture) : elles sont « rendues » donc s'ajoutent au disponible.
     *
     * @return array<string, string>  erreurs (vide si OK)
     */
    protected function stockErrors(array $lines, array $alreadyConsumed = []): array
    {
        $errors = [];
        foreach ($this->productQuantities($lines) as $pid => $qty) {
            $product = Product::find($pid);
            if (! $product) {
                continue;
            }
            $available = (float) $product->stock + (float) ($alreadyConsumed[$pid] ?? 0);
            if ($qty > $available) {
                $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', ' '), '0'), ',');
                $errors['lines'] = "Stock insuffisant pour « {$product->name} » : demandé {$fmt($qty)}, disponible {$fmt($available)} {$product->unit}.";
            }
        }

        return $errors;
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
                'product_id'  => $line['product_id'] ?? null,
                'position'    => $position,
            ]);
        }

        $document->load('lines');
        $document->recalculateTotals();
    }
}
