<?php

use App\Enums\Status;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function syncPlan(): Plan
{
    return Plan::factory()->create(['days' => 30, 'status' => 'active']);
}

function syncSubscription(Member $member, string $status, int $startOffsetDays, int $endOffsetDays): Subscription
{
    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => syncPlan()->id,
        'start_date' => Carbon::today()->addDays($startOffsetDays)->toDateString(),
        'end_date' => Carbon::today()->addDays($endOffsetDays)->toDateString(),
        'status' => $status,
        'type' => 'official',
    ]);
}

// ─── Observer: immediate sync on subscription changes ───────────────────────

it('activates the member when a current subscription is created', function (): void {
    $member = Member::factory()->create(['status' => 'inactive']);

    syncSubscription($member, 'ongoing', -5, 25);

    expect($member->fresh()->status)->toBe(Status::Active);
});

it('does not activate the member for a pending payment subscription', function (): void {
    $member = Member::factory()->create(['status' => 'inactive']);

    syncSubscription($member, 'pending_payment', 0, 30);

    expect($member->fresh()->status)->toBe(Status::Inactive);
});

it('deactivates the member when their only subscription is cancelled', function (): void {
    $member = Member::factory()->create(['status' => 'inactive']);
    $subscription = syncSubscription($member, 'ongoing', -5, 25);

    expect($member->fresh()->status)->toBe(Status::Active);

    $subscription->update(['status' => 'cancelled']);

    expect($member->fresh()->status)->toBe(Status::Inactive);
});

it('deactivates the member when their subscription is deleted', function (): void {
    $member = Member::factory()->create(['status' => 'inactive']);
    $subscription = syncSubscription($member, 'ongoing', -5, 25);

    $subscription->delete();

    expect($member->fresh()->status)->toBe(Status::Inactive);
});

// ─── Daily command: bulk re-bucketing ────────────────────────────────────────

it('gym:subscriptions deactivates members whose subscription expired', function (): void {
    $member = Member::factory()->create(['status' => 'active']);
    syncSubscription($member, 'ongoing', -40, -1);

    $this->artisan('gym:subscriptions')->assertSuccessful();

    expect($member->fresh()->status)->toBe(Status::Inactive);
});

it('gym:subscriptions activates members with a valid subscription', function (): void {
    $member = Member::factory()->create(['status' => 'inactive']);
    syncSubscription($member, 'ongoing', -5, 25);

    // Simulate stale data (e.g. a restored database) bypassing the observer.
    DB::table('members')->where('id', $member->id)->update(['status' => 'inactive']);

    $this->artisan('gym:subscriptions')->assertSuccessful();

    expect($member->fresh()->status)->toBe(Status::Active);
});

it('gym:subscriptions heals members with NULL status', function (): void {
    $member = Member::factory()->create(['status' => 'active']);
    DB::table('members')->where('id', $member->id)->update(['status' => null]);

    $this->artisan('gym:subscriptions')->assertSuccessful();

    expect($member->fresh()->status)->toBe(Status::Inactive);
});

// ─── Portal registration ─────────────────────────────────────────────────────

it('registers portal members as inactive until they get a subscription', function (): void {
    $this->post(route('member.register'), [
        'name' => 'Test Member',
        'email' => 'register-status@example.com',
        'contact' => '0712345678',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $member = Member::where('email', 'register-status@example.com')->first();

    expect($member)->not->toBeNull()
        ->and($member->status)->toBe(Status::Inactive);
});
