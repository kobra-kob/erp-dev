<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id', 'reference', 'title', 'type', 'transaction', 'status',
    'price', 'surface', 'rooms', 'dpe', 'address', 'city', 'zip', 'owner_name', 'description',
])]
class Property extends Model
{
    use BelongsToCompany;

    public const TYPES = [
        'appartement' => 'Appartement', 'maison' => 'Maison',
        'terrain' => 'Terrain', 'local' => 'Local commercial', 'autre' => 'Autre',
    ];

    public const STATUSES = [
        'disponible' => 'Disponible', 'sous_offre' => 'Sous offre',
        'vendu' => 'Vendu', 'loue' => 'Loué',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'surface' => 'decimal:2', 'rooms' => 'integer'];
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'vendu', 'loue' => 'secondary',
            'sous_offre'    => 'warning',
            default         => 'success', // disponible
        };
    }
}
