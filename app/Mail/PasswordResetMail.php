<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email with a newly generated password and login link.
 */
class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  non-empty-string  $subjectLine
     * @param  non-empty-string  $gymName
     * @param  non-empty-string  $recipientName
     * @param  non-empty-string  $plainPassword
     * @param  non-empty-string  $loginUrl
     */
    public function __construct(
        public readonly string $subjectLine,
        public readonly string $gymName,
        public readonly string $gymEmail,
        public readonly string $gymContact,
        public readonly string $recipientName,
        public readonly string $plainPassword,
        public readonly string $loginUrl,
        public readonly string $introLine,
        public readonly string $loginButtonLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-reset',
            with: [
                'gymName' => $this->gymName,
                'gymEmail' => $this->gymEmail,
                'gymContact' => $this->gymContact,
                'recipientName' => $this->recipientName,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => $this->loginUrl,
                'introLine' => $this->introLine,
                'loginButtonLabel' => $this->loginButtonLabel,
            ],
        );
    }
}
