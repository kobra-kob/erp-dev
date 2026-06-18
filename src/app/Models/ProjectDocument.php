<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasFileMetadata;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'project_id', 'user_id', 'original_name', 'path', 'mime', 'size',
])]
class ProjectDocument extends Model
{
    use BelongsToCompany, HasFileMetadata;

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
