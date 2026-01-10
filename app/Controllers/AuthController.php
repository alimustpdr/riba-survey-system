<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (is_logged_in()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', [
            'title' => 'Login',
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash_error']);
    }

    /** @param array<string,string> $params */
    public function login(array $params = []): void
    {
        verify_csrf();

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $_SESSION['flash_error'] = 'Email and password are required.';
            $this->redirect('/login');
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            $_SESSION['flash_error'] = 'Invalid credentials.';
            $this->redirect('/login');
        }

        login_user($user);
        $this->redirect('/dashboard');
    }

    /** @param array<string,string> $params */
    public function logout(array $params = []): void
    {
        verify_csrf();
        logout_user();
        $this->redirect('/login');
    }
}
