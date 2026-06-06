<?php

use App\Contracts\SettingsRepository;
use App\Filament\Livewire\AdminGuideToggle;
use App\Models\User;
use App\Support\AdminGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('resolves filament route names to guide keys', function (): void {
    expect(AdminGuide::resolveKey('filament.admin.pages.dashboard'))->toBe('admin.dashboard')
        ->and(AdminGuide::resolveKey('filament.admin.resources.members.index'))->toBe('admin.members.index')
        ->and(AdminGuide::resolveKey('filament.admin.resources.members.edit'))->toBe('admin.members.edit')
        ->and(AdminGuide::resolveKey('filament.office.pages.dashboard'))->toBe('office.dashboard')
        ->and(AdminGuide::resolveKey('filament.admin.auth.login'))->toBeNull();
});

it('falls back from unknown edit routes to index guide content', function (): void {
    $entry = AdminGuide::entryForKey('admin.invoices.edit');

    expect($entry)->not->toBeNull()
        ->and($entry['title'])->toBe(AdminGuide::entryForKey('admin.invoices.index')['title']);
});

it('does not expose guide content when disabled', function (): void {
    app(SettingsRepository::class)->put([
        ...app(SettingsRepository::class)->get(),
        'general' => [
            'admin_guide_enabled' => false,
        ],
    ]);

    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful()
        ->assertDontSee(__('admin_guide.badge'), false);
});

it('shows contextual guide banner when enabled', function (): void {
    app(SettingsRepository::class)->put([
        ...app(SettingsRepository::class)->get(),
        'general' => [
            'admin_guide_enabled' => true,
            'locale' => 'en',
        ],
    ]);

    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful()
        ->assertSee(__('admin_guide.badge'), false)
        ->assertSee(AdminGuide::entryForKey('admin.dashboard')['title'], false)
        ->assertSee('jf-admin-guide', false);
});

it('persists admin guide toggle from profile menu control', function (): void {
    app(SettingsRepository::class)->put([
        'general' => ['admin_guide_enabled' => false],
        'invoice' => [],
        'member' => [],
        'charges' => [],
        'expenses' => [],
        'subscriptions' => [],
        'checkin' => [],
        'notifications' => ['email' => []],
        'backup' => [],
    ]);

    Livewire::test(AdminGuideToggle::class)
        ->assertSet('enabled', false)
        ->call('toggle')
        ->assertRedirect();

    expect(app(SettingsRepository::class)->get()['general']['admin_guide_enabled'])->toBeTrue();
});

it('renders admin guide toggle in the user menu theme switcher row', function (): void {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful()
        ->assertSee('fi-theme-switcher', false)
        ->assertSee('wire:click="toggle"', false);
});

it('loads settings tab guide content when guide is enabled', function (): void {
    app(SettingsRepository::class)->put([
        ...app(SettingsRepository::class)->get(),
        'general' => ['admin_guide_enabled' => true, 'locale' => 'en'],
    ]);

    $user = User::factory()->create(['must_change_password' => false]);
    $guide = AdminGuide::entryForKey('admin.settings.tabs.charges');

    expect($guide)->not->toBeNull()
        ->and($guide['steps'])->not->toBeEmpty();

    $this->actingAs($user)
        ->get(route('filament.admin.pages.settings'))
        ->assertSuccessful()
        ->assertSee($guide['title'], false)
        ->assertSee('jf-admin-guide__steps', false);
});

it('shows settings overview guide at page top', function (): void {
    app(SettingsRepository::class)->put([
        ...app(SettingsRepository::class)->get(),
        'general' => ['admin_guide_enabled' => true, 'locale' => 'en'],
    ]);

    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get(route('filament.admin.pages.settings'))
        ->assertSuccessful()
        ->assertSee(AdminGuide::entryForKey('admin.settings.overview')['title'], false);
});
