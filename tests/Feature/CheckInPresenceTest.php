<?php

use App\Helpers\Helpers;
use App\Models\CheckIn;
use App\Models\Member;
use App\Services\CheckIns\CheckInService;
use App\Support\AppConfig;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Europe/Bucharest']);
    Carbon::setTestNow(Carbon::parse('2026-06-02 12:00:00', 'Europe/Bucharest'));
    Helpers::setTestSettingsOverride([
        'checkin' => [
            'present_now_grace_minutes' => 15,
        ],
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
    Helpers::setTestSettingsOverride(null);
});

function presenceMember(): Member
{
    return Member::factory()->create([
        'checkin_token' => Str::random(32),
        'status' => 'active',
    ]);
}

function presenceNow(): Carbon
{
    return Carbon::now(AppConfig::timezone());
}

it('includes members with open check-in in present now query', function (): void {
    $member = presenceMember();
    $now = presenceNow();

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => $now->copy()->subHours(2),
        'checked_out_at' => null,
    ]);

    expect(app(CheckInService::class)->presentNowQuery()->count())->toBe(1);
});

it('includes members who checked out within the grace window', function (): void {
    $member = presenceMember();
    $now = presenceNow();

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => $now->copy()->subHours(2),
        'checked_out_at' => $now->copy()->subMinutes(10),
    ]);

    expect(app(CheckInService::class)->presentNowQuery()->count())->toBe(1);
});

it('excludes members who checked out beyond the grace window', function (): void {
    $member = presenceMember();
    $now = presenceNow();

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => $now->copy()->subHours(3),
        'checked_out_at' => $now->copy()->subMinutes(20),
    ]);

    expect(app(CheckInService::class)->presentNowQuery()->count())->toBe(0);
});

it('respects configurable grace minutes from settings', function (): void {
    Helpers::setTestSettingsOverride([
        'checkin' => ['present_now_grace_minutes' => 5],
    ]);

    $member = presenceMember();
    $now = presenceNow();

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => $now->copy()->subHour(),
        'checked_out_at' => $now->copy()->subMinutes(8),
    ]);

    expect(app(CheckInService::class)->presentNowQuery()->count())->toBe(0);
});

it('rejects qr check-in when member already has an open session', function (): void {
    $member = presenceMember();
    RateLimiter::clear("checkin:{$member->id}");

    $now = presenceNow();

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => $now->copy()->subHour(),
        'checked_out_at' => null,
    ]);

    $this->getJson("/checkin/{$member->checkin_token}")
        ->assertUnprocessable()
        ->assertJsonPath('status', 'already_present');
});

it('rate limits qr check-in after checkout within 30 minutes', function (): void {
    $member = presenceMember();
    RateLimiter::clear("checkin:{$member->id}");

    $this->getJson("/checkin/{$member->checkin_token}")->assertOk();

    $this->postJson("/checkin/{$member->checkin_token}/checkout")->assertOk();

    $this->getJson("/checkin/{$member->checkin_token}")->assertStatus(429);
});

it('creates a manual check-in when member has no open session', function (): void {
    $member = presenceMember();
    $service = app(CheckInService::class);

    $checkIn = $service->createManualCheckIn($member->id);

    expect($checkIn)->toBeInstanceOf(CheckIn::class)
        ->and($checkIn->member_id)->toBe($member->id)
        ->and($checkIn->method)->toBe('manual')
        ->and($checkIn->checked_out_at)->toBeNull();
});

it('blocks manual check-in when member already has an open session', function (): void {
    $member = presenceMember();
    $now = presenceNow();
    $service = app(CheckInService::class);

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => $now->copy()->subHour(),
        'checked_out_at' => null,
    ]);

    expect($service->createManualCheckIn($member->id))->toBeNull()
        ->and(CheckIn::where('member_id', $member->id)->count())->toBe(1);
});
