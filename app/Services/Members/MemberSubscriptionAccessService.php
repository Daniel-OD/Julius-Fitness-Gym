<?php

namespace App\Services\Members;

use App\Data\MemberSubscriptionAccess;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;

class MemberSubscriptionAccessService
{
    public function forMember(Member $member): MemberSubscriptionAccess
    {
        $today = CarbonImmutable::today(AppConfig::timezone());

        $subscription = $member->subscriptions()
            ->whereDate('start_date', '<=', $today->toDateString())
            ->orderByDesc('end_date')
            ->first();

        if (! $subscription instanceof Subscription) {
            return new MemberSubscriptionAccess(
                isActive: false,
                daysRemaining: null,
                label: __('app.members.qr.no_subscription'),
                tone: 'none',
            );
        }

        $endDate = CarbonImmutable::parse($subscription->end_date, AppConfig::timezone())->startOfDay();
        $daysRemaining = (int) $today->diffInDays($endDate, false);

        if ($daysRemaining < 0) {
            return new MemberSubscriptionAccess(
                isActive: false,
                daysRemaining: abs($daysRemaining),
                label: __('app.members.qr.expired_days_ago', ['days' => abs($daysRemaining)]),
                tone: 'expired',
            );
        }

        if ($daysRemaining === 0) {
            return new MemberSubscriptionAccess(
                isActive: true,
                daysRemaining: 0,
                label: __('app.members.qr.expires_today'),
                tone: 'active',
            );
        }

        return new MemberSubscriptionAccess(
            isActive: true,
            daysRemaining: $daysRemaining,
            label: __('app.members.qr.active_days_left', ['days' => $daysRemaining]),
            tone: 'active',
        );
    }

    public function hasActiveSubscription(Member $member): bool
    {
        return $this->forMember($member)->isActive;
    }
}
