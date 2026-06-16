<?php

declare(strict_types=1);

/**
 * Build .env from .env.render.example + container environment (Render / Railway).
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
    'RESEND_API_KEY',
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

/**
 * Wrap a value in double quotes if it contains characters that would
 * break dotenv parsing (spaces, #, ", etc.).
 */
function dotenv_quote(string $value): string
{
    if ($value === '') {
        return '""';
    }

    // Already quoted
    if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
        return $value;
    }

    // Quote if the value contains spaces, #, or double-quote characters
    if (preg_match('/[\s#"\\\\]/', $value)) {
        return '"'.addcslashes($value, '"\\').'"';
    }

    return $value;
}

/**
 * Extract the (unquoted) value portion of a `KEY=value` .env line.
 */
function env_line_value(string $line): string
{
    if (! str_contains($line, '=')) {
        return '';
    }

    $value = substr($line, strpos($line, '=') + 1);

    if (strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
        $value = stripcslashes(substr($value, 1, -1));
    }

    return $value;
}

foreach ($keys as $key) {
    $value = read_env($key, $containerExport);

    if ($value === null) {
        continue;
    }

    $lines[$key] = $key.'='.dotenv_quote($value);
}

$databaseUrl = read_env('DATABASE_URL', $containerExport);

if ($databaseUrl !== null && ! isset($lines['DB_URL'])) {
    $lines['DB_URL'] = 'DB_URL='.dotenv_quote($databaseUrl);
}

/**
 * Fallback: fill empty DB_* connection vars from Railway's native Postgres
 * variables (PG* / POSTGRES_*). Railway does not share vars between services
 * unless referenced, so a half-wired service can leave DB_HOST empty even
 * though the database exists — that produced cryptic "could not translate
 * host name" 500s. Harmless when DB_URL is set (Laravel's url wins).
 *
 * @var array<string, list<string>> $dbFallbacks
 */
$dbFallbacks = [
    'DB_HOST' => ['PGHOST'],
    'DB_PORT' => ['PGPORT'],
    'DB_DATABASE' => ['PGDATABASE', 'POSTGRES_DB'],
    'DB_USERNAME' => ['PGUSER', 'POSTGRES_USER'],
    'DB_PASSWORD' => ['PGPASSWORD', 'POSTGRES_PASSWORD'],
];

foreach ($dbFallbacks as $key => $sources) {
    if (isset($lines[$key]) && env_line_value($lines[$key]) !== '') {
        continue;
    }

    foreach ($sources as $source) {
        $value = read_env($source, $containerExport);

        if ($value !== null && $value !== '') {
            $lines[$key] = $key.'='.dotenv_quote($value);
            break;
        }
    }
}

$renderExternalUrl = read_env('RENDER_EXTERNAL_URL', $containerExport);

if ($renderExternalUrl !== null && ! isset($lines['APP_URL'])) {
    $host = str_starts_with($renderExternalUrl, 'http')
        ? $renderExternalUrl
        : 'https://'.$renderExternalUrl;
    $lines['APP_URL'] = 'APP_URL='.dotenv_quote($host);
}

$appKeyLine = $lines['APP_KEY'] ?? '';
$appKeyValue = str_starts_with($appKeyLine, 'APP_KEY=')
    ? substr($appKeyLine, strlen('APP_KEY='))
    : '';

if ($appKeyValue === '' || ! str_starts_with($appKeyValue, 'base64:')) {
    $lines['APP_KEY'] = 'APP_KEY=base64:'.base64_encode(random_bytes(32));
}

file_put_contents($envPath, implode("\n", $lines)."\n");

$dbHostValue = isset($lines['DB_HOST']) ? env_line_value($lines['DB_HOST']) : '';
$dbUrlValue = isset($lines['DB_URL']) ? env_line_value($lines['DB_URL']) : '';
$dbConfigured = $dbHostValue !== '' || $dbUrlValue !== '';

echo '[ensure-env] Wrote '.count($lines).' variables to '.$envPath.PHP_EOL;

$onRender = read_env('RENDER', $containerExport) !== null
    || read_env('RENDER_SERVICE_ID', $containerExport) !== null
    || read_env('RENDER_EXTERNAL_URL', $containerExport) !== null;

if ($onRender && count($lines) < 10) {
    fwrite(STDERR, '[ensure-env] ERROR: .env.render.example missing from image — only '.count($lines)." variables written\n");
    exit(1);
}

if (! $dbConfigured) {
    fwrite(STDERR, str_repeat('=', 72).PHP_EOL);
    fwrite(STDERR, '[ensure-env] ERROR: no database connection configured — every DB-backed'.PHP_EOL);
    fwrite(STDERR, '             request will return HTTP 500 (only /up will work).'.PHP_EOL);
    fwrite(STDERR, '             Neither DB_URL nor a non-empty DB_HOST resolved, and no'.PHP_EOL);
    fwrite(STDERR, '             Railway PG*/POSTGRES_* fallback was found.'.PHP_EOL);
    fwrite(STDERR, '             Railway: add a Variable Reference DATABASE_URL = ${{ <db-service>.DATABASE_URL }}'.PHP_EOL);
    fwrite(STDERR, '             Render:  link the PostgreSQL service to this web service.'.PHP_EOL);
    fwrite(STDERR, str_repeat('=', 72).PHP_EOL);
}
