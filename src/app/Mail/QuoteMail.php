<?php

namespace App\Mail;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Devis ' . $this->quote->number . ' — ' . $this->quote->company?->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.quote');
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.quote', ['quote' => $this->quote]);

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->quote->number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
