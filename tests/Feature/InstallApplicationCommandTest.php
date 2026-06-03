<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

it('creates admin user and credentials file', function (): void {
    $this->artisan('app:install', [
        '--email' => 'installer@test.local',
        '--password' => 'secret-pass-99',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $user = User::query()->where('email', 'installer@test.local')->first();

    expect($user)->not->toBeNull()
        ->and($user?->hasRole('super_admin'))->toBeTrue();

    $credentialsPath = storage_path('app/install-credentials.txt');
    expect(File::exists($credentialsPath))->toBeTrue()
        ->and(File::get($credentialsPath))->toContain('installer@test.local')
        ->and(File::exists(storage_path('app/.install-complete')))->toBeTrue();
});

it('does not recreate admin without force flag', function (): void {
    User::factory()->create([
        'email' => 'existing@test.local',
        'password' => 'old-password',
    ]);

    $this->artisan('app:install', [
        '--email' => 'existing@test.local',
        '--password' => 'new-password',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(User::query()->where('email', 'existing@test.local')->count())->toBe(1);
});
