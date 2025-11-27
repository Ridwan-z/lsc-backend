<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// PENTING: Buat folder bootstrap/cache dengan benar
$bootstrapCache = __DIR__ . '/../bootstrap/cache';

// Jangan hapus, langsung cek dan buat
if (!is_dir($bootstrapCache)) {
    @mkdir($bootstrapCache, 0777, true);
}

// Set permissions (untuk memastikan writable)
@chmod($bootstrapCache, 0777);

// Buat folder storage di /tmp
$tmpDirs = [
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

// Override storage path
$app->useStoragePath('/tmp/storage');

// Make kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle request
$response = $kernel->handle($request = Request::capture());
$response->send();
$kernel->terminate($request, $response);
