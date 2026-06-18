<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'user_id', 'type', 'start_date', 'end_date', 'reason',
    'status', 'reviewed_by', 'reviewed_at', 'review_comment',
])]
class LeaveRequest extends Model
{
    use BelongsToCompany;

    public const TYPES = [
        'conges_payes' => 'Congés payés',
        'rtt'          => 'RTT',
        'maladie'      => 'Maladie',
        'sans_solde'   => 'Sans solde',
        'autre'        => 'Autre',
    ];

    public const STATUSES = [
        'pending'   => 'En attente',
        'approved'  => 'Approuvée',
        'rejected'  => 'Refusée',
        'cancelled' => 'Annulée',
    ];

    protected function casts(): array
    {
        return [
            'start_date'  => 'date',
            'end_date'    => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Nombre de jours (calendaires, bornes incluses). */
    public function durationDays(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Autre';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved'  => 'success',
            'rejected'  => 'danger',
            'cancelled' => 'secondary',
            default     => 'warning', // pending
        };
    }
}
