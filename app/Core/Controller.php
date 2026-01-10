<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $v = new View();
        $v->render($view, $data, $layout);
    }

    protected function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!is_logged_in()) {
            $this->redirect('/login');
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        if (!has_role($role)) {
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }
}
