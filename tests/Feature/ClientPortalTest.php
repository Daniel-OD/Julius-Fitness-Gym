<?php

use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Members\CreateMemberPortalAccountService;
use App\Services\Members\MemberQrCodeService;
use Carbon\CarbonImmutable;
use Database\Seeders\ClientRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function portalClientUser(?Member $member = null): User
{
    (new ClientRoleSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole('client');

    if ($member instanceof Member) {
        $member->user()->associate($user);
        $member->save();
    }

    return $user;
}

function activeMemberSubscription(Member $member): Subscription
{
    $plan = Plan::factory()->create(['name' => 'Monthly Pass']);
    $today = CarbonImmutable::today(config('app.timezone'));

    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => $today->subDays(5),
        'end_date' => $today->addDays(25),
        'status' => 'ongoing',
    ]);
}

it('client with linked member sees dashboard qr and subscription status', function (): void {
    $member = Member::factory()->create(['name' => 'Alex Member']);
    activeMemberSubscription($member);
    $user = portalClientUser($member);

    actingAs($user)
        ->get(route('client.dashboard'))
        ->assertSuccessful()
        ->assertSee('Alex Member')
        ->assertSee('Monthly Pass')
        ->assertSee(__('app.client_portal.open_qr'));
});

it('client without linked member sees contact message', function (): void {
    $user = portalClientUser();

    actingAs($user)
        ->get(route('client.dashboard'))
        ->assertSuccessful()
        ->assertSee(__('app.client_portal.no_member_linked'));
});

it('client can open fullscreen qr page', function (): void {
    $member = Member::factory()->create();
    activeMemberSubscription($member);
    $user = portalClientUser($member);

    actingAs($user)
        ->get(route('client.qr'))
        ->assertSuccessful()
        ->assertSee($member->code);
});

it('client without linked member cannot open fullscreen qr page', function (): void {
    $user = portalClientUser();

    actingAs($user)
        ->get(route('client.qr'))
        ->assertForbidden();
});

it('client sees recent check-ins on dashboard', function (): void {
    $member = Member::factory()->create();
    activeMemberSubscription($member);
    $user = portalClientUser($member);

    CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => now()->subHour(),
        'checked_out_at' => null,
    ]);

    actingAs($user)
        ->get(route('client.dashboard'))
        ->assertSuccessful()
        ->assertSee(__('app.client_portal.still_present'));
});

it('create portal account service links member and assigns client role', function (): void {
    $member = Member::factory()->create([
        'name' => 'Portal Member',
        'email' => 'portal.member@example.com',
    ]);

    $user = app(CreateMemberPortalAccountService::class)->create($member);

    expect($user->hasRole('client'))->toBeTrue()
        ->and($member->fresh()->user_id)->toBe($user->id)
        ->and($user->email)->toBe('portal.member@example.com');
});

it('create portal account service rejects members that already have an account', function (): void {
    $member = Member::factory()->create(['email' => 'linked@example.com']);
    portalClientUser($member);

    expect(fn () => app(CreateMemberPortalAccountService::class)->create($member->fresh()))
        ->toThrow(RuntimeException::class, __('app.client_portal.portal_account_exists'));
});

it('member qr route uses qrToken parameter', function (): void {
    $member = Member::factory()->create();

    expect(app(MemberQrCodeService::class)->checkinUrl($member))
        ->toContain('/checkin/'.$member->checkin_token);
});
