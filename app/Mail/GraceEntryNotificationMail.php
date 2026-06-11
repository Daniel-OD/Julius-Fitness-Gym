<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Admin notification email sent when an expired member uses the grace entry.
 */
class GraceEntryNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $memberName,
        public readonly string $memberCode,
        public readonly string $planName,
        public readonly string $expiredOn,
        public readonly string $scannedAt,
        public readonly string $gymName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Intrare cu abonament expirat — {$this->gymName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.checkins.grace-entry',
        );
    }
}
