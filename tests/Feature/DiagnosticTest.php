<?php

test('office login page loads', function () {
    $response = $this->get('/office/login');

    $response->assertStatus(200);
});

test('admin login page loads', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
});
