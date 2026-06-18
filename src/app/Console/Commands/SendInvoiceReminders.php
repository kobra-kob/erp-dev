<?php

namespace App\Console\Commands;

use App\Mail\InvoiceReminderMail;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Relances automatiques des factures échues impayées (toutes entreprises).
 * Planifiée quotidiennement (voir routes/console.php) et déclenchable à la main.
 */
class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';

    protected $description = 'Envoie les relances pour les factures échues impayées';

    public function handle(): int
    {
        // Sans contexte d'authentification : on ignore le CompanyScope.
        $invoices = Invoice::withoutGlobalScopes()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->with('client', 'company')
            ->get();

        $sent = 0;
        foreach ($invoices as $invoice) {
            if (! $invoice->needsReminder()) {
                continue;
            }

            if ($invoice->client?->email) {
                Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));
            }

            $invoice->markReminded();
            $sent++;
        }

        $this->info("{$sent} relance(s) traitée(s) sur {$invoices->count()} facture(s) échue(s).");

        return self::SUCCESS;
    }
}
