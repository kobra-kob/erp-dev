<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('brand_color', 7)->default('#2563eb')->after('logo');   // couleur principale
            $table->string('brand_accent', 7)->default('#1f2937')->after('brand_color'); // bandeau / accents
            $table->string('document_shape', 20)->default('rounded')->after('brand_accent'); // rounded | square
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['brand_color', 'brand_accent', 'document_shape']);
        });
    }
};
