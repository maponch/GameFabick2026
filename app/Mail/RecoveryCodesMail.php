<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoveryCodesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $username,
        public array $codes,
        public string $adminName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nouveaux codes de secours 2FA');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recovery-codes',
            with: [
                'username'  => $this->username,
                'codes'     => $this->codes,
                'adminName' => $this->adminName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}