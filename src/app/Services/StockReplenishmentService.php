<?php

namespace App\Services;

use App\Mail\SupplierOrderMail;
use App\Models\Expense;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Réapprovisionnement du stock : passe une commande fournisseur pour un produit
 * (quantité de réappro pré-renseignée), enregistre la dépense matériel
 * correspondante — qui réincrémente le stock — et notifie le fournisseur.
 */
class StockReplenishmentService
{
    public function replenish(Product $product, string $source = 'manual'): PurchaseOrder
    {
        $qty   = $product->orderQuantity();
        $price = (float) $product->purchase_price;

        // Dépense « matériel » liée au produit → le hook Expense réincrémente le stock.
        $expense = Expense::create([
            'company_id' => $product->company_id,
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'category'   => 'materiel',
            'label'      => 'Réapprovisionnement : ' . $product->name,
            'amount'     => round($qty * $price, 2),
            'quantity'   => $qty,
            'spent_at'   => now()->toDateString(),
            'supplier'   => $product->supplier_name,
        ]);

        $order = PurchaseOrder::create([
            'company_id'     => $product->company_id,
            'product_id'     => $product->id,
            'expense_id'     => $expense->id,
            'quantity'       => $qty,
            'unit_price'     => $price,
            'supplier_name'  => $product->supplier_name,
            'supplier_email' => $product->supplier_email,
            'status'         => 'ordered',
            'source'         => $source,
            'ordered_at'     => now()->toDateString(),
        ]);

        // Notification fournisseur « best-effort » : une panne SMTP ne doit pas
        // annuler le réappro déjà effectué (commande + stock incrémenté).
        if ($product->supplier_email) {
            try {
                Mail::to($product->supplier_email)->send(new SupplierOrderMail($order));
            } catch (\Throwable $e) {
                Log::warning('Échec e-mail fournisseur (réappro #' . $order->id . ') : ' . $e->getMessage());
            }
        }

        return $order;
    }

    /**
     * Réapprovisionne tous les produits en stock faible disposant d'un fournisseur.
     *
     * @return Collection<int, PurchaseOrder>
     */
    public function replenishLowStock(string $source = 'ai'): Collection
    {
        $products = Product::whereColumn('stock', '<=', 'min_stock')
            ->whereNotNull('supplier_email')
            ->get();

        return $products->map(fn (Product $p) => $this->replenish($p, $source));
    }
}
