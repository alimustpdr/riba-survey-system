<?php
$page_title = 'Dashboard';
require_once 'header.php';

// İstatistikleri çek
$stmt = $pdo->query("SELECT COUNT(*) as total FROM schools");
$total_schools = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'school_admin'");
$total_admins = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM surveys");
$total_surveys = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM responses");
$total_responses = $stmt->fetch()['total'];

// Son okullar
$stmt = $pdo->query("SELECT * FROM schools ORDER BY created_at DESC LIMIT 5");
$recent_schools = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-school fa-3x text-primary mb-3"></i>
                <h2><?= $total_schools ?></h2>
                <p class="text-muted">Toplam Okul</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                <h2><?= $total_admins ?></h2>
                <p class="text-muted">Okul Yöneticisi</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-3x text-info mb-3"></i>
                <h2><?= $total_surveys ?></h2>
                <p class="text-muted">Toplam Anket</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                <h2><?= $total_responses ?></h2>
                <p class="text-muted">Toplam Yanıt</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-school"></i> Son Eklenen Okullar</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_schools)): ?>
                    <p class="text-muted">Henüz okul eklenmemiş.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Okul Adı</th>
                                    <th>Slug</th>
                                    <th>Durum</th>
                                    <th>Eklenme Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_schools as $school): ?>
                                    <tr>
                                        <td><?= e($school['name']) ?></td>
                                        <td><code><?= e($school['slug']) ?></code></td>
                                        <td>
                                            <?php if ($school['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= e($school['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($school['created_at'])) ?></td>
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
