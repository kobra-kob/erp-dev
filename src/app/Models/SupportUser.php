<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Compte de la console de support (super-admin).
 *
 * Stocké dans une table dédiée `support_users`, totalement séparée des
 * utilisateurs des tenants : aucun company_id, donc jamais soumis au
 * {@see \App\Models\Scopes\CompanyScope}. Authentifié via le guard « support ».
 */
#[Fillable(['name', 'email', 'password', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class SupportUser extends Authenticatable
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'password'                => 'hashed',
            'is_active'               => 'boolean',
            'last_login_at'           => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }
}
