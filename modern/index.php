<?php
require_once __DIR__ . '/includes/init.php';

if (!is_logged_in()) {
    header('Location: /login.php');
    exit;
}

$user = get_logged_in_user();
if (!$user) {
    logout();
    header('Location: /login.php');
    exit;
}

if ($user['role'] === 'super_admin') {
    header('Location: /modern/admin/dashboard.php');
    exit;
}
if ($user['role'] === 'school_admin') {
    header('Location: /modern/school/dashboard.php');
    exit;
}

logout();
header('Location: /login.php');
exit;

