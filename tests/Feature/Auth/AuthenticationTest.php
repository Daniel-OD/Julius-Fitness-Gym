<?php

use App\Models\User;

test('legacy login screen returns not found', function (): void {
    get('/login')->assertNotFound();
});

test('users can logout via breeze logout route', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/logout');

    assertGuest();
    $response->assertRedirect('/');
});
