<?php

namespace App\Models\Concerns;

/**
 * Génère un numéro séquentiel par entreprise et par année : PREFIX-AAAA-NNN.
 * Le modèle hôte doit définir la constante NUMBER_PREFIX (ex. 'DEV', 'FAC').
 */
trait GeneratesDocumentNumber
{
    public static function nextNumber(int $companyId): string
    {
        $prefix = static::NUMBER_PREFIX;
        $year   = now()->year;

        $last = static::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        $seq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;

        return sprintf('%s-%d-%03d', $prefix, $year, $seq);
    }
}
