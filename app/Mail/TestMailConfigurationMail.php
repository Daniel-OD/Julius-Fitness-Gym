<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Test email sent from admin Settings to verify mail transport configuration.
 */
class TestMailConfigurationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $gymName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.settings.mail.test_subject', ['gym' => $this->gymName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test-configuration',
            with: [
                'gymName' => $this->gymName,
            ],
        );
    }
}
