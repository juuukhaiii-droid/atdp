<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// This is a copy of public/index.php with paths adjusted by one directory
// level. It exists because Hostinger pins this domain's document root to
// the project root instead of /public (see .htaccess) - it was previously
// only a manual, untracked file on the server, so it's committed here now
// to survive a redeploy.

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
