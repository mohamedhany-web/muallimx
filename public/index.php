<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

register_shutdown_function(static function () use ($app): void {
    $lastError = error_get_last();
    if (!$lastError) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($lastError['type'] ?? null, $fatalTypes, true)) {
        return;
    }

    try {
        $app->make(\Psr\Log\LoggerInterface::class)->error('Fatal shutdown error', [
            'message' => $lastError['message'] ?? null,
            'file' => $lastError['file'] ?? null,
            'line' => $lastError['line'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        ]);
    } catch (\Throwable $e) {
        // Avoid any secondary failures at shutdown.
    }
});

$app->handleRequest(Request::capture());
