<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionExpiringEmail;
use App\Models\Subscription;
use App\Support\AppConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Dispatch member-facing expiring subscription emails at 7 and 3 days before end_date.
 */
class SendSubscriptionExpiringEmails extends Command
{
    protected $signature = 'gym:send-expiring-emails';

    protected $description = 'Trimite emailuri de notificare pentru abonamentele care expiră în 7 sau 3 zile';

    /** @var list<int> */
    private const TRIGGER_DAYS = [7, 3];

    public function handle(): int
    {
        $timezone = AppConfig::timezone();
        $today = Carbon::today($timezone);
        $dispatched = 0;
        $skipped = 0;
        $processedIds = [];

        foreach (self::TRIGGER_DAYS as $daysLeft) {
            $targetDate = $today->copy()->addDays($daysLeft)->toDateString();

            $subscriptions = Subscription::query()
                ->with('member')
                ->whereIn('status', ['ongoing', 'expiring'])
                ->whereDate('end_date', $targetDate)
                ->get();

            foreach ($subscriptions as $subscription) {
                if (in_array($subscription->id, $processedIds, true)) {
                    continue;
                }

                $processedIds[] = $subscription->id;

                $member = $subscription->member;
                $memberEmail = is_string($member?->email) ? $member->email : '';

                if (! filter_var($memberEmail, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    $this->warn("Skipped subscription #{$subscription->id}: member has no valid email.");

                    continue;
                }

                dispatch(new SendSubscriptionExpiringEmail($member, $subscription, $daysLeft));
                $dispatched++;
            }
        }

        $this->info("Dispatched {$dispatched} expiring email job(s).");

        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} subscription(s) without a valid member email.");
        }

        if ($dispatched === 0 && $skipped === 0) {
            $this->info('No active subscriptions match the 7- or 3-day expiry triggers today.');
        }

        Log::info('gym:send-expiring-emails finished', [
            'dispatched' => $dispatched,
            'skipped' => $skipped,
        ]);

        return self::SUCCESS;
    }
}
