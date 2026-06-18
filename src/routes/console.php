<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Relances automatiques des factures échues impayées, chaque jour à 8h.
// Nécessite un planificateur : `php artisan schedule:work` (ou un cron appelant `schedule:run`).
Schedule::command('invoices:send-reminders')->dailyAt('08:00');
