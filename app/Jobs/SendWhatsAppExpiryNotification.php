<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppExpiryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $subscriptionId,
        public readonly int $daysLeft,
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $subscription = Subscription::with(['member', 'plan'])->find($this->subscriptionId);

        if (! $subscription) {
            return;
        }

        $member = $subscription->member;

        if (! $member) {
            return;
        }

        $whatsApp->sendSubscriptionExpiry($member, $subscription, $this->daysLeft);
    }
}
