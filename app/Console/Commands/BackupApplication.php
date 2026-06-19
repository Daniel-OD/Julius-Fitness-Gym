<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use ZipArchive;

#[Description('Create a zip backup of the database and settings to the configured folder')]
#[Signature('app:backup {--trigger= : Trigger type: manual, after_member, end_of_day, pre-restore} {--force : Run even when backup is disabled}')]
class BackupApplication extends Command
{
    public function handle(): int
    {
        $settings = Helpers::getSettings();
        $backup = is_array($settings['backup'] ?? null) ? $settings['backup'] : [];

        $isForced = (bool) $this->option('force');

        if (! $isForced && empty($backup['enabled'])) {
            $this->info('Backup is disabled.');

            return self::SUCCESS;
        }

        $path = trim((string) ($backup['path'] ?? ''));

        if ($path === '') {
            $this->error('Backup path is not configured.');

            return self::FAILURE;
        }

        $path = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        if (! is_dir($path) && ! mkdir($path, 0755, true)) {
            $this->error("Cannot create backup directory: {$path}");

            return self::FAILURE;
        }

        $filename = 'julius-gym-backup-'.now()->format('Y-m-d_H-i-s').'.zip';
        $zipPath = $path.DIRECTORY_SEPARATOR.$filename;

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Cannot create ZIP archive: {$zipPath}");

            return self::FAILURE;
        }

        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            $zip->addFile($dbPath, 'database.sqlite');
        }

        $settingsPath = storage_path('data/settingsData.json');
        if (file_exists($settingsPath)) {
            $zip->addFile($settingsPath, 'settingsData.json');
        }

        $zip->close();

        $keep = max(1, (int) ($backup['keep_backups'] ?? 7));
        $this->rotateBackups($path, $keep);

        $this->info("Backup created: {$zipPath}");

        return self::SUCCESS;
    }

    private function rotateBackups(string $path, int $keep): void
    {
        $files = glob($path.DIRECTORY_SEPARATOR.'julius-gym-backup-*.zip');

        if ($files === false || count($files) <= $keep) {
            return;
        }

        sort($files);

        foreach (array_slice($files, 0, count($files) - $keep) as $old) {
            @unlink($old);
        }
    }
}
