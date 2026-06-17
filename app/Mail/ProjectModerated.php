<?php

namespace App\Mail;

use App\Models\ModerationAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectModerated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public User $user,
        public ModerationAction $action,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Votre projet \"{$this->project->name}\" a été archivé",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project-moderated',
        );
    }
}