<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'name', 'modules'])]
class Role extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return ['modules' => 'array'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
