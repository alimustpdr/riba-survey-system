<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';

// Require super admin role
require_role('super_admin');

$user = get_current_user();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM schools WHERE status = 'active'");
$active_schools = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM schools");
$total_schools = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'school_admin'");
$total_admins = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM surveys");
$total_surveys = $stmt->fetchColumn();

// Get recent schools
$stmt = $pdo->prepare("
    SELECT s.*, u.name as admin_name, u.email as admin_email 
    FROM schools s 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_schools = $stmt->fetchAll();

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'schools', 'url' => 'schools.php', 'icon' => 'fas fa-school', 'label' => 'Okullar'],
    ['page' => 'settings', 'url' => 'settings.php', 'icon' => 'fas fa-cog', 'label' => 'Ayarlar'],
];

render_header('Süper Admin Panel', 'dashboard');
render_sidebar($sidebar_items, 'dashboard');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-home"></i> Panel</h1>
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
                            <h6 class="text-white-50 mb-1">Aktif Okullar</h6>
                            <h2 class="mb-0"><?= $active_schools ?></h2>
                        </div>
                        <i class="fas fa-school fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Okul</h6>
                            <h2 class="mb-0"><?= $total_schools ?></h2>
                        </div>
                        <i class="fas fa-building fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Okul Yöneticileri</h6>
                            <h2 class="mb-0"><?= $total_admins ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
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
    </div>

    <!-- Recent Schools -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-school"></i> Son Eklenen Okullar
        </div>
        <div class="card-body">
            <?php if (empty($recent_schools)): ?>
                <p class="text-muted">Henüz okul eklenmemiş.</p>
                <a href="schools.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> İlk Okulu Ekle
                </a>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Okul Adı</th>
                                <th>Slug</th>
                                <th>Yönetici</th>
                                <th>Email</th>
                                <th>Durum</th>
                                <th>Oluşturma</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_schools as $school): ?>
                                <tr>
                                    <td><strong><?= e($school['name']) ?></strong></td>
                                    <td><code><?= e($school['slug']) ?></code></td>
                                    <td><?= e($school['admin_name'] ?? '-') ?></td>
                                    <td><?= e($school['admin_email'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($school['status'] === 'active'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php elseif ($school['status'] === 'inactive'): ?>
                                            <span class="badge bg-secondary">Pasif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Süresi Dolmuş</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= format_date($school['created_at']) ?></td>
                                    <td>
                                        <a href="schools.php?action=edit&id=<?= $school['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
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
</main>

<?php render_footer(); ?>
