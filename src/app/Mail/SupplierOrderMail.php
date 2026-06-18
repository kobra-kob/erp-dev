<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupplierOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PurchaseOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Commande de réapprovisionnement — ' . $this->order->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.supplier-order');
    }
}
