<?php

use App\Models\User;

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    assertSame('Test User', $user->name);
    assertSame('test@example.com', $user->email);
    assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    assertGuest();
    assertNotNull($user->fresh()->deleted_at);
});

test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    assertNotNull($user->fresh());
});
