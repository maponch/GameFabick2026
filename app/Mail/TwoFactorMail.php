<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $otp) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Code de connexion GameFabrick');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor',
            with: ['otp' => $this->otp],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}