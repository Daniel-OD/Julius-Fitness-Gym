<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\Subscription;
use App\Support\AppConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarkSubscriptionsStatus extends Command
{
    protected $signature = 'gym:subscriptions
                            {--mark-expired : Mark expired subscriptions}
                            {--mark-expiring : Mark subscriptions expiring within the configured window}';

    protected $description = 'Mark subscriptions as expiring or expired';

    public function handle(): int
    {
        $timezone = AppConfig::timezone();
        $today = Carbon::today($timezone);
        $expiringDays = Helpers::getSubscriptionExpiringDays();
        $expiringThreshold = $today->copy()->addDays($expiringDays);

        $summary = [];
        $runExpiredOnly = (bool) $this->option('mark-expired');
        $runExpiringOnly = (bool) $this->option('mark-expiring');
        $runAll = ! $runExpiredOnly && ! $runExpiringOnly;

        if ($runAll || $runExpiredOnly) {
            $expiredCount = Subscription::query()
                ->whereDate('end_date', '<', $today)
                ->whereNotIn('status', ['expired', 'renewed'])
                ->whereDoesntHave('renewals')
                ->update(['status' => 'expired']);

            if ($expiredCount > 0) {
                $summary[] = "{$expiredCount} expired";
            }

            $renewedCount = Subscription::query()
                ->whereDate('end_date', '<', $today)
                ->where('status', '!=', 'renewed')
                ->whereHas('renewals')
                ->update(['status' => 'renewed']);

            if ($renewedCount > 0) {
                $summary[] = "{$renewedCount} renewed";
            }
        }

        if ($runAll) {
            $upcomingCount = Subscription::query()
                ->whereDate('start_date', '>', $today)
                ->whereNotIn('status', ['renewed', 'upcoming'])
                ->update(['status' => 'upcoming']);

            if ($upcomingCount > 0) {
                $summary[] = "{$upcomingCount} upcoming";
            }
        }

        if ($runAll || $runExpiringOnly) {
            $expiringCount = Subscription::query()
                ->whereDate('start_date', '<=', $today)
                ->whereBetween('end_date', [$today->toDateString(), $expiringThreshold->toDateString()])
                ->whereNotIn('status', ['renewed', 'expiring', 'expired'])
                ->update(['status' => 'expiring']);

            if ($expiringCount > 0) {
                $summary[] = "{$expiringCount} expiring (≤ {$expiringDays} days)";
            }
        }

        if ($runAll) {
            $ongoingCount = Subscription::query()
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>', $expiringThreshold)
                ->whereNotIn('status', ['ongoing', 'expired', 'renewed'])
                ->update(['status' => 'ongoing']);

            if ($ongoingCount > 0) {
                $summary[] = "{$ongoingCount} ongoing";
            }
        }

        if (empty($summary)) {
            $this->info('No subscription statuses needed updating.');

            return self::SUCCESS;
        }

        foreach ($summary as $line) {
            $this->info("• {$line}");
        }

        return self::SUCCESS;
    }
}
