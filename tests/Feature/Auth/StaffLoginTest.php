<?php

use App\Filament\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('staff login page is available at staff login', function (): void {
    get('/staff/login')->assertSuccessful();
});

test('legacy login url returns not found', function (): void {
    get('/login')->assertNotFound();
    post('/login', [
        'email' => 'staff@example.com',
        'password' => 'password',
    ])->assertNotFound();
});

test('unauthenticated admin redirects to staff login', function (): void {
    get('/admin')->assertRedirect('/staff/login');
});

test('member login is unaffected', function (): void {
    get('/member/login')->assertSuccessful();
});

test('office login remains at office login', function (): void {
    get('/office/login')->assertSuccessful();
});

test('admin login slug is no longer registered', function (): void {
    get('/admin/login')->assertNotFound();
});

test('filament admin login route points to staff login', function (): void {
    expect(route('filament.admin.auth.login'))->toBe(url('/staff/login'));
});

test('a user without roles cannot authenticate via staff login', function (): void {
    $user = User::factory()->create();

    get('/staff/login');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    assertGuest();
});
