<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Active les modules du socle pour toutes les entreprises EXISTANTES, afin que
 * le nouveau contrôle d'activation par entreprise ne leur retire pas l'accès.
 * Les nouvelles entreprises sont gérées par l'évènement « created » du modèle.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Clés du socle activables (hors « settings » obligatoire).
        $coreKeys = [
            'clients', 'quotes', 'invoices', 'planning', 'projects', 'stock',
            'expenses', 'employees', 'documents', 'leaves', 'accounting',
            'assistant', 'statistics',
        ];
        $now = now();

        DB::table('companies')->orderBy('id')->pluck('id')->each(function ($companyId) use ($coreKeys, $now) {
            foreach ($coreKeys as $key) {
                DB::table('company_modules')->updateOrInsert(
                    ['company_id' => $companyId, 'module_key' => $key],
                    ['active' => true, 'activated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
                );
            }
        });
    }

    public function down(): void
    {
        // Backfill non réversible (on ne sait pas distinguer les activations manuelles).
    }
};
