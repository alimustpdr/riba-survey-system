<?php
session_start();

// Eğer zaten kurulmuşsa yönlendir
if (file_exists('.env') && filesize('.env') > 0) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';
    
    if (empty($db_name) || empty($db_user) || empty($admin_email) || empty($admin_password)) {
        $error = 'Tüm alanları doldurun!';
    } else {
        try {
            // Database bağlantısı test et
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Veritabanı oluştur
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");
            
            // Tabloları oluştur
            $sql = file_get_contents('database/schema.sql');
            $pdo->exec($sql);
            
            // Admin kullanıcısı oluştur
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role, status, created_at) VALUES (?, ?, 'super_admin', 'active', NOW())");
            $stmt->execute([$admin_email, $hashed_password]);
            
            // .env dosyası oluştur
            $env_content = "DB_HOST=$db_host\n";
            $env_content .= "DB_NAME=$db_name\n";
            $env_content .= "DB_USER=$db_user\n";
            $env_content .= "DB_PASS=$db_pass\n";
            $env_content .= "APP_URL=" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "\n";
            
            file_put_contents('.env', $env_content);
            
            $success = 'Kurulum başarıyla tamamlandı! 5 saniye içinde login sayfasına yönlendirileceksiniz...';
            header('refresh:5;url=login.php');
            
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .install-card { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-card">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-cog fa-3x text-primary mb-3"></i>
                        <h2>RİBA Sistem Kurulumu</h2>
                        <p class="text-muted">Rehberlik İhtiyacı Belirleme Anketi Yönetim Sistemi</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= $success ?>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <h5 class="mb-3">Veritabanı Bilgileri</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">MySQL Host</label>
                                <input type="text" name="db_host" class="form-control" value="localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Veritabanı Adı</label>
                                <input type="text" name="db_name" class="form-control" placeholder="riba_system" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">MySQL Kullanıcı Adı</label>
                                <input type="text" name="db_user" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">MySQL Şifre</label>
                                <input type="password" name="db_pass" class="form-control">
                                <small class="text-muted">Şifre yoksa boş bırakın</small>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Süper Admin Bilgileri</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="admin_email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="admin_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-rocket"></i> Kurulumu Başlat
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>