<?php

test('office login page loads', function (): void {
    $response = $this->get('/office/login');

    $response->assertStatus(200);
});

test('staff login page loads', function (): void {
    $response = $this->get('/staff/login');

    $response->assertStatus(200);
});

test('legacy admin login slug is not available', function (): void {
    $response = $this->get('/admin/login');

    $response->assertNotFound();
});
