<?php

declare(strict_types=1);

/**
 * Build .env from .env.render.example + container environment (Render).
 * PHP-FPM workers may not inherit env vars; Laravel must read them from disk.
 */
$envPath = $argv[1] ?? (getcwd().DIRECTORY_SEPARATOR.'.env');
$templatePath = dirname($envPath).DIRECTORY_SEPARATOR.'.env.render.example';

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
    'DB_URL',
    'SESSION_DRIVER',
    'SESSION_LIFETIME',
    'SESSION_ENCRYPT',
    'SESSION_SECURE_COOKIE',
    'BROADCAST_CONNECTION',
    'CACHE_STORE',
    'QUEUE_CONNECTION',
    'FILESYSTEM_DISK',
    'MAIL_MAILER',
    'MAIL_FROM_ADDRESS',
    'MAIL_FROM_NAME',
];

if (is_file($templatePath)) {
    copy($templatePath, $envPath);
} elseif (! is_file($envPath)) {
    touch($envPath);
}

/** @var array<string, string> $containerExport */
$containerExport = [];

$exportPath = '/tmp/render-container.env';

if (is_file($exportPath)) {
    foreach (file($exportPath, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $containerExport[trim($key)] = $value;
    }
}

/** @var array<string, string> $lines */
$lines = [];

foreach (file($envPath, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
    $trim = trim($line);

    if ($trim === '' || str_starts_with($trim, '#') || ! str_contains($line, '=')) {
        continue;
    }

    [$key] = explode('=', $line, 2);
    $lines[trim($key)] = $line;
}

/**
 * @param  array<string, string>  $containerExport
 */
function read_env(string $key, array $containerExport): string|false|null
{
    if (isset($containerExport[$key]) && $containerExport[$key] !== '') {
        return $containerExport[$key];
    }

    $value = getenv($key, local_only: false);

    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }

    return null;
}

foreach ($keys as $key) {
    $value = read_env($key, $containerExport);

    if ($value === null) {
        continue;
    }

    $lines[$key] = $key.'='.$value;
}

$databaseUrl = read_env('DATABASE_URL', $containerExport);

if ($databaseUrl !== null && ! isset($lines['DB_URL'])) {
    $lines['DB_URL'] = 'DB_URL='.$databaseUrl;
}

$renderExternalUrl = read_env('RENDER_EXTERNAL_URL', $containerExport);

if ($renderExternalUrl !== null && ! isset($lines['APP_URL'])) {
    $host = str_starts_with($renderExternalUrl, 'http')
        ? $renderExternalUrl
        : 'https://'.$renderExternalUrl;
    $lines['APP_URL'] = 'APP_URL='.$host;
}

$appKeyLine = $lines['APP_KEY'] ?? '';
$appKeyValue = str_starts_with($appKeyLine, 'APP_KEY=')
    ? substr($appKeyLine, strlen('APP_KEY='))
    : '';

if ($appKeyValue === '' || ! str_starts_with($appKeyValue, 'base64:')) {
    $lines['APP_KEY'] = 'APP_KEY=base64:'.base64_encode(random_bytes(32));
}

file_put_contents($envPath, implode("\n", $lines)."\n");

$dbConfigured = isset($lines['DB_HOST']) || isset($lines['DB_URL']);

echo '[ensure-env] Wrote '.count($lines).' variables to '.$envPath.PHP_EOL;

$onRender = read_env('RENDER', $containerExport) !== null
    || read_env('RENDER_SERVICE_ID', $containerExport) !== null
    || read_env('RENDER_EXTERNAL_URL', $containerExport) !== null;

if ($onRender && count($lines) < 10) {
    fwrite(STDERR, '[ensure-env] ERROR: .env.render.example missing from image — only '.count($lines)." variables written\n");
    exit(1);
}

if ($onRender && ! $dbConfigured) {
    fwrite(STDERR, '[ensure-env] WARNING: DB_HOST/DB_URL missing — link PostgreSQL to this web service in Render'.PHP_EOL);
}
