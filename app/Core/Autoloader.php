<?php
declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = rtrim($baseDir, '/');
    }

    public function register(): void
    {
        spl_autoload_register(function (string $class): void {
            // Only autoload our App namespace
            if (!str_starts_with($class, 'App\\')) {
                return;
            }

            $relative = substr($class, 4); // remove "App\"
            $relativePath = str_replace('\\', '/', $relative);
            $file = $this->baseDir . $relativePath . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}
