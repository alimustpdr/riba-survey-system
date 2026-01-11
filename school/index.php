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

if (!$school) {
    die('Okul bulunamadı!');
}

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE school_id = ?");
$stmt->execute([$school_id]);
$total_classes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM surveys WHERE school_id = ?");
$stmt->execute([$school_id]);
$total_surveys = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM surveys WHERE school_id = ? AND status = 'active'");
$stmt->execute([$school_id]);
$active_surveys = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(response_count) FROM surveys WHERE school_id = ?");
$stmt->execute([$school_id]);
$total_responses = $stmt->fetchColumn() ?: 0;

// Get recent surveys
$stmt = $pdo->prepare("
    SELECT s.*, ft.title as form_title, ft.kademe, ft.role,
           (SELECT COUNT(*) FROM responses WHERE survey_id = s.id) as actual_responses
    FROM surveys s
    LEFT JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
    LIMIT 5
");
$stmt->execute([$school_id]);
$recent_surveys = $stmt->fetchAll();

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'classes', 'url' => 'classes.php', 'icon' => 'fas fa-users-class', 'label' => 'Sınıflar'],
    ['page' => 'surveys', 'url' => 'surveys.php', 'icon' => 'fas fa-clipboard-list', 'label' => 'Anketler'],
    ['page' => 'survey_create', 'url' => 'survey_create.php', 'icon' => 'fas fa-plus-circle', 'label' => 'Yeni Anket'],
];

render_header($school['name'] . ' - Panel', 'dashboard');
render_sidebar($sidebar_items, 'dashboard');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-school"></i> <?= e($school['name']) ?></h1>
        <div class="text-muted">
            <small>Hoş geldiniz, <?= e($user['name']) ?></small>
        </div>
    </div>

    <?= show_flash('success', 'success') ?>
    <?= show_flash('error', 'error') ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Sınıf</h6>
                            <h2 class="mb-0"><?= $total_classes ?></h2>
                        </div>
                        <i class="fas fa-users-class fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Aktif Anket</h6>
                            <h2 class="mb-0"><?= $active_surveys ?></h2>
                        </div>
                        <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Anket</h6>
                            <h2 class="mb-0"><?= $total_surveys ?></h2>
                        </div>
                        <i class="fas fa-clipboard-list fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Katılım</h6>
                            <h2 class="mb-0"><?= $total_responses ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Hızlı İşlemler
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="survey_create.php" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                Yeni Anket Oluştur
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="classes.php?action=create" class="btn btn-success w-100 py-3">
                                <i class="fas fa-users-class fa-2x d-block mb-2"></i>
                                Yeni Sınıf Ekle
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="surveys.php" class="btn btn-info w-100 py-3">
                                <i class="fas fa-list fa-2x d-block mb-2"></i>
                                Anketleri Görüntüle
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="classes.php" class="btn btn-secondary w-100 py-3">
                                <i class="fas fa-users fa-2x d-block mb-2"></i>
                                Sınıfları Yönet
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Surveys -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-clipboard-list"></i> Son Oluşturulan Anketler
        </div>
        <div class="card-body">
            <?php if (empty($recent_surveys)): ?>
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
                            <?php foreach ($recent_surveys as $survey): ?>
                                <tr>
                                    <td><strong><?= e($survey['title']) ?></strong></td>
                                    <td><?= e($survey['form_title']) ?></td>
                                    <td><?= e(translate_kademe($survey['kademe'])) ?></td>
                                    <td><?= e(translate_role($survey['role'])) ?></td>
                                    <td><span class="badge bg-info"><?= $survey['actual_responses'] ?></span></td>
                                    <td>
                                        <?php if ($survey['status'] === 'active'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Kapalı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= format_date($survey['created_at']) ?></td>
                                    <td>
                                        <a href="surveys.php?action=view&id=<?= $survey['id'] ?>" class="btn btn-sm btn-primary" title="Detay">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="surveys.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-right"></i> Tüm Anketleri Görüntüle
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($school['gender_field_enabled']): ?>
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle"></i> 
            <strong>Cinsiyet alanı aktif:</strong> Anketlerinizde cinsiyet bilgisi toplanabilir.
        </div>
    <?php endif; ?>
</main>

<?php render_footer(); ?>
