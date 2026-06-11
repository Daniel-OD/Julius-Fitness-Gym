<?php

use App\Enums\CheckInStatus;
use App\Helpers\Helpers;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\EmployeeRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
    Helpers::setTestSettingsOverride([
        'checkin' => [
            'enabled' => true,
            'require_active_subscription' => false,
        ],
    ]);
});

afterEach(function (): void {
    Helpers::setTestSettingsOverride(null);
});

function receptionEmployee(): User
{
    (new EmployeeRoleSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole('employee');

    return $user;
}

function receptionMember(): Member
{
    $member = Member::factory()->create([
        'checkin_token' => Str::random(32),
        'status' => 'active',
    ]);

    RateLimiter::clear("checkin:{$member->id}");

    return $member;
}

function receptionActiveSubscription(Member $member): Subscription
{
    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => Plan::factory()->create(['days' => 30, 'status' => 'active'])->id,
        'start_date' => Carbon::today()->subDays(5)->toDateString(),
        'end_date' => Carbon::today()->addDays(25)->toDateString(),
        'status' => 'ongoing',
        'type' => 'official',
    ]);
}

it('redirects guests to login', function (): void {
    $this->get('/reception/scan')->assertRedirect();
});

it('rejects guests posting a scan', function (): void {
    $this->postJson('/reception/scan', ['code' => 'whatever'])->assertUnauthorized();
});

it('forbids staff without check-in permission', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/reception/scan')->assertForbidden();
});

it('shows the scan page to an employee', function (): void {
    $this->actingAs(receptionEmployee())
        ->get('/reception/scan')
        ->assertSuccessful()
        ->assertSee(__('app.reception.title'));
});

it('records a check-in from a scanned full url', function (): void {
    $member = receptionMember();
    receptionActiveSubscription($member);

    $this->actingAs(receptionEmployee())
        ->postJson('/reception/scan', [
            'code' => route('checkin.scan', ['qrToken' => $member->checkin_token]),
        ])
        ->assertSuccessful()
        ->assertJsonPath('result', 'success')
        ->assertJsonPath('color', 'green')
        ->assertJsonPath('member.name', $member->name);

    expect(CheckIn::where('member_id', $member->id)->sole()->status)
        ->toBe(CheckInStatus::Success);
});

it('records a check-in from a bare token', function (): void {
    $member = receptionMember();
    receptionActiveSubscription($member);

    $this->actingAs(receptionEmployee())
        ->postJson('/reception/scan', ['code' => $member->checkin_token])
        ->assertSuccessful()
        ->assertJsonPath('result', 'success');
});

it('returns yellow for a grace entry', function (): void {
    $member = receptionMember();
    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => Plan::factory()->create(['days' => 30, 'status' => 'active'])->id,
        'start_date' => Carbon::today()->subDays(40)->toDateString(),
        'end_date' => Carbon::today()->subDays(10)->toDateString(),
        'status' => 'expired',
        'type' => 'official',
    ]);

    $this->actingAs(receptionEmployee())
        ->postJson('/reception/scan', ['code' => $member->checkin_token])
        ->assertSuccessful()
        ->assertJsonPath('result', 'grace_entry')
        ->assertJsonPath('color', 'yellow');
});

it('returns red for an unknown code', function (): void {
    $this->actingAs(receptionEmployee())
        ->postJson('/reception/scan', ['code' => 'not-a-real-token'])
        ->assertNotFound()
        ->assertJsonPath('result', 'error')
        ->assertJsonPath('color', 'red');
});

it('validates that a code is present', function (): void {
    $this->actingAs(receptionEmployee())
        ->postJson('/reception/scan', ['code' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);
});
