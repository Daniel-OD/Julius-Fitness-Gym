<?php

use App\Jobs\SendSubscriptionExpiringEmail;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
    Carbon::setTestNow(Carbon::parse('2026-06-07 09:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function makeActiveSubscriptionExpiringIn(int $days, array $memberOverrides = [], string $status = 'ongoing'): Subscription
{
    $member = Member::factory()->create([
        'email' => 'member@example.com',
        'status' => 'active',
        ...$memberOverrides,
    ]);

    $plan = Plan::factory()->create(['days' => 30, 'status' => 'active']);

    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(30)->toDateString(),
        'end_date' => Carbon::today()->addDays($days)->toDateString(),
        'status' => $status,
    ]);
}

it('dispatches a job for a subscription expiring in exactly 7 days', function (): void {
    $subscription = makeActiveSubscriptionExpiringIn(7);

    $this->artisan('gym:send-expiring-emails')->assertSuccessful();

    Queue::assertPushed(SendSubscriptionExpiringEmail::class, function (SendSubscriptionExpiringEmail $job) use ($subscription): bool {
        return $job->subscription->id === $subscription->id
            && $job->daysLeft === 7;
    });
});

it('dispatches a job for a subscription expiring in exactly 3 days', function (): void {
    $subscription = makeActiveSubscriptionExpiringIn(3, [], 'expiring');

    $this->artisan('gym:send-expiring-emails')->assertSuccessful();

    Queue::assertPushed(SendSubscriptionExpiringEmail::class, function (SendSubscriptionExpiringEmail $job) use ($subscription): bool {
        return $job->subscription->id === $subscription->id
            && $job->daysLeft === 3;
    });
});

it('does not dispatch a job for a subscription expiring in 5 days', function (): void {
    makeActiveSubscriptionExpiringIn(5);

    $this->artisan('gym:send-expiring-emails')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not dispatch a job for an expired subscription', function (): void {
    $member = Member::factory()->create(['email' => 'member@example.com', 'status' => 'active']);
    $plan = Plan::factory()->create(['days' => 30, 'status' => 'active']);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(60)->toDateString(),
        'end_date' => Carbon::today()->subDays(1)->toDateString(),
        'status' => 'expired',
    ]);

    $this->artisan('gym:send-expiring-emails')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('skips subscriptions whose member has no email without throwing', function (): void {
    makeActiveSubscriptionExpiringIn(7, ['email' => null]);

    $this->artisan('gym:send-expiring-emails')->assertSuccessful();

    Queue::assertNothingPushed();
});
