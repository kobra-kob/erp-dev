<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'invoice_id', 'amount', 'paid_at', 'method', 'note',
])]
class Payment extends Model
{
    use BelongsToCompany;

    public const METHODS = [
        'virement' => 'Virement',
        'cheque'   => 'Chèque',
        'especes'  => 'Espèces',
        'cb'       => 'Carte bancaire',
        'autre'    => 'Autre',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'date',
            'amount'  => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }
}
