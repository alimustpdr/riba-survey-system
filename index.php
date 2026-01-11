<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login
require_login();

$user = get_current_user();

// Role-based routing
if ($user['role'] === 'super_admin') {
    header('Location: admin/index.php');
} elseif ($user['role'] === 'school_admin') {
    header('Location: school/index.php');
} else {
    // Unknown role
    logout_user();
    header('Location: login.php');
}
exit;
