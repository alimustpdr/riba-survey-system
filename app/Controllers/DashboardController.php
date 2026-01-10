<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class DashboardController extends Controller
{
    /** @param array<string,string> $params */
    public function index(array $params = []): void
    {
        $this->requireAuth();
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => current_user(),
        ]);
    }
}
