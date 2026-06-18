<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'owner_id', 'siret', 'address', 'city', 'zip',
    'phone', 'email', 'logo', 'subscription',
])]
class Company extends Model
{
    /** Nombre maximum d'employés (en plus de l'owner) par entreprise. */
    public const MAX_EMPLOYEES = 5;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /** Nombre d'employés (utilisateurs hors owner). */
    public function employeeCount(): int
    {
        return $this->users()->where('id', '!=', $this->owner_id)->count();
    }

    /** Reste-t-il de la place pour un employé supplémentaire ? */
    public function canAddEmployee(): bool
    {
        return $this->employeeCount() < self::MAX_EMPLOYEES;
    }

    // --- Modules optionnels (verticaux) ---

    public function modules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    /** Le module optionnel est-il activé pour cette entreprise ? */
    public function hasModule(string $key): bool
    {
        return $this->modules()->where('module_key', $key)->where('active', true)->exists();
    }

    public function enableModule(string $key): void
    {
        $this->modules()->updateOrCreate(
            ['module_key' => $key],
            ['active' => true, 'activated_at' => now()],
        );
    }

    public function disableModule(string $key): void
    {
        // Désactivation sans suppression des données (réversible).
        $this->modules()->where('module_key', $key)->update(['active' => false]);
    }
}
