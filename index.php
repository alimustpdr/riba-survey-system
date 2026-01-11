<?php
require_once 'includes/auth.php';

// Giriş yapmamışsa login'e yönlendir
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Role göre yönlendir
if ($_SESSION['user_role'] === 'super_admin') {
    header('Location: admin/index.php');
} elseif ($_SESSION['user_role'] === 'school_admin') {
    header('Location: school/index.php');
} else {
    // Bilinmeyen rol
    logout();
    header('Location: login.php');
}
exit;
?>
