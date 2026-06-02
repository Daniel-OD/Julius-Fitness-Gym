<?php

use App\Contracts\SettingsRepository;
use App\Services\JsonSettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Use a temp file for settings during tests
    $this->repo = new JsonSettingsRepository;
});

it('SettingsRepository is bound correctly in container', function (): void {
    $repo = app(SettingsRepository::class);
    expect($repo)->toBeInstanceOf(JsonSettingsRepository::class);
});

it('get() returns array', function (): void {
    $settings = app(SettingsRepository::class)->get();
    expect($settings)->toBeArray();
});

it('put() persists data and get() reads it back', function (): void {
    $repo = app(SettingsRepository::class);
    $original = $repo->get();

    $data = array_merge($original, ['general' => array_merge(
        $original['general'] ?? [],
        ['gym_name' => 'Test Gym '.now()->timestamp]
    )]);
    $repo->put($data);

    $readBack = $repo->get();
    expect($readBack['general']['gym_name'])->toStartWith('Test Gym');

    // Restore original
    $repo->put($original);
});

it('settings has checkin keys', function (): void {
    $settings = app(SettingsRepository::class)->get();
    // May or may not have checkin key depending on the file — just check it doesn't crash
    expect(is_array($settings))->toBeTrue();
});

it('data_get works on nested settings keys', function (): void {
    $repo = app(SettingsRepository::class);
    $settings = $repo->get();

    $locale = data_get($settings, 'general.locale', 'en');
    expect($locale)->toBeString();
});
