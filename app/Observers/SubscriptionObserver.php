<?php

namespace App\Observers;

use App\Contracts\SettingsRepository;
use App\Jobs\SendWhatsAppWelcome;
use App\Models\Subscription;
use App\Services\Members\MemberStatusSyncService;
use App\Support\Data;

/**
 * Subscription observer.
 *
 * Keeps the owning member's active/inactive status in sync whenever a
 * subscription is created, updated or removed.
 */
class SubscriptionObserver
{
    public function __construct(
        private readonly MemberStatusSyncService $statusSync,
        private readonly SettingsRepository $settingsRepository,
    ) {}

    public function created(Subscription $subscription): void
    {
        // Do not send a welcome message for renewals (they have a parent subscription).
        if ($subscription->renewed_from_subscription_id !== null) {
            return;
        }

        $settings = $this->settingsRepository->get();
        $whatsAppEnabled = (bool) data_get($settings, 'notifications.whatsapp.enabled', false);

        if (! $whatsAppEnabled) {
            return;
        }

        $memberId = Data::int($subscription->member_id);

        if ($memberId === 0) {
            return;
        }

        SendWhatsAppWelcome::dispatch($memberId)->afterCommit();
    }

    public function saved(Subscription $subscription): void
    {
        $this->syncOwner($subscription);
    }

    public function deleted(Subscription $subscription): void
    {
        $this->syncOwner($subscription);
    }

    public function restored(Subscription $subscription): void
    {
        $this->syncOwner($subscription);
    }

    private function syncOwner(Subscription $subscription): void
    {
        $member = $subscription->member;

        if ($member === null || $member->trashed()) {
            return;
        }

        $this->statusSync->syncMember($member);
    }
}
