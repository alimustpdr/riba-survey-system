<?php
/**
 * Settings helpers (system + school scope).
 */

function get_setting(?int $school_id, string $key, $default = null) {
    require_once __DIR__ . '/db.php';
    global $pdo;

    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE school_id " . ($school_id === null ? "IS NULL" : "= ?") . " AND setting_key = ? LIMIT 1");
    $params = [];
    if ($school_id !== null) $params[] = $school_id;
    $params[] = $key;
    $stmt->execute($params);
    $row = $stmt->fetch();
    if (!$row) return $default;
    return $row['setting_value'];
}

function set_setting(?int $school_id, string $key, ?string $value): bool {
    require_once __DIR__ . '/db.php';
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO settings (school_id, setting_key, setting_value)
        VALUES (" . ($school_id === null ? "NULL" : "?") . ", ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $params = [];
    if ($school_id !== null) $params[] = $school_id;
    $params[] = $key;
    $params[] = $value;
    return $stmt->execute($params);
}

function get_enabled_kademes_for_school(int $school_id): array {
    $raw = get_setting($school_id, 'enabled_kademes', null);
    if (!$raw) {
        // Default: all enabled if not configured
        return ['okuloncesi', 'ilkokul', 'ortaokul', 'lise'];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['okuloncesi', 'ilkokul', 'ortaokul', 'lise'];
    }
    $allowed = ['okuloncesi', 'ilkokul', 'ortaokul', 'lise'];
    $out = [];
    foreach ($decoded as $k) {
        if (in_array($k, $allowed, true)) $out[] = $k;
    }
    return array_values(array_unique($out));
}

