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
    'company_id', 'client_id', 'number', 'status', 'public_token', 'title',
    'issue_date', 'valid_until', 'notes',
    'subtotal_ht', 'tax_amount', 'total_ttc',
])]
class Quote extends Model
{
    use BelongsToCompany, CalculatesTotals, GeneratesDocumentNumber;

    public const NUMBER_PREFIX = 'DEV';

    protected static function booted(): void
    {
        static::creating(function (Quote $quote): void {
            $quote->public_token ??= \Illuminate\Support\Str::random(40);
        });
    }

    /** Garantit l'existence d'un jeton public (devis anciens). */
    public function ensurePublicToken(): string
    {
        if (! $this->public_token) {
            $this->forceFill(['public_token' => \Illuminate\Support\Str::random(40)])->save();
        }

        return $this->public_token;
    }

    /** Lien public de validation (accepter/refuser) destiné au client. */
    public function publicUrl(): string
    {
        return route('quotes.public', $this->ensurePublicToken());
    }

    /** Le client peut encore répondre (ni accepté, ni refusé, ni expiré). */
    public function awaitingClient(): bool
    {
        return in_array($this->status, ['draft', 'sent'], true);
    }

    public const STATUSES = [
        'draft'    => 'Brouillon',
        'sent'     => 'Envoyé',
        'accepted' => 'Accepté',
        'refused'  => 'Refusé',
        'expired'  => 'Expiré',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'valid_until' => 'date',
            'subtotal_ht' => 'decimal:2',
            'tax_amount'  => 'decimal:2',
            'total_ttc'   => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('position');
    }

    public function invoice(): BelongsTo
    {
        // Facture éventuellement issue de ce devis.
        return $this->belongsTo(Invoice::class, 'id', 'quote_id');
    }

    // --- Statut ---

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'accepted' => 'success',
            'sent'     => 'primary',
            'refused'  => 'danger',
            'expired'  => 'secondary',
            default    => 'warning', // draft
        };
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isConvertedToInvoice(): bool
    {
        return Invoice::withoutGlobalScopes()->where('quote_id', $this->id)->exists();
    }
}
