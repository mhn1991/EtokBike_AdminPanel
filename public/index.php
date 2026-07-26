<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
 * Supports both the normal Laravel public directory and the shared-hosting
 * layout where this file sits beside a "laravel" application directory.
 */
$basePath = dirname(__DIR__);

if (! is_file($basePath.'/vendor/autoload.php') && is_file(__DIR__.'/laravel/vendor/autoload.php')) {
    $basePath = __DIR__.'/laravel';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
