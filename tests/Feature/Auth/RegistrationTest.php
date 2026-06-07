<?php

test('public registration is disabled', function (): void {
    $this->get('/register')->assertNotFound();
});

test('public registration post is disabled', function (): void {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertGuest();
});

test('member login remains available', function (): void {
    $this->get('/member/login')->assertSuccessful();
});

test('staff login remains available', function (): void {
    $this->get('/staff/login')->assertSuccessful();
});
