<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $username,
        public string $deletionDate
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Suppression de votre compte GameFabrick');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-deletion',
            with: [
                'username'     => $this->username,
                'deletionDate' => $this->deletionDate,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
