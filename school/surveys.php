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

$action = $_GET['action'] ?? 'list';
$survey_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash('error', 'Güvenlik doğrulaması başarısız!');
        redirect('surveys.php');
    }
    
    if ($action === 'close' && $survey_id) {
        try {
            $stmt = $pdo->prepare("UPDATE surveys SET status = 'closed', closed_at = NOW() WHERE id = ? AND school_id = ?");
            $stmt->execute([$survey_id, $school_id]);
            
            set_flash('success', 'Anket başarıyla kapatıldı!');
            redirect('surveys.php');
        } catch (PDOException $e) {
            set_flash('error', 'Anket kapatılırken bir hata oluştu!');
            redirect('surveys.php');
        }
    } elseif ($action === 'delete' && $survey_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM surveys WHERE id = ? AND school_id = ?");
            $stmt->execute([$survey_id, $school_id]);
            
            set_flash('success', 'Anket başarıyla silindi!');
            redirect('surveys.php');
        } catch (PDOException $e) {
            set_flash('error', 'Anket silinirken bir hata oluştu!');
            redirect('surveys.php');
        }
    }
}

// Get survey data for view
$survey = null;
$survey_classes = [];
$survey_url = '';
if ($action === 'view' && $survey_id) {
    $stmt = $pdo->prepare("
        SELECT s.*, ft.title as form_title, ft.kademe, ft.role, ft.question_count,
               u.name as created_by_name
        FROM surveys s
        LEFT JOIN form_templates ft ON s.form_template_id = ft.id
        LEFT JOIN users u ON s.created_by = u.id
        WHERE s.id = ? AND s.school_id = ?
    ");
    $stmt->execute([$survey_id, $school_id]);
    $survey = $stmt->fetch();
    
    if (!$survey) {
        set_flash('error', 'Anket bulunamadı!');
        redirect('surveys.php');
    }
    
    // Get survey classes
    $stmt = $pdo->prepare("
        SELECT c.* 
        FROM survey_classes sc
        JOIN classes c ON sc.class_id = c.id
        WHERE sc.survey_id = ?
    ");
    $stmt->execute([$survey_id]);
    $survey_classes = $stmt->fetchAll();
    
    // Generate survey URL
    $survey_url = url('survey/fill.php?token=' . $survey['link_token']);
}

// Get all surveys for list
$surveys = [];
if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT s.*, ft.title as form_title, ft.kademe, ft.role,
               (SELECT COUNT(*) FROM responses WHERE survey_id = s.id) as actual_responses
        FROM surveys s
        LEFT JOIN form_templates ft ON s.form_template_id = ft.id
        WHERE s.school_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$school_id]);
    $surveys = $stmt->fetchAll();
}

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'classes', 'url' => 'classes.php', 'icon' => 'fas fa-users-class', 'label' => 'Sınıflar'],
    ['page' => 'surveys', 'url' => 'surveys.php', 'icon' => 'fas fa-clipboard-list', 'label' => 'Anketler'],
    ['page' => 'survey_create', 'url' => 'survey_create.php', 'icon' => 'fas fa-plus-circle', 'label' => 'Yeni Anket'],
];

render_header('Anket Yönetimi', 'surveys');
render_sidebar($sidebar_items, 'surveys');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-clipboard-list"></i> Anket Yönetimi</h1>
        <?php if ($action === 'list'): ?>
            <a href="survey_create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Anket Oluştur
            </a>
        <?php endif; ?>
    </div>

    <?= show_flash('success', 'success') ?>
    <?= show_flash('error', 'error') ?>

    <?php if ($action === 'list'): ?>
        <!-- Surveys List -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Tüm Anketler
            </div>
            <div class="card-body">
                <?php if (empty($surveys)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Henüz anket oluşturulmamış.</p>
                        <a href="survey_create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> İlk Anketi Oluştur
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Anket Başlığı</th>
                                    <th>Form</th>
                                    <th>Kademe</th>
                                    <th>Hedef Kitle</th>
                                    <th>Katılım</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surveys as $s): ?>
                                    <tr>
                                        <td><?= $s['id'] ?></td>
                                        <td><strong><?= e($s['title']) ?></strong></td>
                                        <td><?= e($s['form_title']) ?></td>
                                        <td><?= e(translate_kademe($s['kademe'])) ?></td>
                                        <td><?= e(translate_role($s['role'])) ?></td>
                                        <td><span class="badge bg-info"><?= $s['actual_responses'] ?></span></td>
                                        <td>
                                            <?php if ($s['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kapalı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= format_date($s['created_at']) ?></td>
                                        <td>
                                            <a href="surveys.php?action=view&id=<?= $s['id'] ?>" class="btn btn-sm btn-primary" title="Detay">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($s['status'] === 'active'): ?>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="closeSurvey(<?= $s['id'] ?>)" title="Kapat">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSurvey(<?= $s['id'] ?>)" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($action === 'view' && $survey): ?>
        <!-- Survey Details -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> Anket Detayları
                    </div>
                    <div class="card-body">
                        <h4><?= e($survey['title']) ?></h4>
                        <?php if ($survey['description']): ?>
                            <p class="text-muted"><?= e($survey['description']) ?></p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Form:</strong><br>
                                <?= e($survey['form_title']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Kademe:</strong><br>
                                <?= e(translate_kademe($survey['kademe'])) ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Hedef Kitle:</strong><br>
                                <?= e(translate_role($survey['role'])) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Soru Sayısı:</strong><br>
                                <?= $survey['question_count'] ?> madde
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Oluşturan:</strong><br>
                                <?= e($survey['created_by_name']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Oluşturma Tarihi:</strong><br>
                                <?= format_date($survey['created_at']) ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Hedef Sınıflar:</strong><br>
                                <?php if ($survey['target_all_classes']): ?>
                                    <span class="badge bg-info">Tüm Sınıflar</span>
                                <?php elseif (!empty($survey_classes)): ?>
                                    <?php foreach ($survey_classes as $class): ?>
                                        <span class="badge bg-secondary"><?= e($class['name']) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">Belirtilmemiş</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Durum:</strong><br>
                                <?php if ($survey['status'] === 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Kapalı</span>
                                    <?php if ($survey['closed_at']): ?>
                                        <small class="text-muted">(<?= format_date($survey['closed_at']) ?>)</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-link"></i> Anket Linki
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small">Token:</label>
                            <input type="text" class="form-control form-control-sm" value="<?= e($survey['link_token']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Anket URL:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="surveyUrl" value="<?= e($survey_url) ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyUrl()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <a href="<?= e($survey_url) ?>" target="_blank" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-external-link-alt"></i> Anketi Aç
                        </a>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> İstatistikler
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h2 class="mb-0"><?= $survey['response_count'] ?></h2>
                            <p class="text-muted mb-0">Toplam Katılım</p>
                        </div>
                    </div>
                </div>
                
                <?php if ($survey['status'] === 'active'): ?>
                    <div class="card mt-3">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-cog"></i> İşlemler
                        </div>
                        <div class="card-body">
                            <button class="btn btn-warning w-100 mb-2" onclick="closeSurvey(<?= $survey['id'] ?>)">
                                <i class="fas fa-lock"></i> Anketi Kapat
                            </button>
                            <button class="btn btn-danger w-100" onclick="deleteSurvey(<?= $survey['id'] ?>)">
                                <i class="fas fa-trash"></i> Anketi Sil
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="surveys.php" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Geri Dön
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Action Forms -->
<form method="POST" id="closeForm" style="display: none;">
    <?= csrf_field() ?>
</form>

<form method="POST" id="deleteForm" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
function copyUrl() {
    const urlInput = document.getElementById('surveyUrl');
    urlInput.select();
    document.execCommand('copy');
    alert('Anket linki kopyalandı!');
}

function closeSurvey(id) {
    if (confirm('Bu anketi kapatmak istediğinizden emin misiniz? Kapalı anketler yeniden açılamaz!')) {
        const form = document.getElementById('closeForm');
        form.action = 'surveys.php?action=close&id=' + id;
        form.submit();
    }
}

function deleteSurvey(id) {
    if (confirm('Bu anketi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve tüm yanıtlar silinecektir!')) {
        const form = document.getElementById('deleteForm');
        form.action = 'surveys.php?action=delete&id=' + id;
        form.submit();
    }
}
</script>

<?php render_footer(); ?>
