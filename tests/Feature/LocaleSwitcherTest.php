<?php

use App\Contracts\SettingsRepository;
use App\Filament\Livewire\LocaleSwitcher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('denies switching the application locale without View:Settings permission', function (): void {
    actingAs(User::factory()->create());

    Livewire::test(LocaleSwitcher::class)
        ->call('setLocale', 'ro')
        ->assertForbidden();
});

it('allows switching the application locale with View:Settings permission', function (): void {
    actingAs(adminPanelUser());

    Livewire::test(LocaleSwitcher::class)
        ->call('setLocale', 'ro')
        ->assertHasNoErrors();

    expect(app(SettingsRepository::class)->get()['general']['locale'] ?? null)->toBe('ro');
});
