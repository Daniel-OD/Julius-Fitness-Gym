<?php

namespace App\Providers;

use App\Contracts\SequenceRepository;
use App\Contracts\SettingsRepository;
use App\Services\JsonSequenceRepository;
use App\Services\JsonSettingsRepository;
use App\Support\Studio;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class, JsonSettingsRepository::class);
        $this->app->singleton(SequenceRepository::class, JsonSequenceRepository::class);
    }

    public function boot(): void
    {
        $this->ensureStorageDirectoriesExist();

        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        View::share('studio', Studio::meta());

        AboutCommand::add('Studio', fn (): array => [
            'Author' => Studio::author(),
            'Signature' => Studio::signature(),
            'Repository' => Studio::repository(),
            'Reference' => (string) config('studio.ref'),
        ]);
    }

    private function ensureStorageDirectoriesExist(): void
    {
        $directories = [
            storage_path('framework/views'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('logs'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }
}
