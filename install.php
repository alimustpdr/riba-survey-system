<?php
session_start();

// Kurulum tamamlandıysa yönlendir
if (file_exists('config/.installed')) {
    header('Location: index.php');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Adım 2: Veritabanı bağlantısı test et ve kur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';
    
    // Veritabanı bağlantısı test et
    try {
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Veritabanı oluştur
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        // Tabloları oluştur
        $sql = file_get_contents('database/schema.sql');
        $pdo->exec($sql);
        
        // .env dosyası oluştur
        $env_content = "DB_HOST=$db_host\n";
        $env_content .= "DB_NAME=$db_name\n";
        $env_content .= "DB_USER=$db_user\n";
        $env_content .= "DB_PASS=$db_pass\n";
        $env_content .= "APP_URL=" . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "\n";
        
        file_put_contents('.env', $env_content);
        
        // Süper admin oluştur
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?, ?, 'super_admin', NOW())");
        $stmt->execute([$admin_email, $hashed_password]);
        
        // Kurulum tamamlandı işareti
        if (!is_dir('config')) mkdir('config', 0755, true);
        file_put_contents('config/.installed', date('Y-m-d H:i:s'));
        
        $success = 'Kurulum başarıyla tamamlandı! Giriş sayfasına yönlendiriliyorsunuz...';
        header('refresh:3;url=login.php');
        
    } catch (PDOException $e) {
        $error = 'Veritabanı hatası: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RİBA Sistem Kurulumu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .install-container { max-width: 600px; margin: 0 auto; }
        .install-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        .install-header { text-align: center; margin-bottom: 30px; }
        .install-header h1 { color: #667eea; font-weight: bold; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; border-bottom: 3px solid #e0e0e0; color: #999; }
        .step.active { border-bottom-color: #667eea; color: #667eea; font-weight: bold; }
        .step.completed { border-bottom-color: #10b981; color: #10b981; }
        .btn-primary { background: #667eea; border: none; padding: 12px 30px; }
        .btn-primary:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="install-card">
                <div class="install-header">
                    <i class="fas fa-clipboard-list fa-4x mb-3" style="color: #667eea;"></i>
                    <h1>RİBA Anket Sistemi</h1>
                    <p class="text-muted">Otomatik Kurulum Sihirbazı</p>
                </div>

                <div class="step-indicator">
                    <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i> Hoşgeldiniz
                    </div>
                    <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                        <i class="fas fa-database"></i> Veritabanı
                    </div>
                    <div class="step <?php echo $step >= 3 ? 'completed' : ''; ?>">
                        <i class="fas fa-check-circle"></i> Tamamlandı
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <div class="welcome-step">
                        <h4>Hoş Geldiniz!</h4>
                        <p>RİBA Anket Yönetim Sistemi kurulumuna hoş geldiniz. Bu sihirbaz size adım adım kurulum sürecinde yardımcı olacak.</p>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Kurulum Öncesi Gereksinimler:</h6>
                            <ul class="mb-0">
                                <li>PHP 7.4 veya üzeri</li>
                                <li>MySQL 5.7 veya üzeri</li>
                                <li>CyberPanel MySQL bilgileri</li>
                            </ul>
                        </div>

                        <div class="d-grid">
                            <a href="?step=2" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Kuruluma Başla
                            </a>
                        </div>
                    </div>
                <?php elseif ($step === 2): ?>
                    <div class="database-step">
                        <h4>Veritabanı Ayarları</h4>
                        <p class="text-muted">CyberPanel MySQL bilgilerinizi girin.</p>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Veritabanı Sunucusu</label>
                                <input type="text" name="db_host" class="form-control" value="localhost" required>
                                <small class="text-muted">Genellikle "localhost" olarak kalabilir</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Veritabanı Adı</label>
                                <input type="text" name="db_name" class="form-control" placeholder="riba_system" required>
                                <small class="text-muted">Yeni veritabanı adı (otomatik oluşturulacak)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">MySQL Kullanıcı Adı</label>
                                <input type="text" name="db_user" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">MySQL Şifre</label>
                                <input type="password" name="db_pass" class="form-control" required>
                            </div>

                            <hr class="my-4">

                            <h5>Süper Admin Hesabı</h5>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="admin_email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="admin_password" class="form-control" minlength="6" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-rocket"></i> Kurulumu Tamamla
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-3">
                <small class="text-white">RİBA Anket Sistemi v1.0 - 2026</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>