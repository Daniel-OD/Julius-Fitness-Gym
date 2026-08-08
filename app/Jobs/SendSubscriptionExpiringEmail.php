<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use App\Support\Data;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Send a subscription expiring email to a member (queued).
 */
class SendSubscriptionExpiringEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly Member $member,
        public readonly Subscription $subscription,
        public readonly int $daysLeft,
    ) {}

    public function handle(): void
    {
        $memberEmail = Data::string($this->member->email);

        if (! filter_var($memberEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info('Skipping subscription expiring email: member email missing.', [
                'member_id' => $this->member->id,
                'subscription_id' => $this->subscription->id,
            ]);

            return;
        }

        $this->member->notify(new SubscriptionExpiringNotification(
            subscription: $this->subscription,
            daysLeft: $this->daysLeft,
        ));
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to send subscription expiring email after all retries.', [
            'member_id' => $this->member->id,
            'subscription_id' => $this->subscription->id,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
