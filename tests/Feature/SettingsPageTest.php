<?php

use App\Contracts\SettingsRepository;
use App\Filament\Pages\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function settingsAdmin(): User
{
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'View:Settings', 'guard_name' => 'web']);
    $role->givePermissionTo('View:Settings');

    $user = User::factory()->create(['must_change_password' => false]);
    $user->assignRole($role);

    return $user;
}

it('loads the settings page for authorized users', function (): void {
    $this->actingAs(settingsAdmin())
        ->get(route('filament.admin.pages.settings'))
        ->assertSuccessful();
});

it('renders the settings form inside a single livewire form', function (): void {
    $html = Livewire::actingAs(settingsAdmin())
        ->test(Settings::class)
        ->html();

    expect(substr_count($html, 'wire:submit="save"'))->toBe(1)
        ->and($html)->toContain(__('app.settings.tabs.mail'));
});

it('binds and saves with sparse settings file like fresh install', function (): void {
    app(SettingsRepository::class)->put([
        'general' => ['locale' => 'ro'],
        'invoice' => [],
        'member' => [],
        'charges' => [],
        'expenses' => [],
        'subscriptions' => [],
        'notifications' => ['email' => []],
        'backup' => [],
    ]);

    $gymName = 'Render Gym '.now()->timestamp;

    Livewire::actingAs(settingsAdmin())
        ->test(Settings::class)
        ->fillForm([
            'general' => [
                'gym_name' => $gymName,
                'gym_email' => 'contact@gym.test',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(SettingsRepository::class)->get()['general']['gym_name'])->toBe($gymName);
});

it('binds form input and persists settings on save', function (): void {
    $gymName = 'Sala Test '.now()->timestamp;

    Livewire::actingAs(settingsAdmin())
        ->test(Settings::class)
        ->fillForm([
            'general' => [
                'gym_name' => $gymName,
            ],
            'invoice' => [
                'prefix' => 'INV-',
                'last_number' => '42',
            ],
            'member' => [
                'prefix' => 'MEM-',
                'last_number' => '7',
            ],
            'charges' => [
                'admission_fee' => '50',
                'taxes' => '19',
                'discounts' => ['10', '20'],
            ],
            'expenses' => [
                'categories' => ['Rent', 'Utilities'],
            ],
            'subscriptions' => [
                'expiring_days' => 5,
            ],
            'checkin' => [
                'present_now_grace_minutes' => 20,
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $saved = app(SettingsRepository::class)->get();

    expect($saved['general']['gym_name'])->toBe($gymName)
        ->and($saved['invoice']['prefix'])->toBe('INV-')
        ->and($saved['member']['prefix'])->toBe('MEM-')
        ->and((string) $saved['charges']['taxes'])->toBe('19')
        ->and($saved['expenses']['categories'])->toBe(['Rent', 'Utilities'])
        ->and((int) $saved['subscriptions']['expiring_days'])->toBe(5)
        ->and((int) $saved['checkin']['present_now_grace_minutes'])->toBe(20);
});

it('persists mail settings and keeps api key when left blank on save', function (): void {
    Livewire::actingAs(settingsAdmin())
        ->test(Settings::class)
        ->fillForm([
            'mail' => [
                'driver' => 'resend',
                'resend_api_key' => 're_new_key',
                'from_address' => 'noreply@gym.test',
                'from_name' => 'Gym Test',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $saved = app(SettingsRepository::class)->get()['mail'];

    expect($saved['driver'])->toBe('resend')
        ->and($saved['resend_api_key'])->toBe('re_new_key')
        ->and($saved['from_address'])->toBe('noreply@gym.test');

    Livewire::actingAs(settingsAdmin())
        ->test(Settings::class)
        ->fillForm([
            'mail' => [
                'driver' => 'resend',
                'resend_api_key' => '',
                'from_address' => 'noreply@gym.test',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(SettingsRepository::class)->get()['mail']['resend_api_key'])->toBe('re_new_key');
});

it('updates gym name when set on the livewire data property', function (): void {
    $gymName = 'Direct Data '.now()->timestamp;

    Livewire::actingAs(settingsAdmin())
        ->test(Settings::class)
        ->set('data.general.gym_name', $gymName)
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(SettingsRepository::class)->get()['general']['gym_name'])->toBe($gymName);
});
