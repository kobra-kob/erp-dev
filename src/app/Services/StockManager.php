<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Mouvements de stock liés à la facturation. Le décompte n'intervient QU'À la
 * facture (pas au devis). Le stock baisse à la vente (facture) et remonte si la
 * facture est supprimée ou ses quantités réduites. La réincrémentation à l'achat
 * est gérée séparément (dépenses « matériel » / réappro fournisseur).
 */
class StockManager
{
    /**
     * Applique la variation nette de consommation entre l'ancien et le nouvel
     * état d'une facture : stock -= (nouvelle qté - ancienne qté) par produit.
     *
     * @param  array<int, float>  $old  product_id => quantité avant
     * @param  array<int, float>  $new  product_id => quantité après
     */
    public function reconcile(int $companyId, array $old, array $new): void
    {
        $ids = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($ids as $pid) {
            $delta = (float) ($new[$pid] ?? 0) - (float) ($old[$pid] ?? 0);
            if ($delta === 0.0) {
                continue;
            }

            // stock -= delta (delta négatif → restitution). Jamais sous zéro.
            Product::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('id', $pid)
                ->update(['stock' => DB::raw('GREATEST(0, stock - ' . $delta . ')')]);
        }
    }
}
