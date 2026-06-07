<?php

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a member with a password to log in', function (): void {
    $member = Member::factory()->create([
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $response = $this->post('/member/login', [
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertRedirect('/member/dashboard');
    $this->assertAuthenticatedAs($member, 'member');
});

it('redirects a passwordless member to set-password instead of showing invalid credentials', function (): void {
    Member::factory()->create([
        'email' => 'new@example.com',
        'password' => null,
    ]);

    $response = $this->post('/member/login', [
        'email' => 'new@example.com',
        'password' => 'anything',
    ]);

    $response->assertRedirect(route('member.set-password', ['email' => 'new@example.com']));
    $response->assertSessionHas('status');
    $this->assertGuest('member');
});

it('returns an error for invalid credentials', function (): void {
    Member::factory()->create([
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $response = $this->from('/member/login')->post('/member/login', [
        'email' => 'member@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/member/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest('member');
});

it('denies dashboard access after logout', function (): void {
    $member = Member::factory()->create([
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    $this->actingAs($member, 'member');

    $this->get('/member/dashboard')->assertOk();

    $this->post('/member/logout')->assertRedirect('/member/login');

    $this->get('/member/dashboard')->assertRedirect('/member/login');
});
