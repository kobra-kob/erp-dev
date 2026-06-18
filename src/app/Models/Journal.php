<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'code', 'name', 'type'])]
class Journal extends Model
{
    use BelongsToCompany;

    protected $table = 'accounting_journals';

    public function entries(): HasMany
    {
        return $this->hasMany(AccountingEntry::class);
    }
}
