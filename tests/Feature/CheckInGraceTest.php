<?php

use App\Contracts\SettingsRepository;
use App\Enums\CheckInStatus;
use App\Helpers\Helpers;
use App\Jobs\SendGraceEntryNotification;
use App\Mail\GraceEntryNotificationMail;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CheckIns\CheckInService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Helpers::setTestSettingsOverride([
        'checkin' => [
            'enabled' => true,
            'require_active_subscription' => false,
        ],
        'general' => [
            'admin_email' => 'admin@example.com',
            'gym_name' => 'Julius Fitness Gym',
        ],
    ]);
});

afterEach(function (): void {
    Helpers::setTestSettingsOverride(null);
});

function graceMember(): Member
{
    $member = Member::factory()->create([
        'checkin_token' => Str::random(32),
        'status' => 'active',
    ]);

    RateLimiter::clear("checkin:{$member->id}");

    return $member;
}

function graceExpiredSubscription(Member $member, int $endedDaysAgo = 10): Subscription
{
    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => Plan::factory()->create(['days' => 30, 'status' => 'active'])->id,
        'start_date' => Carbon::today()->subDays($endedDaysAgo + 30)->toDateString(),
        'end_date' => Carbon::today()->subDays($endedDaysAgo)->toDateString(),
        'status' => 'expired',
        'type' => 'official',
    ]);
}

it('records a success check-in for an active subscription', function (): void {
    Queue::fake();

    $member = graceMember();
    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => Plan::factory()->create(['days' => 30, 'status' => 'active'])->id,
        'start_date' => Carbon::today()->subDays(5)->toDateString(),
        'end_date' => Carbon::today()->addDays(25)->toDateString(),
        'status' => 'ongoing',
        'type' => 'official',
    ]);

    $this->getJson("/checkin/{$member->checkin_token}")
        ->assertSuccessful()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('color', 'green');

    expect(CheckIn::where('member_id', $member->id)->sole()->status)
        ->toBe(CheckInStatus::Success);

    Queue::assertNotPushed(SendGraceEntryNotification::class);
});

it('grants a single grace entry on the first scan after expiry', function (): void {
    Queue::fake();

    $member = graceMember();
    graceExpiredSubscription($member);

    $this->getJson("/checkin/{$member->checkin_token}")
        ->assertSuccessful()
        ->assertJsonPath('status', 'grace_entry')
        ->assertJsonPath('color', 'yellow');

    $checkIn = CheckIn::where('member_id', $member->id)->sole();

    expect($checkIn->status)->toBe(CheckInStatus::GraceEntry);

    Queue::assertPushed(
        SendGraceEntryNotification::class,
        fn (SendGraceEntryNotification $job): bool => $job->checkInId === $checkIn->id,
    );
});

it('blocks the second scan after the grace entry was used', function (): void {
    Queue::fake();

    $member = graceMember();
    graceExpiredSubscription($member);

    CheckIn::factory()->graceEntry()->create([
        'member_id' => $member->id,
        'checked_in_at' => Carbon::now()->subDay(),
        'checked_out_at' => Carbon::now()->subDay()->addHour(),
    ]);

    $this->getJson("/checkin/{$member->checkin_token}")
        ->assertUnprocessable()
        ->assertJsonPath('status', 'blocked')
        ->assertJsonPath('color', 'red');

    expect(
        CheckIn::where('member_id', $member->id)
            ->where('status', CheckInStatus::Blocked)
            ->where('denied_reason', 'expired_grace_used')
            ->exists(),
    )->toBeTrue();

    Queue::assertNotPushed(SendGraceEntryNotification::class);
});

it('blocks immediately when require_active_subscription is enabled', function (): void {
    Queue::fake();

    Helpers::setTestSettingsOverride([
        'checkin' => [
            'enabled' => true,
            'require_active_subscription' => true,
        ],
    ]);

    $member = graceMember();
    graceExpiredSubscription($member);

    $this->getJson("/checkin/{$member->checkin_token}")
        ->assertUnprocessable()
        ->assertJsonPath('status', 'blocked');

    Queue::assertNotPushed(SendGraceEntryNotification::class);
});

it('grants a new grace entry after a renewal expires again', function (): void {
    Queue::fake();

    $member = graceMember();

    graceExpiredSubscription($member, 60);
    CheckIn::factory()->graceEntry()->create([
        'member_id' => $member->id,
        'checked_in_at' => Carbon::now()->subDays(50),
        'checked_out_at' => Carbon::now()->subDays(50)->addHour(),
    ]);

    graceExpiredSubscription($member, 5);

    $this->getJson("/checkin/{$member->checkin_token}")
        ->assertSuccessful()
        ->assertJsonPath('status', 'grace_entry');
});

it('excludes blocked attempts from presence queries', function (): void {
    $member = graceMember();

    CheckIn::factory()->blocked()->create([
        'member_id' => $member->id,
        'checked_in_at' => Carbon::now()->subMinutes(5),
    ]);

    $service = app(CheckInService::class);

    expect($service->presentNowQuery()->count())->toBe(0)
        ->and($service->todayCheckInCount())->toBe(0)
        ->and($service->hasOpenSession($member->id))->toBeFalse();
});

it('sends the grace entry email to the configured admin', function (): void {
    Mail::fake();

    $member = graceMember();
    $subscription = graceExpiredSubscription($member);

    $checkIn = CheckIn::factory()->graceEntry()->create([
        'member_id' => $member->id,
        'checked_in_at' => Carbon::now(),
        'checked_out_at' => null,
    ]);

    new SendGraceEntryNotification($checkIn->id)->handle(app(SettingsRepository::class));

    Mail::assertSent(
        GraceEntryNotificationMail::class,
        fn (GraceEntryNotificationMail $mail): bool => $mail->hasTo('admin@example.com')
            && $mail->memberName === $member->name
            && $mail->planName === $subscription->plan->name,
    );
});

it('skips the grace entry email when no admin email is configured', function (): void {
    Mail::fake();

    Helpers::setTestSettingsOverride(['checkin' => ['enabled' => true]]);

    $member = graceMember();
    $checkIn = CheckIn::factory()->graceEntry()->create(['member_id' => $member->id]);

    new SendGraceEntryNotification($checkIn->id)->handle(app(SettingsRepository::class));

    Mail::assertNothingSent();
});
