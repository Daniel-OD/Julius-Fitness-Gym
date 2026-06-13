<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Services\Members\MemberStatusSyncService;

/**
 * Subscription observer.
 *
 * Keeps the owning member's active/inactive status in sync whenever a
 * subscription is created, updated or removed.
 */
class SubscriptionObserver
{
    public function __construct(private readonly MemberStatusSyncService $statusSync) {}

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
