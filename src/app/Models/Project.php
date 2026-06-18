<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'client_id', 'name', 'status', 'address', 'city', 'zip',
    'description', 'start_date', 'end_date', 'budget', 'progress',
])]
class Project extends Model
{
    use HasFactory, BelongsToCompany;

    public const STATUSES = [
        'planned'     => 'Planifié',
        'in_progress' => 'En cours',
        'on_hold'     => 'En pause',
        'done'        => 'Terminé',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'budget'     => 'decimal:2',
            'progress'   => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->latest();
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'done'        => 'success',
            'in_progress' => 'primary',
            'on_hold'     => 'warning',
            default       => 'secondary', // planned
        };
    }
}
