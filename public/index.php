<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Storage bootstrap (PHP 8.4+ tempnam)
|--------------------------------------------------------------------------
|
| When storage/framework/views is missing or unwritable, tempnam() falls back
| to the system temp directory and PHP emits a warning that Laravel turns
| into a 500. Ensure required paths exist and prefer a project temp dir.
|
*/
$frameworkStorage = __DIR__.'/../storage/framework';

foreach (['views', 'cache/data', 'sessions', 'testing'] as $directory) {
    $path = $frameworkStorage.'/'.$directory;

    if (! is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

$projectTemp = $frameworkStorage.'/cache/data';

if (is_dir($projectTemp) && is_writable($projectTemp)) {
    putenv('TMPDIR='.$projectTemp);
    $_ENV['TMPDIR'] = $projectTemp;
    $_SERVER['TMPDIR'] = $projectTemp;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
