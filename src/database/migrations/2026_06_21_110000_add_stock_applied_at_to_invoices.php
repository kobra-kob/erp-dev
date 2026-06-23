<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marque les factures dont le décompte de stock a déjà été appliqué, afin que la
 * régularisation rétroactive (commande stock:apply-invoices) soit idempotente
 * et ne décompte jamais deux fois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('stock_applied_at')->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('stock_applied_at');
        });
    }
};
