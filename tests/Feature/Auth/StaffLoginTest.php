<?php

use App\Filament\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('staff login page is available at staff login', function (): void {
    $this->get('/staff/login')->assertSuccessful();
});

test('legacy login url returns not found', function (): void {
    $this->get('/login')->assertNotFound();
    $this->post('/login', [
        'email' => 'staff@example.com',
        'password' => 'password',
    ])->assertNotFound();
});

test('unauthenticated admin redirects to staff login', function (): void {
    $this->get('/admin')->assertRedirect('/staff/login');
});

test('member login is unaffected', function (): void {
    $this->get('/member/login')->assertSuccessful();
});

test('office login remains at office login', function (): void {
    $this->get('/office/login')->assertSuccessful();
});

test('admin login slug is no longer registered', function (): void {
    $this->get('/admin/login')->assertNotFound();
});

test('filament admin login route points to staff login', function (): void {
    expect(route('filament.admin.auth.login'))->toBe(url('/staff/login'));
});

test('a user without roles cannot authenticate via staff login', function (): void {
    $user = User::factory()->create();

    $this->get('/staff/login');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    $this->assertGuest();
});
