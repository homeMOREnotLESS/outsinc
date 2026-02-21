<?php
/**
 * Application Bootstrap
 * Initializes environment, autoloading, and core services
 */

// Get base path
define('BASE_PATH', dirname(dirname(__FILE__)));
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load environment variables
if (file_exists(BASE_PATH . '/.env')) {
    $envFile = file(BASE_PATH . '/.env');
    foreach ($envFile as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            $_SERVER[trim($key)] = trim($value);
        }
    }
}

// Set default environment
if (!isset($_ENV['APP_ENV'])) {
    $_ENV['APP_ENV'] = 'development';
}

error_reporting(E_ALL);
if ($_ENV['APP_ENV'] === 'development') {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}

// Simple autoloader for App namespace
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $path = BASE_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// Initialize session
session_set_cookie_params([
    'lifetime' => intval($_ENV['SESSION_TIMEOUT'] ?? 1800),
    'path' => '/',
    'secure' => $_ENV['SESSION_SECURE'] === 'true',
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('America/Toronto');
