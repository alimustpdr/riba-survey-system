<?php
$page_title = 'Ayarlar';
require_once 'header.php';

// Form işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
    } else {
        $gender_field_enabled = isset($_POST['gender_field_enabled']) ? 'true' : 'false';
        
        // Sistem ayarını güncelle
        $stmt = $pdo->prepare("
            INSERT INTO settings (school_id, setting_key, setting_value) 
            VALUES (NULL, 'gender_field_enabled', ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        
        if ($stmt->execute([$gender_field_enabled, $gender_field_enabled])) {
            set_flash_message('Ayarlar başarıyla kaydedildi!', 'success');
        } else {
            set_flash_message('Ayarlar kaydedilirken hata oluştu!', 'danger');
        }
        
        header('Location: settings.php');
        exit;
    }
}

// Mevcut ayarları çek
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE school_id IS NULL AND setting_key = 'gender_field_enabled'");
$stmt->execute();
$gender_setting = $stmt->fetch();
$gender_field_enabled = ($gender_setting && $gender_setting['setting_value'] === 'true');

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-cog"></i> Sistem Ayarları</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    
                    <h6 class="mb-3">Anket Ayarları</h6>
                    
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="gender_field_enabled" 
                                   id="gender_field_enabled" <?= $gender_field_enabled ? 'checked' : '' ?>>
                            <label class="form-check-label" for="gender_field_enabled">
                                <strong>Cinsiyet Alanı Aktif</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            Bu ayar açıksa, anket doldurma formunda cinsiyet seçimi alanı gösterilir. 
                            Okul yöneticileri kendi anketlerinde bu ayarı özelleştirebilir.
                        </small>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Not:</strong> Bu ayarlar tüm sistem için varsayılan değerlerdir. 
                        Okul yöneticileri kendi okulları için bu ayarları özelleştirebilir.
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Sistem Bilgileri</h5>
            </div>
            <div class="card-body">
                <p><strong>Sistem:</strong> RİBA Anket Yönetim Sistemi</p>
                <p><strong>Versiyon:</strong> 1.0.0 MVP</p>
                <p class="mb-0"><strong>Tarih:</strong> <?= date('d.m.Y H:i') ?></p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Form Şablonları</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT kademe, role, COUNT(*) as total FROM form_templates GROUP BY kademe, role ORDER BY kademe, role");
                $form_counts = $stmt->fetchAll();
                ?>
                <small>
                    <?php foreach ($form_counts as $form): ?>
                        <div class="mb-2">
                            <span class="badge bg-secondary"><?= e($form['kademe']) ?></span>
                            <span class="badge bg-info"><?= e($form['role']) ?></span>
                            <span class="text-muted">: <?= $form['total'] ?> form</span>
                        </div>
                    <?php endforeach; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
