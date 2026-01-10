<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        $layoutPath = BASE_PATH . '/app/Views/' . $layout . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }
        if (!is_file($layoutPath)) {
            throw new \RuntimeException('Layout not found: ' . $layoutPath);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string)ob_get_clean();

        require $layoutPath;
    }
}
