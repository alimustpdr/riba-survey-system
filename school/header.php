<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_school_admin();

$user = get_logged_in_user();
$page_title = $page_title ?? 'Dashboard';

// Okul bilgisini al
$stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
$stmt->execute([$user['school_id']]);
$school = $stmt->fetch();

if (!$school) {
    die('Okul bulunamadı!');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= e($school['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .main-content { padding: 20px; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .navbar { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar px-0">
                <div class="p-3 text-center border-bottom border-white border-opacity-25">
                    <h5><i class="fas fa-school"></i> <?= e($school['name']) ?></h5>
                    <small><?= e($user['name']) ?></small>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="/school/index.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'classes.php' ? 'active' : '' ?>" href="/school/classes.php">
                        <i class="fas fa-users"></i> Sınıflar
                    </a>
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'surveys.php' ? 'active' : '' ?>" href="/school/surveys.php">
                        <i class="fas fa-clipboard-list"></i> Anketler
                    </a>
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'survey-create.php' ? 'active' : '' ?>" href="/school/survey-create.php">
                        <i class="fas fa-plus-circle"></i> Yeni Anket
                    </a>
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'form-templates.php' ? 'active' : '' ?>" href="/school/form-templates.php">
                        <i class="fas fa-clipboard-list"></i> Form Şablonları
                    </a>
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'riba-class-export.php' ? 'active' : '' ?>" href="/school/riba-class-export.php">
                        <i class="fas fa-file-excel"></i> RİBA Excel
                    </a>
                    <hr class="text-white">
                    <a class="nav-link" href="/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <nav class="navbar navbar-expand-lg mb-4">
                    <div class="container-fluid">
                        <h5 class="mb-0"><?= e($page_title) ?></h5>
                        <span class="text-muted">
                            <i class="fas fa-user"></i> <?= e($user['email']) ?>
                        </span>
                    </div>
                </nav>
                
                <?php
                $flash = get_flash_message();
                if ($flash):
                ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <div class="page-content">
