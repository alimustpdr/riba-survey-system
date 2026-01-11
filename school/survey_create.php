<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';

// Require school admin role
require_role('school_admin');

$user = get_current_user();
$school_id = $user['school_id'];

// Get school info
$stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$school = $stmt->fetch();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Güvenlik doğrulaması başarısız!';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $form_template_id = $_POST['form_template_id'] ?? '';
        $target_all_classes = isset($_POST['target_all_classes']) ? 1 : 0;
        $selected_classes = $_POST['classes'] ?? [];
        
        if (empty($title) || empty($form_template_id)) {
            $error = 'Lütfen anket başlığı ve form seçiniz!';
        } elseif (!$target_all_classes && empty($selected_classes)) {
            $error = 'Lütfen en az bir sınıf seçiniz veya "Tüm Sınıflar" seçeneğini işaretleyiniz!';
        } else {
            try {
                // Get form template info
                $stmt = $pdo->prepare("SELECT kademe, role FROM form_templates WHERE id = ?");
                $stmt->execute([$form_template_id]);
                $form_template = $stmt->fetch();
                
                if (!$form_template) {
                    $error = 'Geçersiz form seçimi!';
                } else {
                    $pdo->beginTransaction();
                    
                    // Generate unique token
                    do {
                        $token = bin2hex(random_bytes(16));
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM surveys WHERE link_token = ?");
                        $stmt->execute([$token]);
                    } while ($stmt->fetchColumn() > 0);
                    
                    // Create survey
                    $stmt = $pdo->prepare("
                        INSERT INTO surveys (
                            school_id, form_template_id, title, description, 
                            link_token, target_all_classes, status, created_by, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, NOW())
                    ");
                    $stmt->execute([
                        $school_id, $form_template_id, $title, $description,
                        $token, $target_all_classes, $user['id']
                    ]);
                    
                    $survey_id = $pdo->lastInsertId();
                    
                    // Add selected classes
                    if (!$target_all_classes && !empty($selected_classes)) {
                        $stmt = $pdo->prepare("INSERT INTO survey_classes (survey_id, class_id) VALUES (?, ?)");
                        foreach ($selected_classes as $class_id) {
                            $stmt->execute([$survey_id, $class_id]);
                        }
                    }
                    
                    $pdo->commit();
                    
                    set_flash('success', 'Anket başarıyla oluşturuldu!');
                    redirect('surveys.php?action=view&id=' . $survey_id);
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Bir hata oluştu: ' . $e->getMessage();
            }
        }
    }
}

// Get all form templates
$form_templates = get_form_templates();

// Get all classes for this school
$stmt = $pdo->prepare("SELECT * FROM classes WHERE school_id = ? AND status = 'active' ORDER BY kademe, name");
$stmt->execute([$school_id]);
$classes = $stmt->fetchAll();

// Group classes by kademe
$classes_by_kademe = [];
foreach ($classes as $class) {
    $classes_by_kademe[$class['kademe']][] = $class;
}

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'classes', 'url' => 'classes.php', 'icon' => 'fas fa-users-class', 'label' => 'Sınıflar'],
    ['page' => 'surveys', 'url' => 'surveys.php', 'icon' => 'fas fa-clipboard-list', 'label' => 'Anketler'],
    ['page' => 'survey_create', 'url' => 'survey_create.php', 'icon' => 'fas fa-plus-circle', 'label' => 'Yeni Anket'],
];

render_header('Yeni Anket Oluştur', 'survey_create');
render_sidebar($sidebar_items, 'survey_create');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-plus-circle"></i> Yeni Anket Oluştur</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= e($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($classes)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Dikkat:</strong> Henüz sınıf eklenmemiş! Anket oluşturmadan önce 
            <a href="classes.php?action=create">buradan</a> sınıf ekleyebilirsiniz.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Anket Bilgileri
                </div>
                <div class="card-body">
                    <form method="POST" id="surveyForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4">
                            <label class="form-label">Anket Başlığı <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" 
                                   placeholder="Örn: 2024 Lise Öğrenci Anketi" required
                                   value="<?= e($_POST['title'] ?? '') ?>">
                            <small class="text-muted">Katılımcıların göreceği başlık</small>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Açıklama (Opsiyonel)</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Anket hakkında açıklama..."><?= e($_POST['description'] ?? '') ?></textarea>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Form Seçimi</h5>
                        
                        <div class="mb-4">
                            <label class="form-label">RİBA Formu <span class="text-danger">*</span></label>
                            <select name="form_template_id" id="formTemplate" class="form-select" required>
                                <option value="">Lütfen form seçiniz...</option>
                                <?php
                                $current_kademe = '';
                                foreach ($form_templates as $template):
                                    if ($current_kademe !== $template['kademe']):
                                        if ($current_kademe !== '') echo '</optgroup>';
                                        $current_kademe = $template['kademe'];
                                        echo '<optgroup label="' . e(translate_kademe($current_kademe)) . '">';
                                    endif;
                                ?>
                                    <option value="<?= $template['id'] ?>" 
                                            data-kademe="<?= e($template['kademe']) ?>"
                                            data-role="<?= e($template['role']) ?>"
                                            data-questions="<?= $template['question_count'] ?>"
                                            <?= (isset($_POST['form_template_id']) && $_POST['form_template_id'] == $template['id']) ? 'selected' : '' ?>>
                                        <?= e(translate_role($template['role'])) ?> (<?= $template['question_count'] ?> madde)
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($current_kademe !== '') echo '</optgroup>'; ?>
                            </select>
                            <small class="text-muted">Anket soruları seçtiğiniz form şablonundan gelecektir</small>
                        </div>
                        
                        <div id="formInfo" class="alert alert-info" style="display: none;">
                            <strong>Seçilen Form:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kademe: <span id="infoKademe"></span></li>
                                <li>Hedef Kitle: <span id="infoRole"></span></li>
                                <li>Soru Sayısı: <span id="infoQuestions"></span> madde</li>
                            </ul>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Hedef Sınıflar</h5>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="target_all_classes" value="1" 
                                       class="form-check-input" id="targetAll"
                                       <?= (isset($_POST['target_all_classes']) && $_POST['target_all_classes']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="targetAll">
                                    <strong>Tüm Sınıflar</strong>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Bu seçenek işaretlenirse, link herkese açık olur ve sınıf bazlı filtreleme yapılmaz
                            </small>
                        </div>
                        
                        <div id="classSelection">
                            <?php if (!empty($classes_by_kademe)): ?>
                                <?php foreach ($classes_by_kademe as $kademe => $kademe_classes): ?>
                                    <div class="mb-3">
                                        <strong><?= e(translate_kademe($kademe)) ?>:</strong>
                                        <div class="ms-3 mt-2">
                                            <?php foreach ($kademe_classes as $class): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" name="classes[]" value="<?= $class['id'] ?>" 
                                                           class="form-check-input class-checkbox" id="class_<?= $class['id'] ?>"
                                                           data-kademe="<?= e($class['kademe']) ?>"
                                                           <?= (isset($_POST['classes']) && in_array($class['id'], $_POST['classes'])) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="class_<?= $class['id'] ?>">
                                                        <?= e($class['name']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Henüz sınıf eklenmemiş. 
                                    <a href="classes.php?action=create" target="_blank">Buradan</a> sınıf ekleyebilirsiniz.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Bilgi:</strong> Anket oluşturulduktan sonra size özel bir link verilecektir. 
                            Bu linki istediğiniz kişilerle paylaşabilirsiniz. Link sınırsız sayıda kullanılabilir.
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Anketi Oluştur
                            </button>
                            <a href="surveys.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lightbulb"></i> Anket Oluşturma İpuçları
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li class="mb-2">
                            <strong>Anket Başlığı:</strong> Kısa ve açıklayıcı bir başlık seçin.
                        </li>
                        <li class="mb-2">
                            <strong>Form Seçimi:</strong> Kademe ve hedef kitleye uygun formu seçin.
                        </li>
                        <li class="mb-2">
                            <strong>Sınıf Seçimi:</strong> Belirli sınıflar için anket yapmak istiyorsanız ilgili sınıfları seçin. 
                            Herkese açık yapmak için "Tüm Sınıflar" seçeneğini işaretleyin.
                        </li>
                        <li class="mb-2">
                            <strong>Link Paylaşımı:</strong> Anket oluşturduktan sonra size verilen linki istediğiniz kanallardan paylaşabilirsiniz.
                        </li>
                    </ol>
                </div>
            </div>
            
            <?php if ($school['gender_field_enabled']): ?>
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-venus-mars"></i> Cinsiyet Alanı
                    </div>
                    <div class="card-body">
                        <p class="small mb-0">
                            Okulunuzda cinsiyet alanı aktif. 
                            Anket doldurulurken katılımcılardan cinsiyet bilgisi istenecektir (opsiyonel).
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Form template selection handler
document.getElementById('formTemplate').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const formInfo = document.getElementById('formInfo');
    
    if (selected.value) {
        const kademe = selected.dataset.kademe;
        const role = selected.dataset.role;
        const questions = selected.dataset.questions;
        
        document.getElementById('infoKademe').textContent = translateKademe(kademe);
        document.getElementById('infoRole').textContent = translateRole(role);
        document.getElementById('infoQuestions').textContent = questions;
        
        formInfo.style.display = 'block';
        
        // Auto-select matching classes
        autoSelectClasses(kademe);
    } else {
        formInfo.style.display = 'none';
    }
});

// Target all classes handler
document.getElementById('targetAll').addEventListener('change', function() {
    const classSelection = document.getElementById('classSelection');
    const classCheckboxes = document.querySelectorAll('.class-checkbox');
    
    if (this.checked) {
        classSelection.style.opacity = '0.5';
        classSelection.style.pointerEvents = 'none';
        classCheckboxes.forEach(cb => cb.disabled = true);
    } else {
        classSelection.style.opacity = '1';
        classSelection.style.pointerEvents = 'auto';
        classCheckboxes.forEach(cb => cb.disabled = false);
    }
});

// Auto-select classes based on form kademe
function autoSelectClasses(kademe) {
    const classCheckboxes = document.querySelectorAll('.class-checkbox');
    classCheckboxes.forEach(cb => {
        if (cb.dataset.kademe === kademe) {
            cb.checked = true;
        } else {
            cb.checked = false;
        }
    });
}

// Translation functions
function translateKademe(kademe) {
    const translations = {
        'okuloncesi': 'Okul Öncesi',
        'ilkokul': 'İlkokul',
        'ortaokul': 'Ortaokul',
        'lise': 'Lise'
    };
    return translations[kademe] || kademe;
}

function translateRole(role) {
    const translations = {
        'ogrenci': 'Öğrenci',
        'veli': 'Veli',
        'ogretmen': 'Öğretmen'
    };
    return translations[role] || role;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Trigger change event if form is already selected (after form error)
    const formTemplate = document.getElementById('formTemplate');
    if (formTemplate.value) {
        formTemplate.dispatchEvent(new Event('change'));
    }
    
    // Check targetAll state
    const targetAll = document.getElementById('targetAll');
    if (targetAll.checked) {
        targetAll.dispatchEvent(new Event('change'));
    }
});
</script>

<?php render_footer(); ?>
