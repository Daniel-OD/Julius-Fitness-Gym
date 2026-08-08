<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionRenewalService;

it('marks the old subscription as renewed even when renewing before it expires', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 100, 'days' => 30, 'status' => 'active']);

    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => now()->subDays(5)->toDateString(),
        'end_date' => now()->addDays(25)->toDateString(),
        'status' => 'ongoing',
    ]);

    app(SubscriptionRenewalService::class)->renew($subscription, [
        'plan_id' => $plan->id,
        'start_date' => now()->addDays(25)->toDateString(),
        'end_date' => now()->addDays(55)->toDateString(),
    ]);

    expect($subscription->fresh()->status->value)->toBe('renewed');
});
