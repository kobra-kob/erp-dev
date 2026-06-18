<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Ordonnances optiques (module Opticien). */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('prescriber')->nullable();   // ophtalmologiste
            $table->date('prescribed_at');
            // Œil droit (OD)
            $table->decimal('od_sphere', 5, 2)->nullable();
            $table->decimal('od_cylinder', 5, 2)->nullable();
            $table->unsignedSmallInteger('od_axis')->nullable();   // 0-180
            $table->decimal('od_addition', 4, 2)->nullable();
            // Œil gauche (OG)
            $table->decimal('og_sphere', 5, 2)->nullable();
            $table->decimal('og_cylinder', 5, 2)->nullable();
            $table->unsignedSmallInteger('og_axis')->nullable();
            $table->decimal('og_addition', 4, 2)->nullable();
            $table->unsignedSmallInteger('pupillary_distance')->nullable(); // écart pupillaire (mm)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'prescribed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
