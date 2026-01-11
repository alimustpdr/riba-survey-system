<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/ensure_schema.php';

// Only logged-in admins can run setup
require_login();
$user = get_logged_in_user();
if (!$user) {
    logout();
    header('Location: /login.php');
    exit;
}
if (!in_array($user['role'], ['super_admin', 'school_admin'], true)) {
    header('Location: /index.php');
    exit;
}

$page_title = 'Modern Setup';
$active_nav = null;

// Detect table
$has_ctx = false;
try {
    $pdo->query("SELECT 1 FROM response_context LIMIT 1");
    $has_ctx = true;
} catch (Throwable $e) {
    $has_ctx = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
        header('Location: /modern/setup.php');
        exit;
    }
    try {
        modern_ensure_response_context_table($pdo);
        set_flash_message('Kurulum tamamlandı: response_context tablosu hazır.', 'success');
    } catch (Throwable $e) {
        set_flash_message('Kurulum sırasında hata: ' . $e->getMessage(), 'danger');
    }
    header('Location: /modern/setup.php');
    exit;
}

$csrf_token = generate_csrf_token();

require_once __DIR__ . '/partials/layout-header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h3 class="mb-1">Modern Setup</h3>
        <div class="muted">Modern arayüzün ek tablolarını güvenli şekilde hazırlar.</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-light" href="/modern/">
            <i class="fas fa-arrow-left"></i> Modern Panele Dön
        </a>
    </div>
</div>

<?= modern_flash_html() ?>

<div class="cardx p-3">
    <h5 class="mb-2"><i class="fas fa-database"></i> Veritabanı Kontrol</h5>
    <div class="muted small mb-3">
        Bu işlem **mevcut tabloları silmez/değiştirmez**. Sadece eksikse <code>CREATE TABLE IF NOT EXISTS</code> ile ek tablo oluşturur.
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <div class="fw-semibold">response_context</div>
            <?php if ($has_ctx): ?>
                <div class="text-success"><i class="fas fa-circle-check"></i> Hazır</div>
            <?php else: ?>
                <div class="text-warning"><i class="fas fa-triangle-exclamation"></i> Eksik</div>
            <?php endif; ?>
        </div>
        <form method="POST" class="m-0">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <button class="btn btn-grad" type="submit">
                <i class="fas fa-wrench"></i> Kurulumu Çalıştır
            </button>
        </form>
    </div>

    <hr class="border-white border-opacity-10 my-3">
    <div class="muted small">
        Alternatif: phpMyAdmin’dan şu SQL dosyasını çalıştırabilirsin: <code>modern/migrations/001_response_context.sql</code>
    </div>
</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>

