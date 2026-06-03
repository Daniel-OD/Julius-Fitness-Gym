<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CacheApplication extends Command
{
    protected $signature = 'app:cache
                            {--clear : Clear all warmed caches instead of building them}';

    protected $description = 'Warm or clear Laravel, Filament, and route caches for faster page loads';

    public function handle(): int
    {
        if ((bool) $this->option('clear')) {
            return $this->clearCaches();
        }

        return $this->warmCaches();
    }

    private function warmCaches(): int
    {
        $this->components->info('Warming application caches for faster page loads...');

        $this->removeStaleViteHotFile();

        $this->callSilently('optimize', ['--except' => 'views']);
        $this->callSilently('filament:optimize');

        $this->newLine();
        $this->components->info('Application caches warmed successfully.');
        $this->line('  Tip: run <comment>php artisan app:cache --clear</comment> after changing .env, routes, or Filament resources.');

        return self::SUCCESS;
    }

    private function clearCaches(): int
    {
        $this->components->info('Clearing application caches...');

        $this->callSilently('optimize:clear');
        $this->callSilently('filament:optimize-clear');

        $this->newLine();
        $this->components->info('Application caches cleared.');

        return self::SUCCESS;
    }

    private function removeStaleViteHotFile(): void
    {
        $hotPath = public_path('hot');

        if (! File::exists($hotPath)) {
            return;
        }

        File::delete($hotPath);
        $this->line('  Removed stale <comment>public/hot</comment> (Vite dev server marker).');
    }
}
