<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('notes');   // fiche produit
            $table->string('category')->nullable()->after('reference');
            $table->string('kind')->default('purchased')->after('category'); // purchased | manufactured
            $table->decimal('tax_rate', 5, 2)->default(20)->after('sale_price');
            $table->boolean('is_sellable')->default(true)->after('kind');
            $table->string('image_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'category', 'kind', 'tax_rate', 'is_sellable', 'image_path']);
        });
    }
};
