<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorDisabledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $username,
        public string $reason,
        public string $adminName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre double authentification a été désactivée');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-disabled',
            with: [
                'username'  => $this->username,
                'reason'    => $this->reason,
                'adminName' => $this->adminName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}