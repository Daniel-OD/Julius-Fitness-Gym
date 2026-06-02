<?php

use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// ─── helpers ────────────────────────────────────────────────────────────────

function memberWithToken(?string $token = null): Member
{
    return Member::factory()->create([
        'checkin_token' => $token ?? Str::random(32),
        'status' => 'active',
    ]);
}

function activeSubscription(Member $member): Subscription
{
    $plan = Plan::factory()->create(['days' => 30, 'status' => 'active']);

    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(5)->toDateString(),
        'end_date' => Carbon::today()->addDays(25)->toDateString(),
        'status' => 'ongoing',
        'type' => 'official',
    ]);
}

// ─── DUAL VISIBILITY ────────────────────────────────────────────────────────

it('official subscriptions are visible to all panels', function (): void {
    $member = memberWithToken();
    $sub = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => Plan::factory()->create()->id,
        'type' => 'official',
    ]);

    expect(Subscription::where('type', 'official')->pluck('id'))->toContain($sub->id);
});

it('internal subscriptions are filtered from admin panel query', function (): void {
    $member = memberWithToken();
    $plan = Plan::factory()->create();

    $internal = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'type' => 'internal',
    ]);

    // Simulate /admin panel scope
    $query = Subscription::query()->where('type', 'official');

    expect($query->pluck('id'))->not->toContain($internal->id);
});

it('subscription model stores type and internal_note', function (): void {
    $member = memberWithToken();
    $plan = Plan::factory()->create();

    $sub = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'type' => 'internal',
        'internal_note' => 'Test internal note',
    ]);

    $sub->refresh();

    expect($sub->type)->toBe('internal')
        ->and($sub->internal_note)->toBe('Test internal note');
});

// ─── CHECK-IN QR ────────────────────────────────────────────────────────────

it('scan records a check-in for a valid QR token with active subscription', function (): void {
    RateLimiter::clear('checkin:'.($member = memberWithToken())->id);
    $sub = activeSubscription($member);

    $response = $this->get("/checkin/{$member->checkin_token}");

    $response->assertStatus(200);
    expect(CheckIn::where('member_id', $member->id)->exists())->toBeTrue();
});

it('scan returns json for API clients', function (): void {
    $member = memberWithToken();
    activeSubscription($member);
    RateLimiter::clear("checkin:{$member->id}");

    $response = $this->getJson("/checkin/{$member->checkin_token}");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('member.id', $member->id);
});

it('scan returns 404 for unknown token', function (): void {
    $response = $this->getJson('/checkin/invalid-token-xyz');

    $response->assertStatus(404)
        ->assertJsonPath('status', 'error');
});

it('scan records check-in with warning when member has no active subscription', function (): void {
    $member = memberWithToken();
    RateLimiter::clear("checkin:{$member->id}");

    $response = $this->getJson("/checkin/{$member->checkin_token}");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'warning');

    expect(CheckIn::where('member_id', $member->id)->exists())->toBeTrue();
});

it('rate limits duplicate check-ins within 30 minutes', function (): void {
    $member = memberWithToken();
    activeSubscription($member);

    RateLimiter::clear("checkin:{$member->id}");

    // First scan — should succeed
    $this->getJson("/checkin/{$member->checkin_token}")->assertStatus(200);

    // Second scan immediately — rate limited
    $this->getJson("/checkin/{$member->checkin_token}")->assertStatus(429);
});

it('checkout records checked_out_at on open check-in', function (): void {
    $member = memberWithToken();
    $checkIn = CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => now()->subHour(),
        'checked_out_at' => null,
    ]);

    $response = $this->postJson("/checkin/{$member->checkin_token}/checkout");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');

    expect($checkIn->fresh()->checked_out_at)->not->toBeNull();
});

it('member auto-generates checkin_token on create', function (): void {
    $member = Member::factory()->create();

    expect($member->checkin_token)->not->toBeNull()
        ->and(strlen($member->checkin_token))->toBe(32);
});

it('check_ins table has soft deletes', function (): void {
    $member = memberWithToken();
    $checkIn = CheckIn::factory()->create(['member_id' => $member->id]);
    $id = $checkIn->id;

    $checkIn->delete();

    expect(CheckIn::find($id))->toBeNull()
        ->and(CheckIn::withTrashed()->find($id))->not->toBeNull();
});
