<?php
// Kimlik doğrulama ve oturum yönetimi

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    ini_set('session.cookie_secure', $isHttps ? 1 : 0);
    // Helps mitigate CSRF while keeping UX (works well for typical form POSTs)
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// CSRF token oluştur
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token doğrula
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Kullanıcı giriş kontrolü
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Kullanıcı çıkış
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Giriş zorunluluğu
function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

// Rol kontrolü
function require_role($role) {
    require_login();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: /index.php');
        exit;
    }
}

// Super admin kontrolü
function require_super_admin() {
    require_role('super_admin');
}

// Okul admin kontrolü
function require_school_admin() {
    require_role('school_admin');
}

// Kullanıcı bilgilerini al
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    require_once __DIR__ . '/db.php';
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, name, email, role, school_id, status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// XSS koruması için output escaping
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Başarı mesajı
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Flash mesaj göster
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
?>
