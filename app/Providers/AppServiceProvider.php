<?php

namespace App\Providers;

use App\Contracts\SequenceRepository;
use App\Contracts\SettingsRepository;
use App\Services\JsonSequenceRepository;
use App\Services\JsonSettingsRepository;
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
        //
    }
}
