<?php
// Common utility functions

// Escape HTML output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Generate URL
function url($path = '') {
    $base = rtrim(APP_URL ?? '', '/');
    $path = ltrim($path, '/');
    return $base . ($path ? '/' . $path : '');
}

// Redirect helper
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// Generate random token
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Create slug from string
function create_slug($string) {
    $turkish = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
    $english = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
    $string = str_replace($turkish, $english, $string);
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Format date for display
function format_date($date, $format = 'd.m.Y H:i') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Get setting value
function get_setting($key, $default = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

// Set setting value
function set_setting($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Flash message functions
function set_flash($key, $message) {
    $_SESSION['flash_' . $key] = $message;
}

function get_flash($key) {
    if (isset($_SESSION['flash_' . $key])) {
        $message = $_SESSION['flash_' . $key];
        unset($_SESSION['flash_' . $key]);
        return $message;
    }
    return null;
}

function has_flash($key) {
    return isset($_SESSION['flash_' . $key]);
}

// Show flash message HTML
function show_flash($key, $class = 'info') {
    if ($message = get_flash($key)) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ][$class] ?? 'alert-info';
        
        return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">' .
               e($message) .
               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' .
               '</div>';
    }
    return '';
}

// Get form templates
function get_form_templates($kademe = null, $role = null) {
    global $pdo;
    $query = "SELECT * FROM form_templates WHERE 1=1";
    $params = [];
    
    if ($kademe) {
        $query .= " AND kademe = ?";
        $params[] = $kademe;
    }
    
    if ($role) {
        $query .= " AND role = ?";
        $params[] = $role;
    }
    
    $query .= " ORDER BY kademe, role";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get questions for a form template
function get_form_questions($form_template_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE form_template_id = ? ORDER BY question_number");
    $stmt->execute([$form_template_id]);
    return $stmt->fetchAll();
}

// Translate kademe to Turkish
function translate_kademe($kademe) {
    $translations = [
        'okuloncesi' => 'Okul Öncesi',
        'ilkokul' => 'İlkokul',
        'ortaokul' => 'Ortaokul',
        'lise' => 'Lise'
    ];
    return $translations[$kademe] ?? $kademe;
}

// Translate role to Turkish
function translate_role($role) {
    $translations = [
        'ogrenci' => 'Öğrenci',
        'veli' => 'Veli',
        'ogretmen' => 'Öğretmen'
    ];
    return $translations[$role] ?? $role;
}

// Validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Check if slug is unique for school
function is_unique_slug($slug, $exclude_id = null) {
    global $pdo;
    $query = "SELECT COUNT(*) FROM schools WHERE slug = ?";
    $params = [$slug];
    
    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}
