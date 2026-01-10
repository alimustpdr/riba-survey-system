<?php
declare(strict_types=1);

/**
 * Auth + CSRF helpers.
 * Kept global for simplicity in a plain PHP project.
 */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $t . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $valid = $_SESSION['_csrf_token'] ?? '';
    if (!is_string($sent) || $sent === '' || !hash_equals((string)$valid, $sent)) {
        http_response_code(419);
        echo '419 CSRF token mismatch.';
        exit;
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

/** @return array<string, mixed>|null */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function has_role(string $role): bool
{
    $u = current_user();
    if (!$u) return false;
    return isset($u['role']) && $u['role'] === $role;
}

function login_user(array $user): void
{
    // Regenerate session id on login
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}
