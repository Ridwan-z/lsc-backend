<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Create all writable directories in /tmp for Vercel
$tmpDirs = [
    '/tmp/bootstrap/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Maintenance mode check
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage path for Vercel read-only filesystem
$app->useStoragePath('/tmp/storage');

// Bind bootstrap path manually
$app->instance('path.bootstrap', '/tmp/bootstrap');

// Make kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Capture request
$request = Request::capture();

// Handle request
$response = $kernel->handle($request);

// Send response
$response->send();

// Terminate
$kernel->terminate($request, $response);
