<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Relie une ligne de devis/facture à un produit du stock (nullable : les lignes
 * de prestation libre n'ont pas de produit). Permet le décompte du stock à la
 * facturation et le contrôle de disponibilité.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['quote_lines', 'invoice_lines'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('product_id')->nullable()->after('id')->constrained('products')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['quote_lines', 'invoice_lines'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('product_id');
            });
        }
    }
};
