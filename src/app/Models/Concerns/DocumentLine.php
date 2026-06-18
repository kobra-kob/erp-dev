<?php

namespace App\Models\Concerns;

/**
 * Comportement commun aux lignes de devis et de factures.
 */
trait DocumentLine
{
    /** Libellés des natures de prestation. */
    public const TYPES = [
        'main_oeuvre' => "Main d'œuvre",
        'materiel'    => 'Matériel',
        'deplacement' => 'Déplacement',
        'autre'       => 'Autre',
    ];

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Autre';
    }

    /** Montant HT de la ligne. */
    public function lineHt(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    /** Montant de TVA de la ligne. */
    public function lineTax(): float
    {
        return round($this->lineHt() * (float) $this->tax_rate / 100, 2);
    }

    /** Montant TTC de la ligne. */
    public function lineTtc(): float
    {
        return round($this->lineHt() + $this->lineTax(), 2);
    }
}
