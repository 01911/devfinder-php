<?php
/**
 * DevFinder API - Main Entry Point
 */

// Change to app directory
chdir(__DIR__);

// Load environment variables
loadEnv(__DIR__ . '/.env');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', env('APP_DEBUG', 'false') === 'true' ? '1' : '0');

// Autoload composer dependencies and app classes
require __DIR__ . '/vendor/autoload.php';

// Include app helpers
require __DIR__ . '/app/Helpers/helpers.php';

// Create request from globals
$request = new \Illuminate\Http\Request();
$request->query = $_GET;
$request->post = $_POST;
$request->server = $_SERVER;
$request->headers = parseHeaders();

// Route the request
try {
    $router = new \App\Infrastructure\SimpleRouter();
    $router->dispatch($request);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => env('APP_DEBUG') ? $e->getMessage() : null,
        'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null,
    ]);
}

// Helper functions

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $content = file_get_contents($path);
    foreach (explode("\n", $content) as $line) {
        $line = trim($line);
        
        if (!$line || str_starts_with($line, '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

function parseHeaders(): array
{
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
            $headerKey = str_replace('HTTP_', '', $key);
            $headerKey = str_replace('_', '-', strtolower($headerKey));
            $headers[str_replace(' ', '-', ucwords(str_replace('-', ' ', $headerKey)))] = $value;
        }
    }
    if (isset($_SERVER['CONTENT_TYPE'])) {
        $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
    }
    return $headers;
}
