<?php
declare(strict_types=1);

// Basic bootstrap for plain PHP app.
// Keep public/ free of application logic besides requiring this file.

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

// Sessions
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Environment (.env is optional; copy from .env.example)
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if ($k !== '' && getenv($k) === false) {
            putenv($k . '=' . $v);
            $_ENV[$k] = $v;
        }
    }
}

require BASE_PATH . '/config/database.php';

require BASE_PATH . '/app/Core/Autoloader.php';

$autoloader = new App\Core\Autoloader(BASE_PATH . '/app');
$autoloader->register();

require BASE_PATH . '/app/Helpers/auth.php';

// Ensure CSRF token exists
csrf_token();
