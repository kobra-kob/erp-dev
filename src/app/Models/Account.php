<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'code', 'name', 'type'])]
class Account extends Model
{
    use BelongsToCompany;

    protected $table = 'accounting_accounts';

    public const TYPES = [
        'tiers'       => 'Comptes de tiers',
        'tresorerie'  => 'Trésorerie',
        'tva'         => 'TVA',
        'charge'      => 'Charges',
        'produit'     => 'Produits',
        'actif'       => 'Actif',
        'passif'      => 'Passif',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingEntryLine::class, 'account_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
