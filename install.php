<?php
session_start();

// Kurulum tamamlandıysa yönlendir
if (file_exists('config/.installed')) {
    header('Location: login.php');
    exit;
}

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

/**
 * Split SQL into executable statements safely.
 * Handles semicolons inside strings and SQL comments.
 */
function split_sql_statements(string $sql): array {
    $statements = [];
    $current = '';

    $len = strlen($sql);
    $inSingle = false;
    $inDouble = false;
    $inBacktick = false;
    $inLineComment = false;
    $inBlockComment = false;

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

        // End line comment at newline
        if ($inLineComment) {
            $current .= $ch;
            if ($ch === "\n") {
                $inLineComment = false;
            }
            continue;
        }

        // End block comment
        if ($inBlockComment) {
            $current .= $ch;
            if ($ch === '*' && $next === '/') {
                $current .= $next;
                $i++;
                $inBlockComment = false;
            }
            continue;
        }

        // Start comments (only when not inside quotes)
        if (!$inSingle && !$inDouble && !$inBacktick) {
            if ($ch === '-' && $next === '-') {
                // Treat "--" as line comment when followed by space/tab or endline
                $next2 = ($i + 2 < $len) ? $sql[$i + 2] : '';
                if ($next2 === ' ' || $next2 === "\t" || $next2 === "\r" || $next2 === "\n") {
                    $current .= $ch . $next;
                    $i++;
                    $inLineComment = true;
                    continue;
                }
            }
            if ($ch === '#') {
                $current .= $ch;
                $inLineComment = true;
                continue;
            }
            if ($ch === '/' && $next === '*') {
                $current .= $ch . $next;
                $i++;
                $inBlockComment = true;
                continue;
            }
        }

        // Quote state toggles (ignore when escaped)
        if ($inSingle) {
            $current .= $ch;
            if ($ch === "\\") {
                // Escape next char
                if ($next !== '') {
                    $current .= $next;
                    $i++;
                }
                continue;
            }
            if ($ch === "'" && $next === "'") {
                // SQL escaped single quote ''
                $current .= $next;
                $i++;
                continue;
            }
            if ($ch === "'") {
                $inSingle = false;
            }
            continue;
        }
        if ($inDouble) {
            $current .= $ch;
            if ($ch === "\\") {
                if ($next !== '') {
                    $current .= $next;
                    $i++;
                }
                continue;
            }
            if ($ch === '"' && $next === '"') {
                $current .= $next;
                $i++;
                continue;
            }
            if ($ch === '"') {
                $inDouble = false;
            }
            continue;
        }
        if ($inBacktick) {
            $current .= $ch;
            if ($ch === '`') {
                $inBacktick = false;
            }
            continue;
        }

        // Not currently in any quote/comment
        if ($ch === "'") {
            $inSingle = true;
            $current .= $ch;
            continue;
        }
        if ($ch === '"') {
            $inDouble = true;
            $current .= $ch;
            continue;
        }
        if ($ch === '`') {
            $inBacktick = true;
            $current .= $ch;
            continue;
        }

        // Statement delimiter
        if ($ch === ';') {
            $trimmed = trim($current);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $current = '';
            continue;
        }

        $current .= $ch;
    }

    $trimmed = trim($current);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF koruması
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Geçersiz form gönderimi!';
    } else {
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        $db_exists = isset($_POST['db_exists']) && $_POST['db_exists'] === '1';
        $admin_email = filter_var($_POST['admin_email'] ?? '', FILTER_SANITIZE_EMAIL);
        $admin_pass = $_POST['admin_pass'] ?? '';
        $admin_name = htmlspecialchars($_POST['admin_name'] ?? 'Süper Admin', ENT_QUOTES, 'UTF-8');
        
        if (empty($db_name) || empty($db_user) || empty($admin_email) || empty($admin_pass)) {
            $error = 'Lütfen tüm zorunlu alanları doldurun!';
        } elseif (strlen($admin_pass) < 6) {
            $error = 'Şifre en az 6 karakter olmalıdır!';
        } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi giriniz!';
        } else {
            try {
                // Veritabanı bağlantısı test et
                if ($db_exists) {
                    // DB zaten var, direkt bağlan
                    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db_user, $db_pass);
                } else {
                    // DB yoksa oluştur
                    $dsn = "mysql:host={$db_host};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db_user, $db_pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Veritabanını oluştur
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("USE `{$db_name}`");
                }
                
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                if (!$db_exists) {
                    $pdo->exec("USE `{$db_name}`");
                }
                
                // Tabloları oluştur
                $schema = file_get_contents('database/schema.sql');
                // SQL komutlarını güvenli biçimde ayır ve tek tek çalıştır
                $statements = split_sql_statements($schema);
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                
                // Form verilerini yükle
                if (file_exists('database/forms_data.sql')) {
                    $forms_data = file_get_contents('database/forms_data.sql');
                    $statements = split_sql_statements($forms_data);
                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                }
                
                // Süper admin oluştur
                $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'super_admin', 'active', NOW())");
                $stmt->execute([$admin_name, $admin_email, $hashed_pass]);
                
                // config.php dosyası oluştur
                $config_content = "<?php\n";
                $config_content .= "// Veritabanı Ayarları\n";
                $config_content .= "define('DB_HOST', '{$db_host}');\n";
                $config_content .= "define('DB_NAME', '{$db_name}');\n";
                $config_content .= "define('DB_USER', '{$db_user}');\n";
                $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n\n";
                $config_content .= "// Uygulama Ayarları\n";
                $config_content .= "define('APP_URL', '" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}');\n";
                $config_content .= "define('BASE_PATH', dirname(__FILE__) . '/..');\n\n";
                $config_content .= "// Güvenlik Ayarları\n";
                $config_content .= "define('SESSION_LIFETIME', 3600); // 1 saat\n";
                $config_content .= "?>\n";
                
                if (!is_dir('config')) {
                    mkdir('config', 0755, true);
                }
                
                file_put_contents('config/config.php', $config_content);
                file_put_contents('config/.installed', date('Y-m-d H:i:s'));
                
                // Güvenlik için config dizinini koru
                if (!file_exists('config/.htaccess')) {
                    file_put_contents('config/.htaccess', "Deny from all\n");
                }
                
                $success = 'Kurulum başarıyla tamamlandı! Yönlendiriliyorsunuz...';
                header('refresh:2;url=login.php');
                
            } catch (PDOException $e) {
                $error = 'Veritabanı hatası: ' . $e->getMessage();
            } catch (Exception $e) {
                $error = 'Hata: ' . $e->getMessage();
            }
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
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    
                    <h5 class="mb-3"><i class="fas fa-database text-primary"></i> Veritabanı Bilgileri</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Veritabanı Host</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Veritabanı Adı</label>
                        <input type="text" name="db_name" class="form-control" placeholder="riba_system" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="db_exists" value="1" id="db_exists">
                            <label class="form-check-label" for="db_exists">
                                Veritabanı CyberPanel'den zaten oluşturuldu
                            </label>
                        </div>
                        <small class="text-muted">CyberPanel'de veritabanını zaten oluşturduysanız bu seçeneği işaretleyin</small>
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