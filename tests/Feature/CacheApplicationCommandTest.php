<?php

use Illuminate\Support\Facades\File;

it('warms application caches', function (): void {
    artisan('app:cache', ['--no-interaction' => true])
        ->assertSuccessful();

    expect(File::exists(base_path('bootstrap/cache/config.php')))->toBeTrue()
        ->and(File::exists(base_path('bootstrap/cache/routes-v7.php')))->toBeTrue();
});

it('clears application caches', function (): void {
    artisan('app:cache', ['--no-interaction' => true])->assertSuccessful();

    artisan('app:cache', ['--clear' => true, '--no-interaction' => true])
        ->assertSuccessful();

    expect(File::exists(base_path('bootstrap/cache/config.php')))->toBeFalse()
        ->and(File::exists(base_path('bootstrap/cache/routes-v7.php')))->toBeFalse();
});

it('removes stale public hot file when warming caches', function (): void {
    File::put(public_path('hot'), 'http://127.0.0.1:5173');

    artisan('app:cache', ['--no-interaction' => true])->assertSuccessful();

    expect(File::exists(public_path('hot')))->toBeFalse();
});

afterEach(function (): void {
    if (File::exists(base_path('bootstrap/cache/config.php'))) {
        artisan('app:cache', ['--clear' => true, '--no-interaction' => true]);
    }
});
