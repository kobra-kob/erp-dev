<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Biens immobiliers (module Immobilier). */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('title');
            $table->string('type')->default('appartement');   // appartement|maison|terrain|local|autre
            $table->string('transaction')->default('vente');  // vente|location
            $table->string('status')->default('disponible');  // disponible|sous_offre|vendu|loue
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('surface', 10, 2)->nullable();     // m²
            $table->unsignedSmallInteger('rooms')->nullable(); // nombre de pièces
            $table->string('dpe', 1)->nullable();              // A..G
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('owner_name')->nullable();          // mandant / propriétaire
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'transaction', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
