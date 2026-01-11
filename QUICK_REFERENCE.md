# Hızlı Başvuru Kılavuzu
## RİBA Anket Yönetim Sistemi

**Güncellenme:** 11 Ocak 2026  
**Hedef Kitle:** Geliştiriciler ve Sistem Yöneticileri

---

## 🚀 Hızlı Başlangıç

### Sistem Gereksinimleri

```
✅ PHP 7.4+
✅ MySQL 5.7+
✅ Apache/Nginx
✅ PDO Extension
✅ JSON Extension
✅ mbstring Extension
```

### Kurulum (5 Dakika)

```bash
# 1. Dosyaları yükle
git clone https://github.com/alimustpdr/riba-survey-system.git
cd riba-survey-system

# 2. Database oluştur
mysql -u root -p
CREATE DATABASE riba_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Web installer'ı çalıştır
# Tarayıcıda: http://yourdomain.com/install.php
# Formu doldur ve tamamla

# 4. İlk giriş
# http://yourdomain.com/login.php
# Kurulumda belirlediğin email/şifre ile giriş
```

---

## 📁 Dosya Yapısı (Hızlı Referans)

```
admin/           → Süper Admin paneli
school/          → Okul Yöneticisi paneli
survey/          → Public anket sayfaları
includes/        → Shared kod (auth.php, db.php)
database/        → SQL dosyaları
config/          → Konfigürasyon (gitignore)
```

---

## 🔐 Güvenlik - Hızlı Kontrol

### ✅ Yapılması Gerekenler

```php
// 1. Output'ta her zaman e() kullan
<?= e($user_input) ?>

// 2. SQL'de her zaman prepared statements
$stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);

// 3. Form'larda CSRF token
<input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

// 4. POST'ta CSRF doğrula
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token');
}

// 5. Login check
require_login(); // veya require_super_admin() / require_school_admin()
```

### ❌ Yapılmaması Gerekenler

```php
// SQL injection risk
$query = "SELECT * FROM users WHERE id = " . $_GET['id']; // ❌

// XSS risk
echo $_POST['name']; // ❌

// CSRF risk
if ($_GET['action'] == 'delete') { /* delete */ } // ❌

// Password plain text
$password = $_POST['password']; 
INSERT INTO users (password) VALUES ('$password'); // ❌
```

---

## 🗄️ Database - Sık Kullanılan Queries

### Kullanıcı İşlemleri

```php
// Kullanıcı oluştur
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, school_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $school_id]);

// Kullanıcı bul
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Kullanıcı güncelle
$stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
$stmt->execute([$name, $email, $id]);
```

### Anket İşlemleri

```php
// Anket oluştur
$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare("INSERT INTO surveys (school_id, form_template_id, title, link_token) VALUES (?, ?, ?, ?)");
$stmt->execute([$school_id, $template_id, $title, $token]);

// Anket bul (token ile)
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE link_token = ? AND status = 'active'");
$stmt->execute([$token]);
$survey = $stmt->fetch();

// Yanıt kaydet
$stmt = $pdo->prepare("INSERT INTO responses (survey_id, answers, gender) VALUES (?, ?, ?)");
$stmt->execute([$survey_id, json_encode($answers), $gender]);
```

### School-Based Queries (Multi-tenant)

```php
// Okul yöneticisi - sadece kendi okulu
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);

// Süper admin - tüm okullar
$stmt = $pdo->query("SELECT * FROM schools ORDER BY name");
```

---

## 🎨 Frontend - Hızlı Referans

### Bootstrap Grid

```html
<!-- Responsive columns -->
<div class="row">
    <div class="col-12 col-md-6 col-lg-3">
        <!-- 100% mobile, 50% tablet, 25% desktop -->
    </div>
</div>
```

### Common Components

```html
<!-- Card -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5>Başlık</h5>
    </div>
    <div class="card-body">
        İçerik
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Modal Başlık</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Modal içerik
            </div>
        </div>
    </div>
</div>

<!-- Button trigger -->
<button data-bs-toggle="modal" data-bs-target="#myModal">Aç</button>
```

### Font Awesome Icons

```html
<i class="fas fa-user"></i>          <!-- User icon -->
<i class="fas fa-school"></i>        <!-- School icon -->
<i class="fas fa-clipboard-list"></i><!-- Survey icon -->
<i class="fas fa-chart-line"></i>    <!-- Chart icon -->
<i class="fas fa-sign-out-alt"></i>  <!-- Logout icon -->
```

---

## 🔧 Sık Karşılaşılan Sorunlar

### 1. "Permission denied" - config dizini

```bash
# Çözüm:
chmod 755 /path/to/project/config
chmod 644 /path/to/project/config/config.php
```

### 2. "Database connection failed"

```php
// config/config.php kontrol et:
define('DB_HOST', 'localhost');     // Doğru mu?
define('DB_NAME', 'riba_system');   // Database var mı?
define('DB_USER', 'riba_user');     // User doğru mu?
define('DB_PASS', 'password');      // Şifre doğru mu?

// MySQL test:
mysql -h localhost -u riba_user -p riba_system
```

### 3. "Session error"

```php
// Session dizini yazılabilir mi?
ls -la /tmp
# veya
php -i | grep session.save_path

# Çözüm:
sudo chmod 1777 /tmp
```

### 4. "CSRF token invalid"

```php
// Session çalışıyor mu kontrol:
session_start();
var_dump($_SESSION);

// Browser cookie'leri açık mı?
// Aynı domain'den mi istek geliyor?
```

### 5. "Install.php açılmıyor"

```bash
# .installed dosyası var mı kontrol:
ls -la config/.installed

# Varsa ve tekrar install etmek istiyorsan:
rm config/.installed
rm config/config.php
# Sonra tekrar install.php'yi aç
```

---

## 📊 Performans İyileştirme

### Database Optimization

```sql
-- Index'leri kontrol et
SHOW INDEX FROM surveys;

-- Slow query log aktif et
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Query performance analiz
EXPLAIN SELECT * FROM surveys WHERE school_id = 5;
```

### PHP Optimization

```ini
; php.ini ayarları
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

; Session ayarları
session.gc_maxlifetime=7200
session.cookie_lifetime=0
```

### Apache Optimization

```apache
# .htaccess - Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
</IfModule>
```

---

## 🧪 Test Senaryoları

### Functional Tests

```
1. Login
   ✓ Valid credentials → Success
   ✓ Invalid credentials → Error
   ✓ Inactive user → Blocked
   ✓ Wrong CSRF token → Blocked

2. Survey Creation
   ✓ School admin can create → Success
   ✓ Super admin can create → Success
   ✓ Unique token generated → Check
   ✓ Survey link works → Access

3. Survey Response
   ✓ Valid token → Show form
   ✓ Invalid token → Error
   ✓ All questions required → Validation
   ✓ Submit → Save + Redirect

4. Multi-tenant Isolation
   ✓ School A cannot see School B data
   ✓ Super admin can see all
   ✓ Token works across schools
```

### Security Tests

```
1. SQL Injection
   ✗ ' OR '1'='1 in email → Blocked
   ✗ '; DROP TABLE users-- → Blocked
   
2. XSS
   ✗ <script>alert('xss')</script> → Escaped
   ✗ <img src=x onerror=alert(1)> → Escaped

3. CSRF
   ✗ Cross-site form submit → Blocked
   ✗ Missing CSRF token → Blocked

4. Authentication
   ✗ Access admin without login → Redirect
   ✗ School admin access super admin → Blocked
```

---

## 🔍 Debugging

### Enable Debug Mode

```php
// config/config.php (SADECE DEVELOPMENT)
define('DEBUG_MODE', true);

// includes/db.php
if (DEBUG_MODE) {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
```

### Error Logging

```php
// Log error
error_log("[ERROR] User login failed: " . $email);

// Log to file
error_log("[ERROR] " . $message . "\n", 3, "/var/log/riba/app.log");

// View logs
tail -f /var/log/apache2/error.log
tail -f /var/log/riba/app.log
```

### Database Debugging

```php
// Show last query
var_dump($stmt->queryString);

// Show parameters
var_dump($stmt->debugDumpParams());

// Show affected rows
echo $stmt->rowCount();
```

---

## 📚 Kaynak Dökümanlar

1. **PROJECT_ANALYSIS.md** → Detaylı proje analizi
2. **SECURITY_REPORT.md** → Güvenlik raporu
3. **ARCHITECTURE.md** → Mimari dokümantasyonu
4. **PROJECT_REVIEW_SUMMARY.md** → Özet rapor
5. **README.md** → Kullanım kılavuzu

---

## 🆘 Acil Durum Komutları

### Backup

```bash
# Database backup
mysqldump -u riba_user -p riba_system > backup_$(date +%Y%m%d_%H%M%S).sql

# Full backup
tar -czf backup_full_$(date +%Y%m%d).tar.gz /var/www/html/riba

# Restore database
mysql -u riba_user -p riba_system < backup_20260111.sql
```

### Reset Admin Password

```php
// reset_password.php (one-time use)
<?php
require_once 'includes/db.php';

$email = 'admin@example.com';
$new_password = 'NewSecurePassword123!';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

echo "Password reset successful!";
// DELETE THIS FILE AFTER USE!
?>
```

### Clear All Sessions

```bash
# Find session path
php -i | grep session.save_path

# Delete all sessions
rm /tmp/sess_*
# or
rm /var/lib/php/sessions/sess_*
```

---

## 📞 Destek

### Hata Bildirimi

```
GitHub Issues: https://github.com/alimustpdr/riba-survey-system/issues

Bildirirken şunları ekle:
- PHP version
- MySQL version
- Error message (tam)
- Steps to reproduce
- Expected vs actual behavior
```

### Geliştirme Ortamı

```bash
# Recommended stack
PHP: 7.4 or 8.0
MySQL: 5.7 or 8.0
Web Server: Apache 2.4 or Nginx 1.18
OS: Ubuntu 20.04 LTS or CentOS 8
```

---

**Son Güncelleme:** 11 Ocak 2026  
**Versiyon:** 1.0  
**Hazırlayan:** GitHub Copilot Agent
