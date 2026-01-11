<?php
require_once __DIR__ . '/../includes/init.php';

require_school_admin();
$user = get_logged_in_user();

$page_title = 'Dashboard';
$active_nav = 'dashboard';

// School info
$stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
$stmt->execute([(int)$user['school_id']]);
$school = $stmt->fetch();
if (!$school) {
    set_flash_message('Okul bulunamadı!', 'danger');
    header('Location: /school/index.php');
    exit;
}

// KPIs
$stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE school_id = ?");
$stmt->execute([(int)$user['school_id']]);
$kpi_classes = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM surveys WHERE school_id = ?");
$stmt->execute([(int)$user['school_id']]);
$kpi_surveys = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(response_count), 0) FROM surveys WHERE school_id = ?");
$stmt->execute([(int)$user['school_id']]);
$kpi_responses = (int)$stmt->fetchColumn();

// Recent surveys
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.status, s.response_count, s.created_at, ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
    LIMIT 8
");
$stmt->execute([(int)$user['school_id']]);
$recent_surveys = $stmt->fetchAll();

require_once __DIR__ . '/../partials/layout-header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h3 class="mb-1">Merhaba, <?= e($user['name']) ?></h3>
        <div class="muted">Okul: <strong><?= e($school['name']) ?></strong></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-grad" href="/school/survey-create.php">
            <i class="fas fa-plus"></i> Yeni Anket (Klasik)
        </a>
        <a class="btn btn-outline-light" href="/school/riba-class-export.php">
            <i class="fas fa-file-excel"></i> Excel Çıktı
        </a>
    </div>
</div>

<?= modern_flash_html() ?>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="cardx p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="muted small">Sınıflar</div>
                    <div class="kpi"><?= number_format($kpi_classes, 0, ',', '.') ?></div>
                </div>
                <div class="text-white-50"><i class="fas fa-users fa-2x"></i></div>
            </div>
            <div class="mt-2">
                <a class="btn btn-sm btn-outline-light" href="/school/classes.php">Sınıfları Yönet</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="cardx p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="muted small">Anketler</div>
                    <div class="kpi"><?= number_format($kpi_surveys, 0, ',', '.') ?></div>
                </div>
                <div class="text-white-50"><i class="fas fa-clipboard-list fa-2x"></i></div>
            </div>
            <div class="mt-2">
                <a class="btn btn-sm btn-outline-light" href="/school/surveys.php">Anketlere Git</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="cardx p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="muted small">Toplam Yanıt</div>
                    <div class="kpi"><?= number_format($kpi_responses, 0, ',', '.') ?></div>
                </div>
                <div class="text-white-50"><i class="fas fa-chart-line fa-2x"></i></div>
            </div>
            <div class="mt-2">
                <a class="btn btn-sm btn-outline-light" href="/modern/school/reports/overview.php">Raporları Aç</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="cardx p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h5 class="mb-0">Son Anketler</h5>
                    <div class="muted small">Link, rapor ve hızlı erişim.</div>
                </div>
                <a class="btn btn-sm btn-outline-light" href="/school/surveys.php">
                    Tümünü Gör <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($recent_surveys)): ?>
                <div class="muted">Henüz anket yok. Klasik panelden bir anket oluşturabilirsiniz.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                        <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Kademe / Rol</th>
                            <th>Durum</th>
                            <th class="text-end">Yanıt</th>
                            <th class="text-end"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_surveys as $s): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($s['title']) ?></div>
                                    <div class="muted small"><?= e(date('d.m.Y H:i', strtotime($s['created_at']))) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= e($s['kademe']) ?></span>
                                    <span class="badge bg-info text-dark"><?= e($s['role']) ?></span>
                                </td>
                                <td>
                                    <?php if ($s['status'] === 'active'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Kapalı</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary"><?= (int)$s['response_count'] ?></span>
                                </td>
                                <td class="text-end">
                                    <?php if ($s['status'] === 'active'): ?>
                                        <a class="btn btn-sm btn-outline-light" href="/school/survey-links.php?survey_id=<?= (int)$s['id'] ?>">
                                            <i class="fas fa-link"></i> Klasik Linkler
                                        </a>
                                    <?php endif; ?>
                                    <a class="btn btn-sm btn-grad" href="/modern/school/smart-links.php?survey_id=<?= (int)$s['id'] ?>">
                                        <i class="fas fa-wand-magic-sparkles"></i> Akıllı Link
                                    </a>
                                    <a class="btn btn-sm btn-outline-light" href="/modern/school/reports/overview.php?survey_id=<?= (int)$s['id'] ?>">
                                        <i class="fas fa-chart-column"></i>
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

<?php require_once __DIR__ . '/../partials/layout-footer.php'; ?>

