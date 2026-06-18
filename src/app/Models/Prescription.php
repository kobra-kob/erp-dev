<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'client_id', 'prescriber', 'prescribed_at',
    'od_sphere', 'od_cylinder', 'od_axis', 'od_addition',
    'og_sphere', 'og_cylinder', 'og_axis', 'og_addition',
    'pupillary_distance', 'notes',
])]
class Prescription extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return ['prescribed_at' => 'date'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Formate une correction signée (ex. +1.50 / -0.75). */
    public static function fmt(?float $v): string
    {
        return $v === null ? '—' : sprintf('%+.2f', $v);
    }
}
