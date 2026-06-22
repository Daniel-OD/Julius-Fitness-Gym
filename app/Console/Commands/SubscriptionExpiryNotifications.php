<?php

namespace App\Console\Commands;

use App\Contracts\SettingsRepository;
use App\Jobs\SendSubscriptionExpiryNotification;
use App\Jobs\SendWhatsAppExpiryNotification;
use App\Support\Subscriptions\ExpiringSubscriptionsQuery;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Finds subscriptions expiring in exactly 7, 3, 1, or 0 days and dispatches
 * notification jobs for each trigger day.
 *
 * Deduplication: a cache key per subscription+day prevents double-firing
 * if the command is accidentally run multiple times on the same day.
 */
#[Description('Send expiry notifications for subscriptions at 7, 3, 1, and 0 days')]
#[Signature('gym:subscription-expiry-notifications
                            {--dry-run : List matching subscriptions without dispatching}')]
class SubscriptionExpiryNotifications extends Command
{
    /** @var list<int> */
    private const array TRIGGER_DAYS = [7, 3, 1, 0];

    public function handle(SettingsRepository $settingsRepository): int
    {
        $settings = $settingsRepository->get();
        $whatsAppEnabled = (bool) data_get($settings, 'notifications.whatsapp.enabled', false);
        $today = ExpiringSubscriptionsQuery::today();
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach (self::TRIGGER_DAYS as $days) {
            $subscriptions = ExpiringSubscriptionsQuery::dueOn($days)
                ->with(['member', 'plan'])
                ->whereNotIn('status', ['renewed', 'cancelled'])
                ->get();

            foreach ($subscriptions as $subscription) {
                $cacheKey = "sub_expiry_notified:{$subscription->id}:{$days}:{$today->toDateString()}";

                if (Cache::has($cacheKey)) {
                    continue;
                }

                $memberName = $subscription->member->name ?? "#{$subscription->id}";

                if ($dryRun) {
                    $this->line("  [dry-run] subscription #{$subscription->id} ({$memberName}) — {$days} days left");
                } else {
                    SendSubscriptionExpiryNotification::dispatch($subscription->id, $days)->afterCommit();

                    if ($whatsAppEnabled) {
                        SendWhatsAppExpiryNotification::dispatch($subscription->id, $days)->afterCommit();
                    }

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
