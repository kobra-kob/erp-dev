<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name', 'owner_id', 'siret', 'address', 'city', 'zip',
    'phone', 'email', 'logo', 'subscription',
    'brand_color', 'brand_accent', 'document_shape',
])]
class Company extends Model
{
    /** Nombre maximum d'employés (en plus de l'owner) par entreprise. */
    public const MAX_EMPLOYEES = 5;

    protected static function booted(): void
    {
        // À la création d'une entreprise, on active par défaut tous les modules
        // du socle (hors « settings » obligatoire). L'owner pourra en désactiver
        // à l'inscription (onboarding) ou plus tard dans le catalogue.
        static::created(function (self $company) {
            foreach (array_keys(config('modules', [])) as $key) {
                if (config("modules.$key.mandatory")) {
                    continue;
                }
                $company->modules()->create([
                    'module_key'   => $key,
                    'active'       => true,
                    'activated_at' => now(),
                ]);
            }
        });
    }

    /** Formes disponibles pour les documents (devis/factures). */
    public const SHAPES = [
        'rounded' => 'Arrondie',
        'square'  => 'Anguleuse',
    ];

    // --- Identité visuelle (logo + thème des documents) ---

    public function brandColor(): string
    {
        return $this->brand_color ?: '#2563eb';
    }

    public function brandAccent(): string
    {
        return $this->brand_accent ?: '#1f2937';
    }

    /** Rayon des coins selon la forme choisie. */
    public function documentRadius(): string
    {
        return $this->document_shape === 'square' ? '0' : '6px';
    }

    /** URL publique du logo (affichage web), ou null. */
    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    /** Logo en data-URI base64 — fiable pour l'inclusion dans les PDF (DomPDF). */
    public function logoDataUri(): ?string
    {
        if (! $this->logo || ! Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        $contents = Storage::disk('public')->get($this->logo);
        $mime = Storage::disk('public')->mimeType($this->logo) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    /** Identifiant lisible du tenant, à communiquer au support (ex. AF-000012). */
    public function supportId(): string
    {
        return 'AF-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

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

    /**
     * Le module (socle OU vertical) est-il actif pour l'entreprise ?
     * Les modules « obligatoires » (ex. Paramètres) le sont toujours.
     */
    public function isModuleEnabled(string $key): bool
    {
        if (config("modules.$key.mandatory")) {
            return true;
        }

        return $this->hasModule($key);
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
