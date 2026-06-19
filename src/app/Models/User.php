<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'company_id', 'name', 'email', 'password',
    'role', 'role_id', 'phone', 'skill', 'is_active', 'module_preferences',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Rôles intégrés. */
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_GERANT = 'GERANT';
    public const ROLE_EMPLOYE = 'EMPLOYE';
    public const ROLE_CUSTOM = 'CUSTOM'; // rôle personnalisé (voir role_id)

    public const ROLES = [self::ROLE_ADMIN, self::ROLE_GERANT, self::ROLE_EMPLOYE];

    /** Modules toujours accessibles quel que soit le rôle. */
    public const ALWAYS_ALLOWED = ['settings', 'leaves'];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'module_preferences'      => 'array',
            'is_active'               => 'boolean',
        ];
    }

    // --- Relations ---

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'user_id')->latest();
    }

    public function customRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // --- Rôles ---

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // --- Accès par module (rôles intégrés ou personnalisés) ---

    /**
     * Liste des clés de modules accessibles à l'utilisateur.
     *
     * @return array<int, string>
     */
    public function allowedModules(): array
    {
        if ($this->isAdmin()) {
            return array_keys(config('modules'));
        }

        if ($this->role === self::ROLE_CUSTOM) {
            return array_values(array_merge((array) ($this->customRole?->modules ?? []), self::ALWAYS_ALLOWED));
        }

        // Rôles intégrés GERANT / EMPLOYE : défauts définis dans config/modules.php.
        return collect(config('modules'))
            ->filter(fn ($m) => in_array($this->role, $m['roles'], true))
            ->keys()
            ->merge(self::ALWAYS_ALLOWED)
            ->unique()->values()->all();
    }

    public function canAccessModule(string $key): bool
    {
        // Module obligatoire (Paramètres) : toujours accessible.
        if (config("modules.$key.mandatory")) {
            return true;
        }

        // Gate entreprise : le module (socle ou vertical) doit être activé.
        if (! $this->company?->isModuleEnabled($key)) {
            return false;
        }

        // Gate rôle.
        if ($this->isAdmin() || in_array($key, self::ALWAYS_ALLOWED, true)) {
            return true;
        }

        return in_array($key, $this->allowedModules(), true);
    }

    public function isGerant(): bool
    {
        return $this->role === self::ROLE_GERANT;
    }

    public function isEmploye(): bool
    {
        return $this->role === self::ROLE_EMPLOYE;
    }

    // --- Double authentification ---

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /** Libellé lisible du rôle. */
    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN   => 'Administrateur',
            self::ROLE_GERANT  => 'Gérant',
            self::ROLE_EMPLOYE => 'Employé',
            self::ROLE_CUSTOM  => $this->customRole?->name ?? 'Rôle personnalisé',
            default            => $this->role,
        };
    }
}
