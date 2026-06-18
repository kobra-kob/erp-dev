<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table « entreprises » : racine de l'isolation multi-tenant.
     * Doit être créée avant `users` (clé étrangère company_id).
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('siret', 14)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('subscription')->default('free'); // free | pro | business
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
