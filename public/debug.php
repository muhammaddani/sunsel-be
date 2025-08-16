<?php
// Debug script - save as debug.php in your public directory
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

echo "<h2>Laravel Debug Information</h2>";

echo "<h3>Environment</h3>";
echo "APP_ENV: " . env('APP_ENV', 'Not set') . "<br>";
echo "APP_URL: " . env('APP_URL', 'Not set') . "<br>";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "<br>";

echo "<h3>Paths</h3>";
echo "Base Path: " . base_path() . "<br>";
echo "Public Path: " . public_path() . "<br>";

echo "<h3>Routes</h3>";
try {
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
        return str_starts_with($route->uri(), 'api/');
    })->map(function ($route) {
        return $route->methods()[0] . ' ' . $route->uri();
    });

    foreach ($routes as $route) {
        echo $route . "<br>";
    }
} catch (Exception $e) {
    echo "Error loading routes: " . $e->getMessage() . "<br>";
}

echo "<h3>File Permissions</h3>";
$paths = [
    'storage' => storage_path(),
    'cache' => storage_path('framework/cache'),
    'views' => storage_path('framework/views'),
    'bootstrap/cache' => base_path('bootstrap/cache'),
];

foreach ($paths as $name => $path) {
    $perms = fileperms($path);
    echo "$name: " . substr(sprintf('%o', $perms), -4) . "<br>";
}
?>
