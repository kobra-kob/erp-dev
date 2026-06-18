<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'company_id', 'email', 'action', 'ip_address', 'user_agent',
])]
class LoginAudit extends Model
{
    public const UPDATED_AT = null; // un seul horodatage : created_at

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre un évènement d'audit.
     */
    public static function log(string $action, ?User $user = null, ?string $email = null): void
    {
        static::create([
            'user_id'    => $user?->id,
            'company_id' => $user?->company_id,
            'email'      => $email ?? $user?->email,
            'action'     => $action,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
        ]);
    }
}
