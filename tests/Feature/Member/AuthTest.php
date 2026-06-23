<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a member with a password to log in', function (): void {
    $member = Member::factory()->create([
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $response = post('/member/login', [
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertRedirect('/member/dashboard');
    assertAuthenticatedAs($member, 'member');
});

it('redirects a passwordless member to set-password instead of showing invalid credentials', function (): void {
    Member::factory()->create([
        'email' => 'new@example.com',
        'password' => null,
    ]);

    $response = post('/member/login', [
        'email' => 'new@example.com',
        'password' => 'anything',
    ]);

    $response->assertRedirect(route('member.set-password', ['email' => 'new@example.com']));
    $response->assertSessionHas('status');
    assertGuest('member');
});

it('returns an error for invalid credentials', function (): void {
    Member::factory()->create([
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $response = from('/member/login')->post('/member/login', [
        'email' => 'member@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/member/login');
    $response->assertSessionHasErrors('email');
    assertGuest('member');
});

it('denies dashboard access after logout', function (): void {
    $member = Member::factory()->create([
        'email' => 'member@example.com',
        'password' => 'secret-password',
        'email_verified_at' => now(),
    ]);

    $plan = Plan::factory()->create();
    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(5)->toDateString(),
        'end_date' => Carbon::today()->addDays(20)->toDateString(),
        'status' => 'ongoing',
    ]);

    actingAs($member, 'member');

    get('/member/dashboard')->assertOk();

    post('/member/logout')->assertRedirect('/member/login');

    get('/member/dashboard')->assertRedirect('/member/login');
});
