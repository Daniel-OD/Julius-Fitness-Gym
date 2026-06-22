<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('homepage shows active services from database', function (): void {
    Service::factory()->create(['name' => 'Sală de forță', 'is_active' => true, 'sort_order' => 1]);
    Service::factory()->create(['name' => 'Cardio', 'is_active' => true, 'sort_order' => 2]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Sală de forță')
        ->assertSee('Cardio');
});

it('homepage does not show inactive services', function (): void {
    Service::factory()->create(['name' => 'Serviciu activ', 'is_active' => true]);
    Service::factory()->inactive()->create(['name' => 'Serviciu inactiv']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Serviciu activ')
        ->assertDontSee('Serviciu inactiv');
});

it('homepage services are ordered by sort_order', function (): void {
    Service::factory()->create(['name' => 'Al doilea', 'is_active' => true, 'sort_order' => 2]);
    Service::factory()->create(['name' => 'Primul', 'is_active' => true, 'sort_order' => 1]);

    $response = $this->get('/');

    $response->assertOk();
    $content = $response->getContent();
    $this->assertGreaterThan(
        strpos($content, 'Primul'),
        strpos($content, 'Al doilea'),
    );
});

it('homepage services section renders when no services exist', function (): void {
    $this->get('/')->assertOk()->assertSee('servicii');
});
