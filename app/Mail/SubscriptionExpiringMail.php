<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Member-facing email notifying that their subscription is expiring soon.
 */
class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  non-empty-string  $gymName
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $subjectLine,
        public readonly string $gymName,
        public readonly string $gymEmail,
        public readonly string $gymContact,
        public readonly string $memberName,
        public readonly int $daysLeft,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscriptions.expiring',
            with: [
                'subscription' => $this->subscription,
                'gymName' => $this->gymName,
                'gymEmail' => $this->gymEmail,
                'gymContact' => $this->gymContact,
                'memberName' => $this->memberName,
                'daysLeft' => $this->daysLeft,
            ],
        );
    }
}
