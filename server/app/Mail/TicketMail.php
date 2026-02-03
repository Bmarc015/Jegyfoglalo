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

    public $ticket;
    protected $pdfContent;

    public function __construct($ticket, $pdfContent)
    {
        $this->ticket = $ticket;
        $this->pdfContent = $pdfContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sikeres Jegyvásárlás - Hala Madrid!',
        );
    }

    public function content(): Content
    {
        return new Content(
            // ITT ADJUK MEG AZ E-MAIL DIZÁJN HELYÉT:
            view: 'emails.ticket', 
        );
    }

    public function attachments(): array
    {
        return [
            // ITT CSATOLJUK A GENERÁLT PDF-ET:
            Attachment::fromData(fn () => $this->pdfContent, 'Real_Madrid_Jegy.pdf')
                ->withMime('application/pdf'),
        ];
    }
}