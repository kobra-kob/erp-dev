<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi d'e-mails « best-effort » : une panne SMTP est journalisée mais ne fait
 * jamais échouer l'action métier (inscription, demande de congés, etc.).
 */
class Notifier
{
    /**
     * @param  string|array<int, string>  $to
     */
    public static function send($to, Mailable $mailable): void
    {
        $recipients = array_filter((array) $to);
        if (empty($recipients)) {
            return;
        }

        try {
            Mail::to($recipients)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Envoi e-mail échoué', [
                'mailable' => $mailable::class,
                'message'  => $e->getMessage(),
            ]);
        }
    }
}
