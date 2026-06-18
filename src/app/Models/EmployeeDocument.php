<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasFileMetadata;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'user_id', 'uploaded_by', 'title', 'type',
    'original_name', 'path', 'mime', 'size',
])]
class EmployeeDocument extends Model
{
    use BelongsToCompany, HasFileMetadata;

    public const TYPES = [
        'contrat'     => 'Contrat de travail',
        'avenant'     => 'Avenant',
        'attestation' => 'Attestation',
        'autre'       => 'Autre',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Autre';
    }
}
