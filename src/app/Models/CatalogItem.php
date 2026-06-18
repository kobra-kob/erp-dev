<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id', 'trade', 'line_type', 'label', 'unit', 'unit_price', 'tax_rate',
])]
class CatalogItem extends Model
{
    use BelongsToCompany;

    public const TRADES = [
        'general'     => 'Général',
        'electricien' => 'Électricien',
        'plombier'    => 'Plombier',
        'peintre'     => 'Peintre',
    ];

    public const LINE_TYPES = [
        'main_oeuvre' => "Main d'œuvre",
        'materiel'    => 'Matériel',
        'deplacement' => 'Déplacement',
        'autre'       => 'Autre',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'tax_rate'   => 'decimal:2',
        ];
    }

    public function tradeLabel(): string
    {
        return self::TRADES[$this->trade] ?? 'Général';
    }

    /** Libellé de la nature de ligne (main d'œuvre, matériel…). */
    public function lineTypeLabel(): string
    {
        return self::LINE_TYPES[$this->line_type] ?? 'Autre';
    }
}
