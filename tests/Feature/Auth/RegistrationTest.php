<?php

test('public registration is disabled', function (): void {
    get('/register')->assertNotFound();
});

test('public registration post is disabled', function (): void {
    post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    assertGuest();
});

test('member login remains available', function (): void {
    get('/member/login')->assertSuccessful();
});

test('staff login remains available', function (): void {
    get('/staff/login')->assertSuccessful();
});
