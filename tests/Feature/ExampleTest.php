<?php

it('returns a successful response', function (): void {
    $response = get('/');

    $response->assertStatus(200);
});
