<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use ZipArchive;

#[Description('Restore database (and optionally settings) from a backup ZIP file')]
#[Signature('app:restore
                            {zip : Full path to the backup ZIP file}
                            {--include-settings : Also restore settingsData.json}
                            {--skip-pre-backup : Skip the automatic safety backup before restoring}')]
class RestoreApplication extends Command
{
    public function handle(): int
    {
        $zipPath = (string) $this->argument('zip');

        if (! file_exists($zipPath)) {
            $this->error("ZIP file not found: {$zipPath}");

            return self::FAILURE;
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            $this->error('Cannot open ZIP file.');

            return self::FAILURE;
        }

        if ($zip->locateName('database.sqlite') === false) {
            $this->error('ZIP does not contain a database.sqlite file.');
            $zip->close();

            return self::FAILURE;
        }

        $tempDir = storage_path('app/restore-tmp-'.time());
        mkdir($tempDir, 0755, true);
        $zip->extractTo($tempDir);
        $zip->close();

        $newDbPath = $tempDir.DIRECTORY_SEPARATOR.'database.sqlite';

        if (! $this->isValidSQLite($newDbPath)) {
            $this->cleanupDir($tempDir);
            $this->error('database.sqlite in the ZIP is not a valid SQLite database.');

            return self::FAILURE;
        }

        if (! $this->option('skip-pre-backup')) {
            $this->info('Creating safety backup before restore…');
            Artisan::call('app:backup', ['--trigger' => 'pre-restore', '--force' => true]);
        }

        $dbPath = database_path('database.sqlite');

        DB::disconnect();

        if (! copy($newDbPath, $dbPath)) {
            DB::reconnect();
            $this->cleanupDir($tempDir);
            $this->error('Failed to replace database file.');

            return self::FAILURE;
        }

        if ($this->option('include-settings')) {
            $settingsSource = $tempDir.DIRECTORY_SEPARATOR.'settingsData.json';
            if (file_exists($settingsSource)) {
                copy($settingsSource, storage_path('data/settingsData.json'));
                $this->info('settingsData.json restored.');
            }
        }

        $this->cleanupDir($tempDir);

        DB::reconnect();

        $this->info('Database restored successfully.');

        return self::SUCCESS;
    }

    private function isValidSQLite(string $path): bool
    {
        if (! file_exists($path) || filesize($path) < 16) {
            return false;
        }

        $handle = fopen($path, 'rb');
        $magic = fread($handle, 16);
        fclose($handle);

        return str_starts_with((string) $magic, 'SQLite format 3');
    }

    private function cleanupDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($dir);
    }
}
