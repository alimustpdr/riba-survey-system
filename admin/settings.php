<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';

// Require super admin role
require_role('super_admin');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash('error', 'Güvenlik doğrulaması başarısız!');
        redirect('settings.php');
    }
    
    $gender_field_enabled = isset($_POST['gender_field_enabled']) ? '1' : '0';
    $app_name = trim($_POST['app_name'] ?? 'RİBA Anket Yönetim Sistemi');
    
    set_setting('gender_field_enabled', $gender_field_enabled);
    set_setting('app_name', $app_name);
    
    set_flash('success', 'Ayarlar başarıyla kaydedildi!');
    redirect('settings.php');
}

// Get current settings
$gender_field_enabled = get_setting('gender_field_enabled', '0');
$app_name = get_setting('app_name', 'RİBA Anket Yönetim Sistemi');

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'schools', 'url' => 'schools.php', 'icon' => 'fas fa-school', 'label' => 'Okullar'],
    ['page' => 'settings', 'url' => 'settings.php', 'icon' => 'fas fa-cog', 'label' => 'Ayarlar'],
];

render_header('Sistem Ayarları', 'settings');
render_sidebar($sidebar_items, 'settings');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-cog"></i> Sistem Ayarları</h1>
    </div>

    <?= show_flash('success', 'success') ?>
    <?= show_flash('error', 'error') ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sliders-h"></i> Genel Ayarlar
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4">
                            <label class="form-label">Uygulama Adı</label>
                            <input type="text" name="app_name" class="form-control" value="<?= e($app_name) ?>">
                            <small class="text-muted">Sistemde görüntülenecek uygulama adı</small>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Form Ayarları</h5>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="gender_field_enabled" value="1" 
                                       class="form-check-input" id="genderField" 
                                       <?= $gender_field_enabled === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="genderField">
                                    <strong>Cinsiyet Alanını Aktif Et (Global)</strong>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Bu ayar kapalıysa, tüm okullarda cinsiyet alanı devre dışı olur. 
                                Açıksa, her okul kendi ayarından cinsiyet alanını açıp/kapatabilir.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Not:</strong> Cinsiyet alanı, anket doldurma sırasında opsiyonel olarak gösterilir.
                            Bu alan aktif olduğunda, katılımcılardan cinsiyet bilgisi istenir (zorunlu değil).
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Ayarları Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Sistem Bilgileri
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>PHP Sürümü:</strong></td>
                            <td><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td><strong>Veritabanı:</strong></td>
                            <td><?= DB_NAME ?></td>
                        </tr>
                        <tr>
                            <td><strong>Uygulama:</strong></td>
                            <td><?= get_setting('app_version', '1.0.0') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-exclamation-triangle"></i> Dikkat
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        Global cinsiyet alanı ayarını değiştirdiğinizde, 
                        tüm okullardaki anket formları etkilenir. 
                        Değişiklik yapmadan önce okul yöneticilerine bilgi vermeniz önerilir.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php render_footer(); ?>
