<?php

use App\Models\User;

test('legacy login screen returns not found', function (): void {
    $this->get('/login')->assertNotFound();
});

test('users can logout via breeze logout route', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
