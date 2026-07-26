<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

/*
 * Deploy this file as /public_html/App/index.php.
 * The complete Laravel application must be deployed in /public_html/App/laravel.
 *
 * Every request, including /App/index.php/shop and /App/index.php/admin,
 * is passed to Laravel by this single front controller.
 */
define('LARAVEL_START', microtime(true));

$laravelPath = __DIR__.'/laravel';

if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
