<?php
// Authentication and session management

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user data
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'school_id' => $_SESSION['school_id'] ?? null
    ];
}

// Check if user has specific role
function has_role($role) {
    return is_logged_in() && $_SESSION['user_role'] === $role;
}

// Require login (redirect to login if not logged in)
function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

// Require specific role
function require_role($role) {
    require_login();
    if (!has_role($role)) {
        http_response_code(403);
        die('Bu sayfaya erişim yetkiniz yok.');
    }
}

// Login user
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['school_id'] = $user['school_id'];
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

// Logout user
function logout_user() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get CSRF token input field
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}
