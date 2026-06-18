<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasFileMetadata;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'client_id', 'user_id', 'title', 'category',
    'original_name', 'path', 'mime', 'size',
])]
class Document extends Model
{
    use BelongsToCompany, HasFileMetadata;

    public const CATEGORIES = [
        'contrat'       => 'Contrat',
        'facture'       => 'Facture',
        'photo'         => 'Photo',
        'administratif' => 'Administratif',
        'autre'         => 'Autre',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Autre';
    }
}
