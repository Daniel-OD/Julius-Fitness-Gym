<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Admin notification email sent when a new member chooses a plan via the portal.
 */
class NewMemberNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $memberName,
        public readonly string $memberEmail,
        public readonly string $memberPhone,
        public readonly string $planName,
        public readonly string $gymName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Înregistrare nouă — {$this->gymName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.members.new-member',
        );
    }
}
