<?php

/**
 * Config helper - Load environment configuration
 * 
 * Usage: config('app.debug')
 */
if (!function_exists('config')) {
    function config(string $key, $default = null) {
        $parts = explode('.', $key);
        $file = $parts[0];
        $key = $parts[1] ?? null;

        $configPath = __DIR__ . '/config/' . $file . '.php';
        
        if (!file_exists($configPath)) {
            return $default;
        }

        $config = require $configPath;

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

/**
 * env helper - Load environment variables
 */
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? null;

        if ($value === null) {
            return $default;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }
}

/**
 * response helper - Return JSON response
 */
if (!function_exists('response')) {
    function response() {
        return new class {
            public function json($data, $status = 200) {
                http_response_code($status);
                header('Content-Type: application/json');
                echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                exit;
            }
        };
    }
}
