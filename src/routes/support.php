<?php

use App\Http\Controllers\Support\AuditLogController;
use App\Http\Controllers\Support\SupportAuthController;
use App\Http\Controllers\Support\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Console de support (super-admin multi-tenant)
|--------------------------------------------------------------------------
| Chargé uniquement sur le conteneur dont APP_ROLE=support (voir bootstrap/app.php).
| Tout est protégé par l'allowlist IP (support.ip) ; le guard « support » isole
| l'authentification de celle des tenants.
*/

Route::middleware('support.ip')->group(function () {
    Route::redirect('/', '/tenants');

    // Invités (guard support)
    Route::middleware('guest:support')->group(function () {
        Route::get('login', [SupportAuthController::class, 'create'])->name('support.login');
        Route::post('login', [SupportAuthController::class, 'store'])
            ->middleware('throttle:6,1');
    });

    // Authentifiés (guard support)
    Route::middleware('auth:support')->group(function () {
        Route::post('logout', [SupportAuthController::class, 'destroy'])->name('support.logout');

        Route::get('tenants', [TenantController::class, 'index'])->name('support.tenants.index');
        Route::get('tenants/{company}', [TenantController::class, 'show'])->name('support.tenants.show');

        Route::get('audit', [AuditLogController::class, 'index'])->name('support.audit.index');
    });
});
