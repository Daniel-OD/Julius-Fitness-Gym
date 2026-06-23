<?php

test('office login page loads', function (): void {
    $response = get('/office/login');

    $response->assertSuccessful()
        ->assertDontSee('Daniel-OD/Julius-Fitness-Gym', false);
});

test('staff login page loads', function (): void {
    $response = get('/staff/login');

    $response->assertSuccessful()
        ->assertDontSee('Daniel-OD/Julius-Fitness-Gym', false);
});

test('public pages do not show studio signature', function (): void {
    get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Daniel-OD/Julius-Fitness-Gym', false);
});

test('legacy admin login slug is not available', function (): void {
    $response = get('/admin/login');

    $response->assertNotFound();
});
