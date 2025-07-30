<?php

// Product Name : Car Booking
// Version      : 1.2

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$appUri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($appUri !== '/' && file_exists(__DIR__.'/public'.$appUri)) {
    return false;
}

// Check if public/index.php exists, if not include it directly
if (file_exists(__DIR__.'/public/index.php')) {
    require_once __DIR__.'/public/index.php';
} else {
    // Fallback: Include Laravel bootstrap directly
    define('LARAVEL_START', microtime(true));
    
    require __DIR__.'/vendor/autoload.php';
    
    $app = require_once __DIR__.'/bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    $response->send();
    
    $kernel->terminate($request, $response);
}
