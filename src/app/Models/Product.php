<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'name', 'reference', 'unit',
    'purchase_price', 'sale_price', 'stock', 'min_stock', 'notes',
    'supplier_name', 'supplier_email', 'reorder_quantity',
])]
class Product extends Model
{
    use HasFactory, BelongsToCompany;

    protected function casts(): array
    {
        return [
            'purchase_price'   => 'decimal:2',
            'sale_price'       => 'decimal:2',
            'stock'            => 'decimal:2',
            'min_stock'        => 'decimal:2',
            'reorder_quantity' => 'decimal:2',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class)->latest('ordered_at');
    }

    /** Quantité à commander : celle pré-renseignée, sinon de quoi repasser au-dessus du minimum. */
    public function orderQuantity(): float
    {
        if ((float) $this->reorder_quantity > 0) {
            return (float) $this->reorder_quantity;
        }

        return max(1, (float) $this->min_stock * 2 - (float) $this->stock);
    }

    /** Réapprovisionnable automatiquement : stock faible + e-mail fournisseur connu. */
    public function canReorder(): bool
    {
        return $this->isLowStock() && filled($this->supplier_email);
    }

    /** Stock au niveau ou sous le seuil d'alerte. */
    public function isLowStock(): bool
    {
        return (float) $this->stock <= (float) $this->min_stock;
    }

    /** Marge unitaire en euros. */
    public function margin(): float
    {
        return round((float) $this->sale_price - (float) $this->purchase_price, 2);
    }

    /** Marge en pourcentage du prix de vente. */
    public function marginPercent(): ?float
    {
        if ((float) $this->sale_price <= 0) {
            return null;
        }

        return round($this->margin() / (float) $this->sale_price * 100, 1);
    }
}
