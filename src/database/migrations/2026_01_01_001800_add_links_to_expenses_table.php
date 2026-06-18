<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Liaison des dépenses au stock (produit + quantité) et aux devis.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('project_id')->constrained('products')->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->after('product_id')->constrained('quotes')->nullOnDelete();
            $table->decimal('quantity', 12, 2)->default(0)->after('amount'); // qté pour réappro stock
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('quote_id');
            $table->dropColumn('quantity');
        });
    }
};
