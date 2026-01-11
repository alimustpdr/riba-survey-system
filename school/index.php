<?php
$page_title = 'Dashboard';
require_once 'header.php';

// İstatistikleri çek
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM classes WHERE school_id = ?");
$stmt->execute([$user['school_id']]);
$total_classes = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM surveys WHERE school_id = ?");
$stmt->execute([$user['school_id']]);
$total_surveys = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(response_count) as total FROM surveys WHERE school_id = ?");
$stmt->execute([$user['school_id']]);
$total_responses = $stmt->fetch()['total'] ?? 0;

// Aktif anketler
$stmt = $pdo->prepare("
    SELECT s.*, ft.title as form_title, ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ? AND s.status = 'active'
    ORDER BY s.created_at DESC
    LIMIT 5
");
$stmt->execute([$user['school_id']]);
$active_surveys = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h2><?= $total_classes ?></h2>
                <p class="text-muted">Toplam Sınıf</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-3x text-success mb-3"></i>
                <h2><?= $total_surveys ?></h2>
                <p class="text-muted">Toplam Anket</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                <h2><?= $total_responses ?></h2>
                <p class="text-muted">Toplam Yanıt</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Aktif Anketler</h5>
                <a href="/school/survey-create.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Yeni Anket Oluştur
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($active_surveys)): ?>
                    <p class="text-muted">Henüz aktif anket bulunmuyor.</p>
                    <a href="/school/survey-create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> İlk Anketinizi Oluşturun
                    </a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Form</th>
                                    <th>Yanıt Sayısı</th>
                                    <th>Oluşturulma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_surveys as $survey): ?>
                                    <tr>
                                        <td><?= e($survey['title']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= e($survey['kademe']) ?></span>
                                            <span class="badge bg-info"><?= e($survey['role']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= $survey['response_count'] ?> yanıt</span>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($survey['created_at'])) ?></td>
                                        <td>
                                            <a href="/school/survey-links.php?survey_id=<?= (int)$survey['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-link"></i> Sınıf Linkleri
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Hızlı Başlangıç</h5>
            <ol class="mb-0">
                <li><a href="/school/classes.php">Sınıflarınızı ekleyin</a></li>
                <li><a href="/school/survey-create.php">Yeni bir anket oluşturun</a></li>
                <li>Anket linkini paylaşın ve yanıtları toplayın</li>
                <li><a href="/school/surveys.php">Anket sonuçlarını görüntüleyin</a></li>
            </ol>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
