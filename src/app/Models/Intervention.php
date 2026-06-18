<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'client_id', 'project_id', 'technician_id',
    'title', 'status', 'address', 'start_at', 'end_at', 'notes',
])]
class Intervention extends Model
{
    use BelongsToCompany;

    public const STATUSES = [
        'planned'   => 'Planifiée',
        'done'      => 'Réalisée',
        'cancelled' => 'Annulée',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at'   => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /** Couleur de l'évènement dans le calendrier selon le statut. */
    public function color(): string
    {
        return match ($this->status) {
            'done'      => '#059669',
            'cancelled' => '#9ca3af',
            default     => '#2563eb', // planned
        };
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Durée formatée (ex. « 2 h 30 »). */
    public function duration(): string
    {
        $minutes = $this->start_at->diffInMinutes($this->end_at);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return trim(($h ? "{$h} h " : '') . ($m ? sprintf('%02d', $m) : ($h ? '00' : '0 min')));
    }
}
