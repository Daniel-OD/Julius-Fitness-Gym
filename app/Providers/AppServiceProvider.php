<?php

namespace App\Providers;

use App\Contracts\SequenceRepository;
use App\Contracts\SettingsRepository;
use App\Http\Responses\Filament\LoginResponse;
use App\Services\JsonSequenceRepository;
use App\Services\JsonSettingsRepository;
use App\Support\FilamentSession;
use App\Support\MailConfigurator;
use App\Support\Studio;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class, JsonSettingsRepository::class);
        $this->app->singleton(SequenceRepository::class, JsonSequenceRepository::class);
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
    }

    public function boot(): void
    {
        if (! $this->app->environment('local', 'testing')
            && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceHttps();
        }

        if (str_starts_with((string) config('app.url'), 'https://')) {
            config(['session.secure' => true]);
        }

        $this->ensureStorageDirectoriesExist();
        $this->configureLocalExecutionTimeLimit();
        $this->configureMailFromSettings();

        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        Event::listen(Logout::class, function (): void {
            FilamentSession::forget();
        });

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
            storage_path('app/private'),
            storage_path('app/private/livewire-tmp'),
            storage_path('app/public'),
            storage_path('data'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    /**
     * Local `php artisan serve` on Windows often uses max_execution_time=30,
     * which is too low for the analytics-heavy admin dashboard first load.
     */
    private function configureLocalExecutionTimeLimit(): void
    {
        if ($this->app->runningInConsole() || ! $this->runsOnLocalHttpServer()) {
            return;
        }

        @ini_set('max_execution_time', '120');
        @set_time_limit(120);
    }

    private function runsOnLocalHttpServer(): bool
    {
        if ($this->app->environment('local', 'testing')) {
            return true;
        }

        $url = (string) config('app.url');

        return str_starts_with($url, 'http://127.0.0.1')
            || str_starts_with($url, 'http://localhost');
    }

    private function configureMailFromSettings(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        try {
            MailConfigurator::apply();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
