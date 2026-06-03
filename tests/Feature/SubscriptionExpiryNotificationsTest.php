<?php

use App\Contracts\SettingsRepository;
use App\Jobs\SendSubscriptionExpiryNotification;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiryNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Bus::fake();
    Cache::flush();
});

function makeSubscriptionExpiringIn(int $days): Subscription
{
    $member = Member::factory()->create(['status' => 'active']);
    $plan = Plan::factory()->create(['days' => 30, 'status' => 'active']);

    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(30)->toDateString(),
        'end_date' => Carbon::today()->addDays($days)->toDateString(),
        'status' => $days === 0 ? 'expired' : 'ongoing',
    ]);
}

it('dispatches a job for a subscription expiring in 7 days', function (): void {
    $subscription = makeSubscriptionExpiringIn(7);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertDispatched(SendSubscriptionExpiryNotification::class, function ($job) use ($subscription): bool {
        return $job->subscriptionId === $subscription->id && $job->daysLeft === 7;
    });
});

it('dispatches a job for a subscription expiring in 3 days', function (): void {
    $subscription = makeSubscriptionExpiringIn(3);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertDispatched(SendSubscriptionExpiryNotification::class, function ($job) use ($subscription): bool {
        return $job->subscriptionId === $subscription->id && $job->daysLeft === 3;
    });
});

it('dispatches a job for a subscription expiring in 1 day', function (): void {
    $subscription = makeSubscriptionExpiringIn(1);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertDispatched(SendSubscriptionExpiryNotification::class, function ($job) use ($subscription): bool {
        return $job->subscriptionId === $subscription->id && $job->daysLeft === 1;
    });
});

it('dispatches a job for a subscription expiring today (day 0)', function (): void {
    $subscription = makeSubscriptionExpiringIn(0);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertDispatched(SendSubscriptionExpiryNotification::class, function ($job) use ($subscription): bool {
        return $job->subscriptionId === $subscription->id && $job->daysLeft === 0;
    });
});

it('does not dispatch for subscriptions expiring in 2 days (not a trigger)', function (): void {
    makeSubscriptionExpiringIn(2);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertNothingDispatched();
});

it('does not dispatch twice for the same subscription on the same day', function (): void {
    $subscription = makeSubscriptionExpiringIn(7);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();
    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertDispatchedTimes(SendSubscriptionExpiryNotification::class, 1);
});

it('does not dispatch for renewed subscriptions', function (): void {
    $member = Member::factory()->create(['status' => 'active']);
    $plan = Plan::factory()->create(['days' => 30, 'status' => 'active']);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(30)->toDateString(),
        'end_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'renewed',
    ]);

    $this->artisan('gymie:subscription-expiry-notifications')->assertSuccessful();

    Bus::assertNothingDispatched();
});

it('sends in-app notification to all admin users when job is handled', function (): void {
    Notification::fake();
    Mail::fake();

    $user = User::factory()->create();
    $subscription = makeSubscriptionExpiringIn(3);
    $subscription->load(['member', 'plan']);

    $job = new SendSubscriptionExpiryNotification($subscription->id, 3);
    $job->handle(app(SettingsRepository::class));

    Notification::assertSentTo(
        $user,
        SubscriptionExpiryNotification::class,
        fn ($n): bool => $n->daysLeft === 3
    );
});

it('dry-run lists subscriptions without dispatching', function (): void {
    makeSubscriptionExpiringIn(7);

    $this->artisan('gymie:subscription-expiry-notifications --dry-run')->assertSuccessful();

    Bus::assertNothingDispatched();
});
