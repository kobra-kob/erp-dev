<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** Jeton public pour la validation du devis par le client (sans connexion). */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('status');
        });

        // Backfill des devis existants.
        DB::table('quotes')->whereNull('public_token')->orderBy('id')->pluck('id')->each(function ($id) {
            DB::table('quotes')->where('id', $id)->update(['public_token' => Str::random(40)]);
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
