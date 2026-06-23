<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

/*
 * app:install writes to the real base_path('.env'). Snapshot it before each
 * test and restore it after so running the suite never mutates the working
 * environment's database connection or app settings.
 */
beforeEach(function (): void {
    $env = base_path('.env');

    if (File::exists($env)) {
        File::copy($env, $env.'.testbak');
    }
});

afterEach(function (): void {
    $env = base_path('.env');
    $backup = $env.'.testbak';

    if (File::exists($backup)) {
        File::move($backup, $env);
    }
});

it('creates admin user and credentials file', function (): void {
    artisan('app:install', [
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

    artisan('app:install', [
        '--email' => 'existing@test.local',
        '--password' => 'new-password',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(User::query()->where('email', 'existing@test.local')->count())->toBe(1);
});

it('does not overwrite the database connection on an existing env', function (): void {
    $env = base_path('.env');

    if (! File::exists($env)) {
        File::copy(base_path('.env.example'), $env);
    }

    File::put($env, (string) preg_replace(
        '/^DB_CONNECTION=.*$/m',
        'DB_CONNECTION=mysql',
        File::get($env),
    ));

    artisan('app:install', [
        '--email' => 'guard@test.local',
        '--password' => 'secret-pass-99',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(File::get($env))
        ->toContain('DB_CONNECTION=mysql')
        ->not->toContain('DB_CONNECTION=sqlite');
});
