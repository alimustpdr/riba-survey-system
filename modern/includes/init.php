<?php
/**
 * Modern UI Layer bootstrap.
 *
 * IMPORTANT:
 * - This is additive and does not replace existing pages.
 * - Uses existing auth/db helpers for compatibility.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

/**
 * Modern UI helper: build absolute URL safely.
 */
function modern_base_url(): string {
    if (defined('APP_URL') && is_string(APP_URL) && APP_URL !== '') {
        return rtrim(APP_URL, '/');
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return ($isHttps ? 'https' : 'http') . '://' . $host;
}

/**
 * Modern UI helper: flash message renderer.
 */
function modern_flash_html(): string {
    $flash = get_flash_message();
    if (!$flash) return '';
    $type = e($flash['type']);
    $msg = e($flash['message']);
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
        . $msg
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>'
        . '</div>';
}

