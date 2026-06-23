<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dashboardMemberWithSubscription(): Member
{
    $member = Member::factory()->create([
        'password' => 'secret',
        'email_verified_at' => now(),
    ]);

    $plan = Plan::factory()->create(['name' => 'Premium Gold']);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(5)->toDateString(),
        'end_date' => Carbon::today()->addDays(20)->toDateString(),
        'status' => 'ongoing',
    ]);

    return $member;
}

it('returns 200 for an authenticated member with subscription', function (): void {
    $member = dashboardMemberWithSubscription();

    actingAs($member, 'member')
        ->get('/member/dashboard')
        ->assertOk()
        ->assertViewIs('member.dashboard.index');
});

it('redirects unauthenticated visitors to login', function (): void {
    get('/member/dashboard')
        ->assertRedirect('/member/login');
});

it('redirects verified member without subscription to plans', function (): void {
    $member = Member::factory()->create([
        'password' => 'secret',
        'email_verified_at' => now(),
    ]);

    actingAs($member, 'member')
        ->get('/member/dashboard')
        ->assertRedirect(route('member.plans'));
});

it('shows the plan name when the member has an active subscription', function (): void {
    $member = dashboardMemberWithSubscription();

    actingAs($member, 'member')
        ->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Premium Gold', false);
});
