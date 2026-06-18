<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifie l'entreprise de la réponse du client à un devis. */
class QuoteAnsweredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote, public ?string $invoiceNumber = null) {}

    public function envelope(): Envelope
    {
        $verb = $this->quote->status === 'accepted' ? 'accepté' : 'refusé';

        return new Envelope(subject: 'Devis ' . $this->quote->number . ' ' . $verb . ' par le client');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.quote-answered');
    }
}
