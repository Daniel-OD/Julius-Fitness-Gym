<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CheckIns\CheckInService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not grant check-in access for a pending_payment subscription', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDay()->toDateString(),
        'end_date' => Carbon::today()->addDays(29)->toDateString(),
        'status' => 'pending_payment',
    ]);

    $subscription = app(CheckInService::class)->activeSubscriptionFor($member->id);

    expect($subscription)->toBeNull();
});

it('grants check-in access for an ongoing subscription within its date range', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();

    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDay()->toDateString(),
        'end_date' => Carbon::today()->addDays(29)->toDateString(),
        'status' => 'ongoing',
    ]);

    $found = app(CheckInService::class)->activeSubscriptionFor($member->id);

    expect($found?->id)->toBe($subscription->id);
});
