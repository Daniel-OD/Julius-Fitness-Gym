<?php

namespace App\Services\Subscriptions;

use App\Enums\Status;
use App\Jobs\SendSubscriptionExpiringEmail;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use App\Support\Subscriptions\ExpiringSubscriptionsQuery;
use Illuminate\Support\Carbon;

/**
 * Dispatch member-facing subscription expiring emails from admin actions.
 */
final class SubscriptionExpiringEmailService
{
    public const MANUAL_NOTIFICATION_WINDOW_DAYS = 14;

    public function isEligibleForManualNotification(Subscription $subscription): bool
    {
        if (! $this->isActiveSubscription($subscription)) {
            return false;
        }

        $daysLeft = $this->calculateDaysLeft($subscription);

        return $daysLeft >= 0 && $daysLeft <= self::MANUAL_NOTIFICATION_WINDOW_DAYS;
    }

    public function isActiveSubscription(Subscription $subscription): bool
    {
        $status = $subscription->status?->value;

        if (in_array($status, [
            Status::Expired->value,
            Status::Renewed->value,
            Status::Cancelled->value,
            Status::Upcoming->value,
        ], true)) {
            return false;
        }

        $today = Carbon::today(AppConfig::timezone());

        if (! $subscription->start_date || ! $subscription->end_date) {
            return false;
        }

        return $subscription->start_date->lte($today) && $subscription->end_date->gte($today);
    }

    public function calculateDaysLeft(Subscription $subscription): int
    {
        return ExpiringSubscriptionsQuery::daysLeft($subscription);
    }

    public function findActiveSubscriptionForMember(Member $member): ?Subscription
    {
        $today = Carbon::today(AppConfig::timezone())->toDateString();

        return $member->subscriptions()
            ->with('member')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', [
                Status::Expired->value,
                Status::Renewed->value,
                Status::Cancelled->value,
                Status::Upcoming->value,
            ])
            ->orderByDesc('end_date')
            ->first();
    }

    /**
     * @return array{sent: bool, email: ?string}
     */
    public function dispatchExpiringEmail(Subscription $subscription): array
    {
        $subscription->loadMissing('member');
        $member = $subscription->member;

        if (! $member instanceof Member) {
            return ['sent' => false, 'email' => null];
        }

        $email = is_string($member->email) ? $member->email : '';

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'email' => null];
        }

        $daysLeft = max(0, $this->calculateDaysLeft($subscription));

        dispatch(new SendSubscriptionExpiringEmail($member, $subscription, $daysLeft))->afterCommit();

        return ['sent' => true, 'email' => $email];
    }
}
