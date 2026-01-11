<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/settings.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Fetch active packages (optional)
$packages = [];
try {
    $stmt = $pdo->query("SELECT id, name, price, duration_days, max_students, max_surveys FROM packages WHERE status = 'active' ORDER BY price ASC");
    $packages = $stmt->fetchAll();
} catch (Exception $e) {
    // packages table exists in schema; ignore in case of partial setups
}

$csrf_token = generate_csrf_token();
$kademes = ['okuloncesi' => 'Okul Öncesi', 'ilkokul' => 'İlkokul', 'ortaokul' => 'Ortaokul', 'lise' => 'Lise'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $error = 'Geçersiz form gönderimi!';
    } else {
        $school_name = trim($_POST['school_name'] ?? '');
        $slug = strtolower(trim($_POST['slug'] ?? ''));
        $admin_name = trim($_POST['admin_name'] ?? '');
        $admin_email = filter_var($_POST['admin_email'] ?? '', FILTER_SANITIZE_EMAIL);
        $admin_pass = (string)($_POST['admin_pass'] ?? '');
        $selected_kademes = $_POST['kademes'] ?? [];
        $package_id = (int)($_POST['package_id'] ?? 0);
        $paid_confirmed = isset($_POST['paid_confirmed']) && $_POST['paid_confirmed'] === '1';

        if ($school_name === '' || $slug === '' || $admin_name === '' || $admin_email === '' || $admin_pass === '') {
            $error = 'Lütfen tüm zorunlu alanları doldurun!';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $error = 'Slug sadece küçük harf, rakam ve tire içermelidir!';
        } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi giriniz!';
        } elseif (strlen($admin_pass) < 6) {
            $error = 'Şifre en az 6 karakter olmalıdır!';
        } elseif (!$paid_confirmed) {
            $error = 'Devam etmek için “Ödemeyi yaptım” kutucuğunu işaretlemelisiniz.';
        } else {
            $enabled = [];
            foreach ($selected_kademes as $k) {
                if (isset($kademes[$k])) $enabled[] = $k;
            }
            $enabled = array_values(array_unique($enabled));
            if (empty($enabled)) {
                $error = 'Lütfen en az bir kademe seçin!';
            } else {
                try {
                    $pdo->beginTransaction();

                    // slug unique
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM schools WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if ((int)$stmt->fetchColumn() > 0) {
                        throw new RuntimeException('Bu slug zaten kullanılıyor!');
                    }

                    // email unique
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                    $stmt->execute([$admin_email]);
                    if ((int)$stmt->fetchColumn() > 0) {
                        throw new RuntimeException('Bu e-posta adresi zaten kullanılıyor!');
                    }

                    $expire_date = null;
                    if ($package_id > 0) {
                        $stmt = $pdo->prepare("SELECT duration_days FROM packages WHERE id = ? AND status = 'active'");
                        $stmt->execute([$package_id]);
                        $pkg = $stmt->fetch();
                        if ($pkg) {
                            $days = (int)$pkg['duration_days'];
                            if ($days > 0) {
                                $expire_date = (new DateTimeImmutable('now'))->modify('+' . $days . ' days')->format('Y-m-d');
                            }
                        } else {
                            $package_id = 0;
                        }
                    }

                    $stmt = $pdo->prepare("INSERT INTO schools (name, slug, status, package_id, expire_date) VALUES (?, ?, 'active', ?, ?)");
                    $stmt->execute([$school_name, $slug, $package_id ?: null, $expire_date]);
                    $school_id = (int)$pdo->lastInsertId();

                    $hashed = password_hash($admin_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, school_id, status) VALUES (?, ?, ?, 'school_admin', ?, 'active')");
                    $stmt->execute([$admin_name, $admin_email, $hashed, $school_id]);
                    $user_id = (int)$pdo->lastInsertId();

                    // Save enabled kademes to settings
                    set_setting($school_id, 'enabled_kademes', json_encode($enabled, JSON_UNESCAPED_UNICODE));

                    $pdo->commit();

                    // Auto-login and redirect to school panel
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $admin_name;
                    $_SESSION['user_email'] = $admin_email;
                    $_SESSION['user_role'] = 'school_admin';
                    $_SESSION['school_id'] = $school_id;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header('Location: /school/index.php');
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Kayıt sırasında hata: ' . $e->getMessage();
                }
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
    <title>Üyelik Oluştur - RİBA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 50%, #22c55e 100%); min-height: 100vh; display: flex; align-items: center; }
        .cardx { background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.20); max-width: 720px; margin: 20px auto; }
        .head { background: rgba(2,6,23,0.85); color: white; padding: 24px; border-radius: 16px 16px 0 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="cardx">
            <div class="head">
                <h3 class="mb-1"><i class="fas fa-user-plus"></i> Üyelik Oluştur</h3>
                <div class="opacity-75">Okul bilgilerinizi girin, kademenizi seçin ve paneliniz otomatik açılsın.</div>
            </div>
            <div class="p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                    <h6 class="mb-3">Okul Bilgileri</h6>
                    <div class="row g-2">
                        <div class="col-md-8">
                            <label class="form-label">Okul Adı *</label>
                            <input class="form-control" name="school_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug *</label>
                            <input class="form-control" name="slug" required pattern="[a-z0-9-]+" placeholder="ornek-okul">
                            <small class="text-muted">Sadece küçük harf, rakam, tire</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Kademenizi Seçin *</h6>
                    <div class="row g-2">
                        <?php foreach ($kademes as $key => $label): ?>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kademes[]" value="<?= e($key) ?>" id="k_<?= e($key) ?>">
                                    <label class="form-check-label" for="k_<?= e($key) ?>"><?= e($label) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Seçtiğiniz kademeye uygun formlar panelinizde görünür. (Okul öncesinde öğrenci formu yoktur.)
                    </small>

                    <hr class="my-4">
                    <h6 class="mb-3">Paket (opsiyonel)</h6>
                    <select class="form-select" name="package_id">
                        <option value="0">Şimdilik paket seçmeyeceğim</option>
                        <?php foreach ($packages as $p): ?>
                            <option value="<?= (int)$p['id'] ?>">
                                <?= e($p['name']) ?> — <?= number_format((float)$p['price'], 2, ',', '.') ?> ₺ / <?= (int)$p['duration_days'] ?> gün
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Ödeme entegrasyonu daha sonra eklenecek; şimdilik onay kutusu ile ilerlenir.</small>

                    <hr class="my-4">
                    <h6 class="mb-3">Okul Yöneticisi Hesabı</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad *</label>
                            <input class="form-control" name="admin_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-posta *</label>
                            <input class="form-control" type="email" name="admin_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Şifre *</label>
                            <input class="form-control" type="password" name="admin_pass" required minlength="6">
                            <small class="text-muted">En az 6 karakter</small>
                        </div>
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="paid_confirmed" value="1" id="paid_confirmed">
                        <label class="form-check-label" for="paid_confirmed">
                            Ödemeyi yaptım (demo)
                        </label>
                    </div>

                    <div class="d-grid mt-4">
                        <button class="btn btn-primary btn-lg" type="submit">
                            <i class="fas fa-check"></i> Kaydı Tamamla ve Panele Git
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="/login.php">Zaten hesabın var mı? Giriş yap</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

