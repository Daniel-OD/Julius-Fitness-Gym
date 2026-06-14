<?php

namespace App\Support\Subscriptions;

use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for the timezone-aware date logic shared by the
 * subscription-expiry features (member emails, admin alerts, panel bell).
 *
 * Callers add their own status filters, eager loads and ordering — those rules
 * intentionally differ per feature and stay explicit at the call site.
 */
final class ExpiringSubscriptionsQuery
{
    /**
     * Subscriptions whose end_date falls exactly $daysAhead days from today.
     *
     * @return Builder<Subscription>
     */
    public static function dueOn(int $daysAhead): Builder
    {
        $targetDate = self::today()->addDays($daysAhead)->toDateString();

        return Subscription::query()->whereDate('end_date', $targetDate);
    }

    /**
     * Currently-running subscriptions whose end_date falls within the next $days days.
     *
     * @return Builder<Subscription>
     */
    public static function dueWithin(int $days): Builder
    {
        $today = self::today();

        return Subscription::query()
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereDate('end_date', '<=', $today->addDays($days)->toDateString());
    }

    /**
     * Signed number of days until the subscription's end_date (negative once past).
     */
    public static function daysLeft(Subscription $subscription): int
    {
        $endDate = CarbonImmutable::parse($subscription->end_date, AppConfig::timezone())->startOfDay();

        return (int) self::today()->diffInDays($endDate, false);
    }

    /**
     * Timezone-aware start of today, shared by every expiry feature.
     */
    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::today(AppConfig::timezone());
    }
}
