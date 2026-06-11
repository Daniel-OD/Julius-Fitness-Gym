<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePlan(int $days = 30, float $amount = 150.0): Plan
{
    return Plan::factory()->create(['days' => $days, 'amount' => $amount, 'status' => 'active']);
}

function makeMember(): Member
{
    return Member::factory()->create(['status' => 'active']);
}

// ─── Model ───────────────────────────────────────────────────────────────────

it('stores type and internal_note fields', function (): void {
    $sub = Subscription::factory()->create([
        'member_id' => makeMember()->id,
        'plan_id' => makePlan()->id,
        'type' => 'internal',
        'internal_note' => 'Private arrangement',
    ]);

    $sub->refresh();
    expect($sub->type)->toBe('internal')
        ->and($sub->internal_note)->toBe('Private arrangement');
});

it('isOfficial() returns true for official subscriptions', function (): void {
    $sub = Subscription::factory()->create([
        'member_id' => makeMember()->id,
        'plan_id' => makePlan()->id,
        'type' => 'official',
    ]);

    expect($sub->isOfficial())->toBeTrue();
});

it('soft-deletes subscription', function (): void {
    $sub = Subscription::factory()->create([
        'member_id' => makeMember()->id,
        'plan_id' => makePlan()->id,
    ]);
    $id = $sub->id;

    $sub->delete();

    expect(Subscription::find($id))->toBeNull()
        ->and(Subscription::withTrashed()->find($id))->not->toBeNull();
});

it('member hasMany subscriptions relation works', function (): void {
    $member = makeMember();
    $plan = makePlan();

    Subscription::factory()->count(3)->create(['member_id' => $member->id, 'plan_id' => $plan->id]);

    expect($member->subscriptions()->count())->toBe(3);
});

// ─── Dual visibility scope ────────────────────────────────────────────────────

it('admin panel scope excludes internal subscriptions', function (): void {
    $member = makeMember();
    $plan = makePlan();

    $official = Subscription::factory()->create(['member_id' => $member->id, 'plan_id' => $plan->id, 'type' => 'official']);
    $internal = Subscription::factory()->create(['member_id' => $member->id, 'plan_id' => $plan->id, 'type' => 'internal']);

    // Simulate admin panel scope
    $ids = Subscription::where('type', 'official')->pluck('id');

    expect($ids)->toContain($official->id)
        ->and($ids)->not->toContain($internal->id);
});

it('office panel sees all subscription types', function (): void {
    $member = makeMember();
    $plan = makePlan();

    $official = Subscription::factory()->create(['member_id' => $member->id, 'plan_id' => $plan->id, 'type' => 'official']);
    $internal = Subscription::factory()->create(['member_id' => $member->id, 'plan_id' => $plan->id, 'type' => 'internal']);

    $ids = Subscription::pluck('id');

    expect($ids)->toContain($official->id)
        ->and($ids)->toContain($internal->id);
});

// ─── Status transitions ───────────────────────────────────────────────────────

it('expired subscriptions are found by MarkSubscriptionsStatus command', function (): void {
    $member = makeMember();
    $plan = makePlan();

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(40)->toDateString(),
        'end_date' => Carbon::today()->subDays(5)->toDateString(),
        'status' => 'ongoing',
    ]);

    $this->artisan('gym:subscriptions --mark-expired')->assertSuccessful();

    expect(Subscription::where('member_id', $member->id)->where('status', 'expired')->exists())->toBeTrue();
});

it('ongoing subscriptions far from expiry are not marked expiring', function (): void {
    $member = makeMember();
    $plan = makePlan(365);

    $sub = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(30)->toDateString(),
        'end_date' => Carbon::today()->addDays(200)->toDateString(),
        'status' => 'ongoing',
    ]);

    $this->artisan('gym:subscriptions --mark-expiring')->assertSuccessful();

    expect($sub->fresh()->status->value)->toBe('ongoing');
});
