<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_id', 'module_key', 'active', 'activated_at', 'settings'])]
class CompanyModule extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'active'       => 'boolean',
            'activated_at' => 'datetime',
            'settings'     => 'array',
        ];
    }
}
