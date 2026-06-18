<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Bibliothèque de prestations (module Bâtiment) réutilisables dans les devis. */
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('trade')->default('general'); // electricien|plombier|peintre|general
            $table->string('line_type')->default('main_oeuvre'); // type de ligne de devis
            $table->string('label');
            $table->string('unit')->default('u');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(20);
            $table->timestamps();

            $table->index(['company_id', 'trade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
