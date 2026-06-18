<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Taux de TVA des dépenses (pour la TVA déductible). Montant = TTC. */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->default(20)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vat_rate');
        });
    }
};
