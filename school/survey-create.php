<?php
$page_title = 'Yeni Anket Oluştur';
require_once 'header.php';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $form_template_id = (int)($_POST['form_template_id'] ?? 0);
        $target_type = $_POST['target_type'] ?? 'all';
        $target_classes = $_POST['target_classes'] ?? [];
        $gender_field_enabled = isset($_POST['gender_field_enabled']) ? 1 : 0;
        
        if (empty($title) || $form_template_id <= 0) {
            set_flash_message('Lütfen tüm zorunlu alanları doldurun!', 'danger');
        } else {
            try {
                $pdo->beginTransaction();
                
                // Token üret
                $link_token = bin2hex(random_bytes(32));
                
                // Anket oluştur
                $stmt = $pdo->prepare("
                    INSERT INTO surveys (school_id, form_template_id, title, description, link_token, 
                                        gender_field_enabled, status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, 'active', ?)
                ");
                $stmt->execute([
                    $user['school_id'],
                    $form_template_id,
                    $title,
                    $description,
                    $link_token,
                    $gender_field_enabled,
                    $user['id']
                ]);
                
                $survey_id = $pdo->lastInsertId();
                
                // Hedef sınıfları kaydet
                if ($target_type === 'all') {
                    $stmt = $pdo->prepare("INSERT INTO survey_target_classes (survey_id, is_all_classes) VALUES (?, TRUE)");
                    $stmt->execute([$survey_id]);
                } else {
                    foreach ($target_classes as $class_id) {
                        $stmt = $pdo->prepare("INSERT INTO survey_target_classes (survey_id, class_id, is_all_classes) VALUES (?, ?, FALSE)");
                        $stmt->execute([$survey_id, (int)$class_id]);
                    }
                }
                
                $pdo->commit();
                set_flash_message('Anket başarıyla oluşturuldu!', 'success');
                header('Location: surveys.php');
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('Anket oluşturulurken hata oluştu: ' . $e->getMessage(), 'danger');
            }
        }
    }
}

// Form şablonlarını çek
$stmt = $pdo->query("SELECT * FROM form_templates ORDER BY kademe, role");
$form_templates = $stmt->fetchAll();

// Sınıfları çek
$stmt = $pdo->prepare("SELECT * FROM classes WHERE school_id = ? AND status = 'active' ORDER BY kademe, name");
$stmt->execute([$user['school_id']]);
$classes = $stmt->fetchAll();

// Varsayılan cinsiyet ayarını çek
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE school_id IS NULL AND setting_key = 'gender_field_enabled'");
$stmt->execute();
$default_gender = $stmt->fetch();
$default_gender_enabled = ($default_gender && $default_gender['setting_value'] === 'true');

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Yeni Anket Oluştur</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    
                    <h6 class="mb-3">Temel Bilgiler</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Anket Başlığı <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required 
                               placeholder="Örn: 2024-2025 1. Dönem Değerlendirme">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama (Opsiyonel)</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Anket hakkında kısa bir açıklama..."></textarea>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Form Seçimi</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Form Şablonu <span class="text-danger">*</span></label>
                        <select name="form_template_id" class="form-select" required id="formTemplate">
                            <option value="">Form seçiniz...</option>
                            <?php 
                            $current_kademe = '';
                            foreach ($form_templates as $template): 
                                if ($current_kademe !== $template['kademe']) {
                                    if ($current_kademe !== '') echo '</optgroup>';
                                    $current_kademe = $template['kademe'];
                                    echo '<optgroup label="' . e(ucfirst($template['kademe'])) . '">';
                                }
                            ?>
                                <option value="<?= $template['id'] ?>" 
                                        data-kademe="<?= e($template['kademe']) ?>"
                                        data-role="<?= e($template['role']) ?>">
                                    <?= e($template['title']) ?> (<?= e($template['role']) ?>)
                                </option>
                            <?php 
                            endforeach; 
                            if ($current_kademe !== '') echo '</optgroup>';
                            ?>
                        </select>
                        <small class="text-muted">
                            Form seçimi sonrası sorular değiştirilemez. Her form sabit sorulara sahiptir.
                        </small>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Hedef Kitle</h6>
                    
                    <?php if (empty($classes)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Henüz sınıf eklememişsiniz. 
                            <a href="/school/classes.php" class="alert-link">Sınıf eklemek için tıklayın</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_type" 
                                   id="target_all" value="all" checked onchange="toggleClassSelection()">
                            <label class="form-check-label" for="target_all">
                                <strong>Tüm sınıflar</strong> - Link herkese açık, sınırsız katılım
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_type" 
                                   id="target_specific" value="specific" onchange="toggleClassSelection()">
                            <label class="form-check-label" for="target_specific">
                                <strong>Belirli sınıflar</strong>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="classSelection" style="display:none;">
                        <label class="form-label">Hedef Sınıflar</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($classes)): ?>
                                <?php 
                                $by_kademe = [];
                                foreach ($classes as $class) {
                                    $by_kademe[$class['kademe']][] = $class;
                                }
                                foreach ($by_kademe as $kademe => $kademe_classes): 
                                ?>
                                    <div class="mb-2">
                                        <strong><?= e(ucfirst($kademe)) ?>:</strong><br>
                                        <?php foreach ($kademe_classes as $class): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="target_classes[]" value="<?= $class['id'] ?>" 
                                                       id="class_<?= $class['id'] ?>">
                                                <label class="form-check-label" for="class_<?= $class['id'] ?>">
                                                    <?= e($class['name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">Sınıf bulunamadı</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Ayarlar</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="gender_field_enabled" 
                                   id="gender_field_enabled" <?= $default_gender_enabled ? 'checked' : '' ?>>
                            <label class="form-check-label" for="gender_field_enabled">
                                Cinsiyet alanı göster
                            </label>
                        </div>
                        <small class="text-muted">
                            Bu alan opsiyoneldir. Katılımcılar doldurma sırasında cinsiyetlerini belirtebilir.
                        </small>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Anket oluşturulduktan sonra benzersiz bir link oluşturulacaktır. 
                        Bu linki paylaşarak sınırsız sayıda katılımcının anketi doldurmasını sağlayabilirsiniz.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Anketi Oluştur
                        </button>
                        <a href="/school/surveys.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleClassSelection() {
    const specificRadio = document.getElementById('target_specific');
    const classSelection = document.getElementById('classSelection');
    classSelection.style.display = specificRadio.checked ? 'block' : 'none';
}
</script>

<?php require_once 'footer.php'; ?>
