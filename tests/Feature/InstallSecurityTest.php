<?php

use App\Filament\Auth\ForcePasswordChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function superAdminRole(): Role
{
    return Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
}

it('generates a random password and does not use julius2024', function (): void {
    Artisan::call('app:install', [
        '--email' => 'test-install@julius.test',
        '--name' => 'Test Admin',
        '--url' => 'http://test.test',
    ]);

    $user = User::query()->where('email', 'test-install@julius.test')->firstOrFail();

    expect(Hash::check('julius2024', $user->password))->toBeFalse();
});

it('sets must_change_password when password is generated', function (): void {
    Artisan::call('app:install', [
        '--email' => 'test-mcp@julius.test',
        '--name' => 'Test Admin',
        '--url' => 'http://test.test',
    ]);

    $user = User::query()->where('email', 'test-mcp@julius.test')->firstOrFail();

    expect($user->must_change_password)->toBeTrue();
});

it('does not set must_change_password when explicit password is provided', function (): void {
    Artisan::call('app:install', [
        '--email' => 'test-explicit@julius.test',
        '--password' => 'Explicit$Pass99',
        '--name' => 'Test Admin',
        '--url' => 'http://test.test',
    ]);

    $user = User::query()->where('email', 'test-explicit@julius.test')->firstOrFail();
it('redirects user with must_change_password to force-change page', function (): void {
    $user = User::factory()->create(['must_change_password' => true]);
    $user->assignRole(superAdminRole());

    actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertRedirectToRoute('filament.admin.pages.force-password-change');
});

it('allows access to force-change page for user with must_change_password', function (): void {
    $user = User::factory()->create(['must_change_password' => true]);
    $user->assignRole(superAdminRole());

    actingAs($user)
        ->get(route('filament.admin.pages.force-password-change'))
        ->assertOk();
});
