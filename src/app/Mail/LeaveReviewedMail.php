<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifie l'employé de la décision sur sa demande de congés. */
class LeaveReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leave) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre demande de congés a été ' . mb_strtolower($this->leave->statusLabel()));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.leave-reviewed');
    }
}
