<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifie les responsables d'une nouvelle demande de congés. */
class LeaveSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leave) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nouvelle demande de congés — ' . $this->leave->user?->name);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.leave-submitted');
    }
}
