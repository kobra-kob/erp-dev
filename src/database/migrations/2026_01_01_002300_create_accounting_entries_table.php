<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Écritures comptables (en-tête). Les lignes portent débit/crédit. */
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('journal_id')->constrained('accounting_journals')->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('label');
            $table->string('reference')->nullable();        // ex. FAC-2026-001
            $table->string('source_type')->default('manual'); // invoice|payment|expense|manual
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entries');
    }
};
