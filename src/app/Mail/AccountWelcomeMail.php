<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmation de création de compte. */
class AccountWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public bool $createdByAdmin = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre compte ' . config('app.name') . ' est prêt');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.account-welcome');
    }
}
