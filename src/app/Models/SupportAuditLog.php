<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Journal d'audit de la console de support.
 *
 * Chaque action sensible (connexion, suspension d'un tenant, impersonation,
 * activation de module…) y est tracée : qui, quoi, quel tenant, depuis quelle IP.
 */
#[Fillable([
    'support_user_id', 'action', 'company_id',
    'target_type', 'target_id', 'description', 'properties', 'ip_address',
])]
class SupportAuditLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Enregistre une entrée d'audit pour l'utilisateur support courant.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function record(string $action, array $attributes = []): self
    {
        return static::create(array_merge([
            'support_user_id' => Auth::guard('support')->id(),
            'action'          => $action,
            'ip_address'      => request()->ip(),
            'created_at'      => now(),
        ], $attributes));
    }

    public function supportUser(): BelongsTo
    {
        return $this->belongsTo(SupportUser::class);
    }

    public function company(): BelongsTo
    {
        // Sans contrainte de scope : la console doit voir tous les tenants.
        return $this->belongsTo(Company::class);
    }
}
