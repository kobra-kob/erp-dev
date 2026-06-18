<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Modules optionnels (verticaux) activés par entreprise. */
    public function up(): void
    {
        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('module_key');
            $table->boolean('active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_modules');
    }
};
