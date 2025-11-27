<?php

$_SERVER['REQUEST_URI'] = '/' . trim($_GET['path'] ?? '', '/');

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// SEMUA writable directory HARUS di /tmp (Vercel read-only filesystem)
$tmpDirs = [
    '/tmp/bootstrap/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Maintenance mode check
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override semua path ke /tmp
$app->useStoragePath('/tmp/storage');
$app->useBootstrapPath('/tmp/bootstrap');

// Make kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle request
$response = $kernel->handle($request = Request::capture());
$response->send();
$kernel->terminate($request, $response);
