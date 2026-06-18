<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('autre'); // carburant|materiel|fournitures|sous_traitance|autre
            $table->string('label');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('spent_at');
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            // Justificatif (consultable via le visualiseur)
            $table->string('receipt_path')->nullable();
            $table->string('receipt_name')->nullable();
            $table->string('receipt_mime')->nullable();
            $table->unsignedBigInteger('receipt_size')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'spent_at']);
            $table->index(['company_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
