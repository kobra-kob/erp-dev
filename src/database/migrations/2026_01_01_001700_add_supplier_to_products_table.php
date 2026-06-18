<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Infos fournisseur + quantité de réapprovisionnement (commande mini).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('supplier_name')->nullable()->after('notes');
            $table->string('supplier_email')->nullable()->after('supplier_name');
            $table->decimal('reorder_quantity', 12, 2)->default(0)->after('supplier_email');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['supplier_name', 'supplier_email', 'reorder_quantity']);
        });
    }
};
