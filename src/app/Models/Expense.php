<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'project_id', 'product_id', 'quote_id', 'user_id',
    'category', 'label', 'amount', 'vat_rate', 'quantity', 'spent_at', 'supplier', 'notes',
    'receipt_path', 'receipt_name', 'receipt_mime', 'receipt_size',
])]
class Expense extends Model
{
    use HasFactory, BelongsToCompany;

    public const CATEGORIES = [
        'carburant'      => 'Carburant',
        'materiel'       => 'Matériel',
        'deplacement'    => 'Déplacement',
        'fournitures'    => 'Fournitures',
        'sous_traitance' => 'Sous-traitance',
        'autre'          => 'Autre',
    ];

    protected function casts(): array
    {
        return [
            'spent_at' => 'date',
            'amount'   => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'quantity' => 'decimal:2',
        ];
    }

    /** Montant HT (le montant saisi est TTC). */
    public function amountHt(): float
    {
        $rate = (float) $this->vat_rate;
        return round((float) $this->amount / (1 + $rate / 100), 2);
    }

    /** TVA déductible. */
    public function vatAmount(): float
    {
        return round((float) $this->amount - $this->amountHt(), 2);
    }

    /**
     * Une dépense « matériel » liée à un produit avec une quantité réapprovisionne
     * automatiquement le stock du produit (achat = entrée de stock).
     */
    protected static function booted(): void
    {
        static::created(function (Expense $expense): void {
            if ($expense->category === 'materiel' && $expense->product_id && (float) $expense->quantity > 0) {
                Product::withoutGlobalScopes()
                    ->where('id', $expense->product_id)
                    ->where('company_id', $expense->company_id)
                    ->first()?->increment('stock', (float) $expense->quantity);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Autre';
    }

    public function hasReceipt(): bool
    {
        return ! empty($this->receipt_path);
    }

    public function receiptIsImage(): bool
    {
        return str_starts_with((string) $this->receipt_mime, 'image/');
    }
}
