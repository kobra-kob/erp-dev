<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('number')->index();              // DEV-2026-001
            $table->string('status')->default('draft');     // draft|sent|accepted|refused|expired
            $table->string('title')->nullable();
            $table->date('issue_date');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            // Totaux figés au moment de l'enregistrement (recalculés depuis les lignes).
            $table->decimal('subtotal_ht', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
