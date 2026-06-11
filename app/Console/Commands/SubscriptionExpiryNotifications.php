<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionExpiryNotification;
use App\Models\Subscription;
use App\Support\AppConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Finds subscriptions expiring in exactly 7, 3, 1, or 0 days and dispatches
 * notification jobs for each trigger day.
 *
 * Deduplication: a cache key per subscription+day prevents double-firing
 * if the command is accidentally run multiple times on the same day.
 */
class SubscriptionExpiryNotifications extends Command
{
    protected $signature = 'gym:subscription-expiry-notifications
                            {--dry-run : List matching subscriptions without dispatching}';

    protected $description = 'Send expiry notifications for subscriptions at 7, 3, 1, and 0 days';

    /** @var list<int> */
    private const TRIGGER_DAYS = [7, 3, 1, 0];

    public function handle(): int
    {
        $timezone = AppConfig::timezone();
        $today = Carbon::today($timezone);
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach (self::TRIGGER_DAYS as $days) {
            $targetDate = $today->copy()->addDays($days)->toDateString();

            $subscriptions = Subscription::query()
                ->with(['member', 'plan'])
                ->whereDate('end_date', $targetDate)
                ->whereNotIn('status', ['renewed', 'cancelled'])
                ->get();

            foreach ($subscriptions as $subscription) {
                $cacheKey = "sub_expiry_notified:{$subscription->id}:{$days}:{$today->toDateString()}";

                if (Cache::has($cacheKey)) {
                    continue;
                }

                $memberName = $subscription->member?->name ?? "#{$subscription->id}";

                if ($dryRun) {
                    $this->line("  [dry-run] subscription #{$subscription->id} ({$memberName}) — {$days} days left");
                } else {
                    SendSubscriptionExpiryNotification::dispatch($subscription->id, $days)->afterCommit();
                    Cache::put($cacheKey, true, now()->endOfDay());
                }

                $total++;
            }

            if (! $dryRun && $subscriptions->isNotEmpty()) {
                $this->info("• {$subscriptions->count()} subscriptions dispatched for {$days}-day trigger");
            }
        }

        if ($total === 0) {
            $this->info('No subscriptions match the notification triggers today.');
        }

        return self::SUCCESS;
    }
}
