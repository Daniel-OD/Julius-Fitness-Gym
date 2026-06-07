<?php

use App\Jobs\SendNewMemberNotification;
use App\Models\Member;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('shows the register page', function (): void {
    $this->get(route('member.register'))
        ->assertOk()
        ->assertViewIs('member.auth.register');
});

it('registers a new member and redirects to email verification', function (): void {
    $this->post(route('member.register'), [
        'name' => 'Test Member',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('member.verify-email'));

    $this->assertDatabaseHas('members', ['email' => 'test@example.com']);
    $this->assertAuthenticatedAs(Member::where('email', 'test@example.com')->first(), 'member');
});

it('rejects duplicate email on registration', function (): void {
    Member::factory()->create(['email' => 'existing@example.com']);

    $this->post(route('member.register'), [
        'name' => 'Another',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});

it('shows the login page', function (): void {
    $this->get(route('member.login'))
        ->assertOk()
        ->assertViewIs('member.auth.login');
});

it('shows plans page for verified member', function (): void {
    $member = Member::factory()->create([
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($member, 'member')
        ->get(route('member.plans'))
        ->assertOk()
        ->assertViewIs('member.plans.index');
});

it('redirects unverified member away from plans page', function (): void {
    $member = Member::factory()->create([
        'password' => 'password',
        'email_verified_at' => null,
    ]);

    $this->actingAs($member, 'member')
        ->get(route('member.plans'))
        ->assertRedirect(route('member.verify-email'));
});

it('dispatches SendNewMemberNotification after plan selection', function (): void {
    Queue::fake();

    $member = Member::factory()->create([
        'password' => 'password',
        'email_verified_at' => now(),
    ]);
    $plan = Plan::factory()->create(['status' => 'active']);

    $this->actingAs($member, 'member')
        ->post(route('member.plans.store'), ['plan_id' => $plan->id])
        ->assertRedirect(route('member.plans'));

    Queue::assertPushed(SendNewMemberNotification::class, function ($job) use ($member, $plan): bool {
        return $job->memberId === $member->id && $job->planId === $plan->id;
    });
});
