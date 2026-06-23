<?php

namespace App\Models;

use App\Models\Concerns\DocumentLine;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id', 'product_id', 'type', 'description', 'quantity', 'unit_price', 'tax_rate', 'position',
])]
class InvoiceLine extends Model
{
    use DocumentLine;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate'   => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
