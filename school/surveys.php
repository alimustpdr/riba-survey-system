<?php
$page_title = 'Anketler';
require_once 'header.php';

// Anketleri listele
$stmt = $pdo->prepare("
    SELECT s.*, ft.title as form_title, ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$user['school_id']]);
$surveys = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Anketlerim</h5>
                <a href="/school/survey-create.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Yeni Anket Oluştur
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($surveys)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Henüz anket oluşturmadınız. 
                        <a href="/school/survey-create.php" class="alert-link">İlk anketinizi oluşturun</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Form Türü</th>
                                    <th>Durum</th>
                                    <th>Yanıt Sayısı</th>
                                    <th>Oluşturulma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surveys as $survey): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($survey['title']) ?></strong>
                                            <?php if ($survey['description']): ?>
                                                <br><small class="text-muted"><?= e($survey['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= e($survey['kademe']) ?></span>
                                            <span class="badge bg-info"><?= e($survey['role']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($survey['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kapalı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= $survey['response_count'] ?> yanıt</span>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($survey['created_at'])) ?></td>
                                        <td>
                                            <?php if ($survey['status'] === 'active'): ?>
                                                <a class="btn btn-sm btn-success" href="/school/survey-links.php?survey_id=<?= (int)$survey['id'] ?>">
                                                    <i class="fas fa-link"></i> Sınıf Linkleri
                                                </a>
                                            <?php endif; ?>
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

<?php require_once 'footer.php'; ?>
