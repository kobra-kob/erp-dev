<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\CalculatesTotals;
use App\Models\Concerns\GeneratesDocumentNumber;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'client_id', 'quote_id', 'number', 'status', 'title',
    'issue_date', 'due_date', 'notes',
    'subtotal_ht', 'tax_amount', 'total_ttc', 'paid_amount',
    'reminders_sent', 'last_reminder_at', 'sent_at', 'stock_applied_at',
])]
class Invoice extends Model
{
    use BelongsToCompany, CalculatesTotals, GeneratesDocumentNumber;

    public const NUMBER_PREFIX = 'FAC';

    public const STATUSES = [
        'unpaid'  => 'Non payée',
        'partial' => 'Partiel',
        'paid'    => 'Payée',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'due_date'    => 'date',
            'subtotal_ht' => 'decimal:2',
            'tax_amount'  => 'decimal:2',
            'total_ttc'   => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'last_reminder_at' => 'datetime',
            'sent_at'          => 'datetime',
            'stock_applied_at' => 'datetime',
        ];
    }

    /** Enregistre l'envoi d'une relance. */
    public function markReminded(): void
    {
        $this->increment('reminders_sent');
        $this->forceFill(['last_reminder_at' => now()])->save();
    }

    /** Facture éligible à une relance (impayée, échue, pas relancée depuis 7 j). */
    public function needsReminder(): bool
    {
        return $this->isOverdue()
            && (is_null($this->last_reminder_at) || $this->last_reminder_at->lt(now()->subDays(7)));
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('position');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    // --- Paiement ---

    /** Recalcule le montant réglé et met à jour le statut de paiement. */
    public function refreshPaymentStatus(bool $save = true): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $this->paid_amount = round($paid, 2);

        $this->status = match (true) {
            $paid <= 0                          => 'unpaid',
            $paid + 0.001 >= (float) $this->total_ttc => 'paid',
            default                             => 'partial',
        };

        if ($save) {
            $this->save();
        }
    }

    public function remainingAmount(): float
    {
        return round((float) $this->total_ttc - (float) $this->paid_amount, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'paid'    => 'success',
            'partial' => 'info',
            default   => 'danger', // unpaid
        };
    }
}
