<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'product_id', 'expense_id', 'quantity', 'unit_price',
    'supplier_name', 'supplier_email', 'status', 'source', 'ordered_at',
])]
class PurchaseOrder extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:2',
            'unit_price' => 'decimal:2',
            'ordered_at' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function total(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }
}
