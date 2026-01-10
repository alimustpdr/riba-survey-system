<?php
/**
 * Minimal PSR-4 autoloader.
 *
 * Maps the `App\\` namespace to the `/app` directory.
 * This keeps the project framework-agnostic and avoids extra dependencies.
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR; // /app

    // Only handle classes in our App\ namespace.
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Strip namespace prefix.
    $relativeClass = substr($class, strlen($prefix));

    // Convert namespace separators to directory separators, append with .php.
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
