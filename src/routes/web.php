<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSettingsController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CatalogItemController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ModuleCatalogController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicQuoteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'root']);

/*
|--------------------------------------------------------------------------
| Validation publique d'un devis par le client (sans authentification)
|--------------------------------------------------------------------------
*/
Route::get('devis/{token}', [PublicQuoteController::class, 'show'])->name('quotes.public');
Route::post('devis/{token}/accepter', [PublicQuoteController::class, 'accept'])->name('quotes.public.accept');
Route::post('devis/{token}/refuser', [PublicQuoteController::class, 'refuse'])->name('quotes.public.refuse');

/*
|--------------------------------------------------------------------------
| Invités (non connectés)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1'); // limitation des tentatives de connexion

    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Connectés
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/preferences', [DashboardController::class, 'updatePreferences'])->name('dashboard.preferences');

    // Modules métier : accès contrôlé par module (rôles intégrés OU personnalisés).

    Route::middleware('module:clients')->group(function () {
        Route::resource('clients', ClientController::class);
    });

    Route::middleware('module:quotes')->group(function () {
        Route::resource('quotes', QuoteController::class);
        Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
        Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToInvoice'])->name('quotes.convert');
        Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    });

    Route::middleware('module:invoices')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('invoices/{invoice}/remind', [InvoiceController::class, 'remind'])->name('invoices.remind');
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
        Route::delete('invoices/{invoice}/payments/{payment}', [PaymentController::class, 'destroy'])->name('invoices.payments.destroy');
        Route::get('exports/invoices', [ExportController::class, 'invoices'])->name('exports.invoices');
        Route::get('exports/payments', [ExportController::class, 'payments'])->name('exports.payments');
    });

    Route::middleware('module:stock')->group(function () {
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/adjust', [ProductController::class, 'adjustStock'])->name('products.adjust');
        Route::post('products/replenish-all', [ProductController::class, 'replenishAll'])->name('products.replenish-all');
        Route::post('products/{product}/replenish', [ProductController::class, 'replenish'])->name('products.replenish');
    });

    Route::middleware('module:expenses')->group(function () {
        Route::resource('expenses', ExpenseController::class)->except('show');
        Route::get('expenses/{expense}/receipt', [ExpenseController::class, 'receipt'])->name('expenses.receipt');
        Route::get('expenses/{expense}/receipt/download', [ExpenseController::class, 'receiptDownload'])->name('expenses.receipt.download');
    });

    Route::middleware('module:statistics')->group(function () {
        Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    });

    Route::middleware('module:assistant')->group(function () {
        Route::get('assistant', [AssistantController::class, 'index'])->name('assistant.index');
        Route::post('assistant/message', [AssistantController::class, 'message'])->name('assistant.message');
    });

    Route::middleware('module:accounting')->group(function () {
        // Comptabilité (partie double)
        Route::get('comptabilite', [AccountingController::class, 'index'])->name('accounting.index');
        Route::post('comptabilite/recalculer', [AccountingController::class, 'rebuild'])->name('accounting.rebuild');
        Route::get('comptabilite/plan-comptable', [AccountingController::class, 'accounts'])->name('accounting.accounts');
        Route::get('comptabilite/journal', [AccountingController::class, 'journal'])->name('accounting.journal');
        Route::get('comptabilite/grand-livre', [AccountingController::class, 'ledger'])->name('accounting.ledger');
        Route::get('comptabilite/balance', [AccountingController::class, 'balance'])->name('accounting.balance');
        Route::get('comptabilite/resultat', [AccountingController::class, 'incomeStatement'])->name('accounting.income');
        Route::get('comptabilite/bilan', [AccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');
        Route::get('comptabilite/tva', [AccountingController::class, 'vatReturn'])->name('accounting.vat');
        Route::get('comptabilite/fec', [AccountingController::class, 'fec'])->name('accounting.fec');

        // Banque (comptes, relevés, rapprochement)
        Route::get('banque', [BankController::class, 'index'])->name('bank.index');
        Route::post('banque', [BankController::class, 'store'])->name('bank.store');
        Route::get('banque/{bankAccount}', [BankController::class, 'show'])->name('bank.show');
        Route::post('banque/{bankAccount}/import', [BankController::class, 'import'])->name('bank.import');
        Route::post('banque/{bankAccount}/rapprochement', [BankController::class, 'reconcileAuto'])->name('bank.reconcile');
        Route::patch('banque/{bankAccount}/transactions/{transaction}/toggle', [BankController::class, 'toggleReconcile'])->name('bank.toggle');
    });

    Route::middleware('module:projects')->group(function () {
        Route::resource('projects', ProjectController::class);
        Route::post('projects/{project}/comments', [ProjectCommentController::class, 'store'])->name('projects.comments.store');
        Route::post('projects/{project}/documents', [ProjectDocumentController::class, 'store'])->name('projects.documents.store');
        Route::get('projects/{project}/documents/{document}', [ProjectDocumentController::class, 'show'])->name('projects.documents.show');
        Route::get('projects/{project}/documents/{document}/download', [ProjectDocumentController::class, 'download'])->name('projects.documents.download');
        Route::delete('projects/{project}/documents/{document}', [ProjectDocumentController::class, 'destroy'])->name('projects.documents.destroy');
    });

    Route::middleware('module:planning')->group(function () {
        Route::get('interventions/events', [InterventionController::class, 'events'])->name('interventions.events');
        Route::resource('interventions', InterventionController::class);
    });

    Route::middleware('module:documents')->group(function () {
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    // Gestion des employés & rôles : réservé aux ADMIN
    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::resource('roles', RoleController::class)->except('show');

        // Documents RH (contrats de travail…)
        Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
        Route::get('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'show'])->name('employees.documents.show');
        Route::get('employees/{employee}/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('employees.documents.download');
        Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employees.documents.destroy');

        // Catalogue des modules optionnels (activation par l'entreprise)
        Route::get('modules', [ModuleCatalogController::class, 'index'])->name('modules.catalog');
        Route::post('modules/{key}/toggle', [ModuleCatalogController::class, 'toggle'])->name('modules.toggle');
    });

    // Module Bâtiment : catalogue de prestations (si le module est activé)
    Route::middleware(['module:quotes', 'sector:batiment'])->group(function () {
        Route::resource('catalog', CatalogItemController::class)->except('show')->parameters(['catalog' => 'catalog']);
    });

    // Module Opticien
    Route::middleware('sector:opticien')->group(function () {
        Route::resource('prescriptions', PrescriptionController::class)->except('show');
    });

    // Module Immobilier
    Route::middleware('sector:immobilier')->group(function () {
        Route::resource('properties', PropertyController::class)->except('show');
    });

    // Module Concessionnaire
    Route::middleware('sector:concessionnaire')->group(function () {
        Route::resource('vehicles', VehicleController::class)->except('show');
    });

    // Congés : accessibles à tous les utilisateurs connectés (dont employés)
    Route::get('leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::get('leaves/create', [LeaveRequestController::class, 'create'])->name('leaves.create');
    Route::post('leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::patch('leaves/{leave}/cancel', [LeaveRequestController::class, 'cancel'])->name('leaves.cancel');
    // Validation / refus : réservés aux responsables
    Route::middleware('role:ADMIN,GERANT')->group(function () {
        Route::patch('leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::patch('leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');
    });

    // Paramètres + sécurité (2FA)
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
    Route::put('settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding.update');
    Route::post('settings/password-reset', [SettingsController::class, 'sendPasswordReset'])->name('settings.password-reset');

    Route::get('settings/two-factor', [TwoFactorSettingsController::class, 'show'])->name('two-factor.show');
    Route::post('settings/two-factor', [TwoFactorSettingsController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('settings/two-factor', [TwoFactorSettingsController::class, 'destroy'])->name('two-factor.destroy');
});
