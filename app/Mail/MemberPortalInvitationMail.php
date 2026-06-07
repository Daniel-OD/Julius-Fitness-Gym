<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Member-facing email with a link to set their portal password.
 */
class MemberPortalInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  non-empty-string  $gymName
     * @param  non-empty-string  $setPasswordUrl
     */
    public function __construct(
        public readonly string $subjectLine,
        public readonly string $gymName,
        public readonly string $gymEmail,
        public readonly string $gymContact,
        public readonly string $memberName,
        public readonly string $setPasswordUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.members.portal-invitation',
            with: [
                'gymName' => $this->gymName,
                'gymEmail' => $this->gymEmail,
                'gymContact' => $this->gymContact,
                'memberName' => $this->memberName,
                'setPasswordUrl' => $this->setPasswordUrl,
            ],
        );
    }
}
