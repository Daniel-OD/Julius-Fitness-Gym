<?php

declare(strict_types=1);

/**
 * Build .env from container environment (Render) and guarantee a valid APP_KEY.
 * PHP-FPM may not pass env vars to workers; Laravel needs them in .env on disk.
 */
$envPath = $argv[1] ?? (getcwd().DIRECTORY_SEPARATOR.'.env');

$keys = [
    'APP_NAME',
    'APP_ENV',
    'APP_DEBUG',
    'APP_KEY',
    'APP_URL',
    'APP_LOCALE',
    'APP_FALLBACK_LOCALE',
    'LOG_CHANNEL',
    'LOG_LEVEL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'SESSION_DRIVER',
    'CACHE_STORE',
    'QUEUE_CONNECTION',
    'FILESYSTEM_DISK',
    'MAIL_MAILER',
    'MAIL_FROM_ADDRESS',
    'MAIL_FROM_NAME',
];

/** @var array<string, string> $lines */
$lines = [];

if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $trim = trim($line);

        if ($trim === '' || str_starts_with($trim, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key] = explode('=', $line, 2);
        $lines[trim($key)] = $line;
    }
}

foreach ($keys as $key) {
    $value = getenv($key);

    if ($value === false || $value === '') {
        continue;
    }

    $lines[$key] = $key.'='.$value;
}

$appKeyLine = $lines['APP_KEY'] ?? '';
$appKeyValue = str_starts_with($appKeyLine, 'APP_KEY=')
    ? substr($appKeyLine, strlen('APP_KEY='))
    : '';

if ($appKeyValue === '' || ! str_starts_with($appKeyValue, 'base64:')) {
    $lines['APP_KEY'] = 'APP_KEY=base64:'.base64_encode(random_bytes(32));
}

file_put_contents($envPath, implode("\n", $lines)."\n");

echo '[ensure-env] Wrote '.count($lines)." variables to {$envPath}\n";
