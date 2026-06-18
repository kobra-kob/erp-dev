<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Parc de véhicules (module Concessionnaire). */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete(); // acheteur
            $table->string('vin')->nullable();                 // n° de série
            $table->string('registration')->nullable();        // immatriculation
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('mileage')->nullable();    // km
            $table->string('energy')->default('essence');      // essence|diesel|electrique|hybride|gpl
            $table->string('condition')->default('occasion');  // neuf|occasion
            $table->string('status')->default('disponible');   // disponible|reserve|vendu
            $table->decimal('price', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
