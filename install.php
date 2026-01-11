<?php
session_start();

// Kurulum tamamlandıysa yönlendir
if (file_exists('config/.installed')) {
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
    $admin_pass = $_POST['admin_pass'] ?? '';
    $admin_name = $_POST['admin_name'] ?? 'Süper Admin';
    
    if (empty($db_name) || empty($db_user) || empty($admin_email) || empty($admin_pass)) {
        $error = 'Lütfen tüm alanları doldurun!';
    } else {
        try {
            // Veritabanı bağlantısı test et
            $dsn = "mysql:host={$db_host};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Veritabanını oluştur
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$db_name}`");
            
            // Tabloları oluştur
            $schema = file_get_contents('database/schema.sql');
            $pdo->exec($schema);
            
            // Form verilerini yükle
            $forms_data = file_get_contents('database/forms_data.sql');
            $pdo->exec($forms_data);
            
            // Süper admin oluştur
            $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'super_admin', 'active', NOW())");
            $stmt->execute([$admin_name, $admin_email, $hashed_pass]);
            
            // .env dosyası oluştur
            $env_content = "DB_HOST={$db_host}\n";
            $env_content .= "DB_NAME={$db_name}\n";
            $env_content .= "DB_USER={$db_user}\n";
            $env_content .= "DB_PASS={$db_pass}\n";
            $env_content .= "APP_URL=" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}\n";
            
            if (!is_dir('config')) mkdir('config', 0755, true);
            file_put_contents('config/.env', $env_content);
            file_put_contents('config/.installed', date('Y-m-d H:i:s'));
            
            $success = 'Kurulum başarıyla tamamlandı! Yönlendiriliyorsunuz...';
            header('refresh:2;url=login.php');
            
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .install-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 600px; margin: 20px auto; }
        .install-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px 15px 0 0; text-align: center; }
        .install-body { padding: 30px; }
        .form-label { font-weight: 600; color: #374151; }
        .btn-install { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; font-weight: 600; }
        .btn-install:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-card">
            <div class="install-header">
                <i class="fas fa-rocket fa-3x mb-3"></i>
                <h1>RİBA Anket Yönetim Sistemi</h1>
                <p class="mb-0">Otomatik Kurulum Sihirbazı</p>
            </div>
            <div class="install-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <h5 class="mb-3"><i class="fas fa-database text-primary"></i> Veritabanı Bilgileri</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Veritabanı Host</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Veritabanı Adı</label>
                        <input type="text" name="db_name" class="form-control" placeholder="riba_system" required>
                        <small class="text-muted">Yoksa otomatik oluşturulacak</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MySQL Kullanıcı Adı</label>
                            <input type="text" name="db_user" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MySQL Şifre</label>
                            <input type="password" name="db_pass" class="form-control">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3"><i class="fas fa-user-shield text-success"></i> Süper Admin Hesabı</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="admin_name" class="form-control" value="Süper Admin" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="admin_email" class="form-control" placeholder="admin@riba.com" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="admin_pass" class="form-control" required>
                        <small class="text-muted">En az 6 karakter</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-install w-100 mt-3">
                        <i class="fas fa-magic"></i> Kurulumu Başlat
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>