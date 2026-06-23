<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\Subscription;
use App\Services\Members\MemberStatusSyncService;
use App\Support\AppConfig;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Description('Mark subscriptions as expiring or expired and sync member statuses')]
#[Signature('gym:subscriptions
                            {--mark-expired : Mark expired subscriptions}
                            {--mark-expiring : Mark subscriptions expiring within the configured window}')]
class MarkSubscriptionsStatus extends Command
{
    public function handle(MemberStatusSyncService $memberStatusSync): int
    {
        $timezone = AppConfig::timezone();
        $today = Carbon::today($timezone);
        $expiringThreshold = $this->calculateExpiringThreshold($today);

        $runExpiredOnly = (bool) $this->option('mark-expired');
        $runExpiringOnly = (bool) $this->option('mark-expiring');
        $summary = [];

        if ($this->shouldRunExpired($runExpiredOnly, $runExpiringOnly)) {
            $summary = array_merge($summary, $this->processExpired($today));
        }

        if ($this->shouldRunExpiring($runExpiredOnly, $runExpiringOnly)) {
            $summary = array_merge($summary, $this->processExpiring($today, $expiringThreshold));
        }

        foreach ($summary as $line) {
            $this->info($line);
        }

        $memberStatusSync->sync();

        return Command::SUCCESS;
    }

    private function calculateExpiringThreshold(Carbon $today): Carbon
    {
        $expiringDays = Helpers::getSubscriptionExpiringDays();

        return $today->copy()->addDays($expiringDays);
    }

    private function shouldRunExpired(bool $expiredOnly, bool $expiringOnly): bool
    {
        return $expiredOnly || (! $expiredOnly && ! $expiringOnly);
    }

    private function shouldRunExpiring(bool $expiredOnly, bool $expiringOnly): bool
    {
        return $expiringOnly || (! $expiredOnly && ! $expiringOnly);
    }

    private function processExpired(Carbon $today): array
    {
        $summary = [];

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

        return $summary;
    }

    private function processExpiring(Carbon $today, Carbon $threshold): array
    {
        $summary = [];

        $expiringCount = Subscription::query()
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $threshold)
            ->where('status', '!=', 'expiring')
            ->update(['status' => 'expiring']);

        if ($expiringCount > 0) {
            $summary[] = "{$expiringCount} expiring";
        }

        return $summary;
    }
}
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
            $memberSync = $memberStatusSync->syncAll();
            if ($memberSync['activated'] > 0) {
                $summary[] = "{$memberSync['activated']} members activated";
            }
            if ($memberSync['deactivated'] > 0) {
                $summary[] = "{$memberSync['deactivated']} members deactivated";
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
