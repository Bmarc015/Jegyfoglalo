<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tickets;
    public $orderRef;
    public $orderTotal;
    protected $pdfAttachments;

    public function __construct($tickets, $pdfAttachments, $orderRef, $orderTotal)
    {
        $this->tickets = $tickets;
        $this->pdfAttachments = $pdfAttachments;
        $this->orderRef = $orderRef;
        $this->orderTotal = $orderTotal;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your tickets are ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket',
        );
    }

    public function attachments(): array
    {
        return array_map(function ($item) {
            return Attachment::fromData(fn () => $item['content'], $item['filename'])
                ->withMime('application/pdf');
        }, $this->pdfAttachments);
    }
}
