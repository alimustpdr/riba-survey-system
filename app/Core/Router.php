<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, regex:string, handler:string}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $pattern, string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, string $handler): void
    {
        $regex = $this->compilePattern($pattern);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    private function compilePattern(string $pattern): string
    {
        // Convert "/surveys/{id}/answer" to a regex with named captures
        $escaped = preg_replace('#\/#', '\\/', $pattern);
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $regex . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $method = strtoupper($method);

        $candidates = $this->routes[$method] ?? [];
        foreach ($candidates as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (is_string($k)) {
                        $params[$k] = $v;
                    }
                }
                $this->invoke($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    /** @param array<string, string> $params */
    private function invoke(string $handler, array $params = []): void
    {
        if (!str_contains($handler, '@')) {
            throw new \RuntimeException('Invalid handler: ' . $handler);
        }

        [$controllerShort, $method] = explode('@', $handler, 2);
        $class = 'App\\Controllers\\' . $controllerShort;

        if (!class_exists($class)) {
            throw new \RuntimeException('Controller not found: ' . $class);
        }

        $controller = new $class();
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException('Method not found: ' . $class . '::' . $method);
        }

        call_user_func([$controller, $method], $params);
    }
}
