<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Console de support (instance d'administration multi-tenant).
 *
 * - support_users     : comptes super-admin, HORS de la table `users` (aucun
 *                       company_id) → ne peuvent jamais entrer en collision avec
 *                       le CompanyScope. Guard dédié « support ».
 * - support_audit_logs: journal de toute action effectuée depuis la console.
 * - companies.status  : permet de suspendre un tenant à distance.
 * - users.last_seen_at: présence (« tenants connectés ») pour la console.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            // Double authentification (réutilise la même mécanique que les users).
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('support_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_user_id')->nullable()->constrained('support_users')->nullOnDelete();
            $table->string('action');                       // ex. login, tenant.suspend, impersonate
            $table->foreignId('company_id')->nullable();    // tenant concerné (pas de FK : on garde l'historique même après suppression)
            $table->string('target_type')->nullable();      // modèle ciblé éventuel
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('description')->nullable();
            $table->json('properties')->nullable();         // payload libre (avant/après…)
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'created_at']);
            $table->index('action');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('status')->default('active')->after('subscription'); // active | suspended
            $table->timestamp('suspended_at')->nullable()->after('status');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('is_active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['status', 'suspended_at', 'suspension_reason']);
        });

        Schema::dropIfExists('support_audit_logs');
        Schema::dropIfExists('support_users');
    }
};
