<?php
require_once __DIR__ . '/../includes/init.php';

require_super_admin();
$user = get_logged_in_user();

$page_title = 'Admin Dashboard';
$active_nav = 'dashboard';

$stmt = $pdo->query("SELECT COUNT(*) FROM schools");
$kpi_schools = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'school_admin'");
$kpi_school_admins = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM surveys");
$kpi_surveys = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM responses");
$kpi_responses = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT id, name, slug, status, created_at FROM schools ORDER BY created_at DESC LIMIT 8");
$recent_schools = $stmt->fetchAll();

require_once __DIR__ . '/../partials/layout-header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h3 class="mb-1">Süper Admin</h3>
        <div class="muted">Modern görünüm (additive)</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-light" href="/admin/schools.php">
            <i class="fas fa-school"></i> Okullar (Klasik)
        </a>
    </div>
</div>

<?= modern_flash_html() ?>

<div class="row g-3">
    <div class="col-12 col-md-3">
        <div class="cardx p-3 h-100">
            <div class="muted small">Okullar</div>
            <div class="kpi"><?= number_format($kpi_schools, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="cardx p-3 h-100">
            <div class="muted small">Okul Yöneticileri</div>
            <div class="kpi"><?= number_format($kpi_school_admins, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="cardx p-3 h-100">
            <div class="muted small">Anketler</div>
            <div class="kpi"><?= number_format($kpi_surveys, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="cardx p-3 h-100">
            <div class="muted small">Yanıtlar</div>
            <div class="kpi"><?= number_format($kpi_responses, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="cardx p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0">Son Eklenen Okullar</h5>
                    <div class="muted small">Hızlı görünüm.</div>
                </div>
                <a class="btn btn-sm btn-outline-light" href="/admin/schools.php">
                    Yönet <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($recent_schools)): ?>
                <div class="muted">Henüz okul yok.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                        <thead>
                        <tr>
                            <th>Okul</th>
                            <th>Slug</th>
                            <th>Durum</th>
                            <th class="text-end">Oluşturulma</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_schools as $s): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($s['name']) ?></td>
                                <td><code><?= e($s['slug']) ?></code></td>
                                <td>
                                    <?php if ($s['status'] === 'active'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php elseif ($s['status'] === 'inactive'): ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Süresi Dolmuş</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end muted small"><?= e(date('d.m.Y H:i', strtotime($s['created_at']))) ?></td>
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

