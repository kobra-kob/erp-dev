<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'name', 'bank_name', 'iban', 'opening_balance'])]
class BankAccount extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return ['opening_balance' => 'decimal:2'];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class)->orderByDesc('transaction_date');
    }

    /** Solde = solde initial + somme des mouvements importés. */
    public function balance(): float
    {
        return round((float) $this->opening_balance + (float) $this->transactions()->sum('amount'), 2);
    }
}
