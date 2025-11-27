<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Clear dan buat folder bootstrap/cache
$bootstrapCache = __DIR__ . '/../bootstrap/cache';
if (is_dir($bootstrapCache)) {
    // Hapus semua file cache di bootstrap/cache
    array_map('unlink', glob("$bootstrapCache/*"));
}
if (!is_dir($bootstrapCache)) {
    mkdir($bootstrapCache, 0755, true);
}

// Buat folder storage di /tmp
$tmpDirs = [
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

// Bootstrap Laravel - SKIP CACHE
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage path
$app->useStoragePath('/tmp/storage');

// PENTING: Disable config cache
if (file_exists($configCache = __DIR__ . '/../bootstrap/cache/config.php')) {
    unlink($configCache);
}
if (file_exists($packagesCache = __DIR__ . '/../bootstrap/cache/packages.php')) {
    unlink($packagesCache);
}
if (file_exists($servicesCache = __DIR__ . '/../bootstrap/cache/services.php')) {
    unlink($servicesCache);
}

// Make kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle request
$response = $kernel->handle($request = Request::capture());
$response->send();
$kernel->terminate($request, $response);
