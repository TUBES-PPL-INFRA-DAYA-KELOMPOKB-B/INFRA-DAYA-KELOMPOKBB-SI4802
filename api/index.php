<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Setup temporary storage directories in /tmp for serverless environment
$storagePath = '/tmp/storage';
$directories = [
    $storagePath . '/app/public',
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Register Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel application
/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bind storage path to /tmp
$app->useStoragePath($storagePath);

// Normalize script name for proper root path resolution
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Handle the incoming request
$app->handleRequest(Request::capture());
