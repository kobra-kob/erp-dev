<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Propriétaire (owner) de l'entreprise : l'utilisateur qui l'a créée. */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('name')->constrained('users')->nullOnDelete();
        });

        // Backfill : l'admin le plus ancien de chaque entreprise devient l'owner.
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            $owner = DB::table('users')->where('company_id', $companyId)
                ->orderByRaw("FIELD(role, 'ADMIN') DESC")->orderBy('id')
                ->value('id');
            if ($owner) {
                DB::table('companies')->where('id', $companyId)->update(['owner_id' => $owner]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
