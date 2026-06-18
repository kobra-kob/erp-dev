<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'type', 'name', 'contact_name', 'email', 'phone',
    'address', 'city', 'zip', 'siret', 'notes', 'last_contact_at',
])]
class Client extends Model
{
    use HasFactory, BelongsToCompany;

    protected function casts(): array
    {
        return [
            'last_contact_at' => 'date',
        ];
    }

    /**
     * Libellé lisible du type de client.
     */
    public function typeLabel(): string
    {
        return $this->type === 'professionnel' ? 'Professionnel' : 'Particulier';
    }

    /**
     * Adresse complète sur une ligne (pour l'affichage).
     */
    public function fullAddress(): string
    {
        return collect([$this->address, trim($this->zip . ' ' . $this->city)])
            ->filter()
            ->implode(', ');
    }

    // --- Historique (devis / factures) ---

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class)->latest('issue_date');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('issue_date');
    }

    public function quotesCount(): int
    {
        return $this->quotes()->count();
    }

    public function invoicesCount(): int
    {
        return $this->invoices()->count();
    }
}
