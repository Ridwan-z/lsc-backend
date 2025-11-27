<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Create writable directories in /tmp for Vercel
$tmpDirs = [
    '/tmp/views',
    '/tmp/cache',
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

// Set writable paths for Laravel
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// Maintenance mode check
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage paths for Vercel
$app->useStoragePath('/tmp/storage');

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
