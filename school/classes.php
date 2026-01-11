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
$class_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash('error', 'Güvenlik doğrulaması başarısız!');
        redirect('classes.php');
    }
    
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $kademe = $_POST['kademe'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name) || empty($kademe)) {
            set_flash('error', 'Lütfen tüm alanları doldurun!');
        } else {
            try {
                // Check if class already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE school_id = ? AND name = ?");
                $stmt->execute([$school_id, $name]);
                
                if ($stmt->fetchColumn() > 0) {
                    set_flash('error', 'Bu sınıf adı zaten kullanılıyor!');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO classes (school_id, name, kademe, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$school_id, $name, $kademe, $status]);
                    
                    set_flash('success', 'Sınıf başarıyla oluşturuldu!');
                    redirect('classes.php');
                }
            } catch (PDOException $e) {
                set_flash('error', 'Bir hata oluştu: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'edit' && $class_id) {
        $name = trim($_POST['name'] ?? '');
        $kademe = $_POST['kademe'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name) || empty($kademe)) {
            set_flash('error', 'Lütfen tüm alanları doldurun!');
        } else {
            try {
                // Check if class name already exists (excluding current class)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE school_id = ? AND name = ? AND id != ?");
                $stmt->execute([$school_id, $name, $class_id]);
                
                if ($stmt->fetchColumn() > 0) {
                    set_flash('error', 'Bu sınıf adı zaten kullanılıyor!');
                } else {
                    $stmt = $pdo->prepare("UPDATE classes SET name = ?, kademe = ?, status = ?, updated_at = NOW() WHERE id = ? AND school_id = ?");
                    $stmt->execute([$name, $kademe, $status, $class_id, $school_id]);
                    
                    set_flash('success', 'Sınıf başarıyla güncellendi!');
                    redirect('classes.php');
                }
            } catch (PDOException $e) {
                set_flash('error', 'Bir hata oluştu: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete' && $class_id) {
        try {
            // Check if class is used in any survey
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM survey_classes WHERE class_id = ?");
            $stmt->execute([$class_id]);
            
            if ($stmt->fetchColumn() > 0) {
                set_flash('error', 'Bu sınıf bir ankette kullanılıyor, silinemez!');
            } else {
                $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ? AND school_id = ?");
                $stmt->execute([$class_id, $school_id]);
                
                set_flash('success', 'Sınıf başarıyla silindi!');
            }
            redirect('classes.php');
        } catch (PDOException $e) {
            set_flash('error', 'Sınıf silinirken bir hata oluştu!');
            redirect('classes.php');
        }
    }
}

// Get class data for edit
$class = null;
if ($action === 'edit' && $class_id) {
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ? AND school_id = ?");
    $stmt->execute([$class_id, $school_id]);
    $class = $stmt->fetch();
    
    if (!$class) {
        set_flash('error', 'Sınıf bulunamadı!');
        redirect('classes.php');
    }
}

// Get all classes for list
$classes = [];
if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               (SELECT COUNT(*) FROM survey_classes WHERE class_id = c.id) as survey_count
        FROM classes c 
        WHERE c.school_id = ? 
        ORDER BY c.kademe, c.name
    ");
    $stmt->execute([$school_id]);
    $classes = $stmt->fetchAll();
}

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'classes', 'url' => 'classes.php', 'icon' => 'fas fa-users-class', 'label' => 'Sınıflar'],
    ['page' => 'surveys', 'url' => 'surveys.php', 'icon' => 'fas fa-clipboard-list', 'label' => 'Anketler'],
    ['page' => 'survey_create', 'url' => 'survey_create.php', 'icon' => 'fas fa-plus-circle', 'label' => 'Yeni Anket'],
];

render_header('Sınıf Yönetimi', 'classes');
render_sidebar($sidebar_items, 'classes');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-users-class"></i> Sınıf Yönetimi</h1>
        <?php if ($action === 'list'): ?>
            <a href="classes.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Sınıf Ekle
            </a>
        <?php endif; ?>
    </div>

    <?= show_flash('success', 'success') ?>
    <?= show_flash('error', 'error') ?>

    <?php if ($action === 'list'): ?>
        <!-- Classes List -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Tüm Sınıflar
            </div>
            <div class="card-body">
                <?php if (empty($classes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users-class fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Henüz sınıf eklenmemiş.</p>
                        <a href="classes.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> İlk Sınıfı Ekle
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sınıf Adı</th>
                                    <th>Kademe</th>
                                    <th>Anket Sayısı</th>
                                    <th>Durum</th>
                                    <th>Oluşturma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $c): ?>
                                    <tr>
                                        <td><?= $c['id'] ?></td>
                                        <td><strong><?= e($c['name']) ?></strong></td>
                                        <td><?= e(translate_kademe($c['kademe'])) ?></td>
                                        <td><span class="badge bg-info"><?= $c['survey_count'] ?></span></td>
                                        <td>
                                            <?php if ($c['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= format_date($c['created_at']) ?></td>
                                        <td>
                                            <a href="classes.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-primary" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($c['survey_count'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteClass(<?= $c['id'] ?>)" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Ankette kullanılıyor, silinemez">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

    <?php elseif ($action === 'create'): ?>
        <!-- Create Class Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus"></i> Yeni Sınıf Ekle
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sınıf Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Örn: 9/A, 10/B" required>
                            <small class="text-muted">Sınıf adını istediğiniz formatta girebilirsiniz</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kademe <span class="text-danger">*</span></label>
                            <select name="kademe" class="form-select" required>
                                <option value="">Seçiniz...</option>
                                <option value="okuloncesi">Okul Öncesi</option>
                                <option value="ilkokul">İlkokul</option>
                                <option value="ortaokul">Ortaokul</option>
                                <option value="lise">Lise</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Sınıf oluşturduktan sonra, anket oluştururken bu sınıfı hedef kitle olarak seçebilirsiniz.
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Sınıfı Kaydet
                        </button>
                        <a href="classes.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($action === 'edit' && $class): ?>
        <!-- Edit Class Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Sınıf Düzenle
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sınıf Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($class['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kademe <span class="text-danger">*</span></label>
                            <select name="kademe" class="form-select" required>
                                <option value="okuloncesi" <?= $class['kademe'] === 'okuloncesi' ? 'selected' : '' ?>>Okul Öncesi</option>
                                <option value="ilkokul" <?= $class['kademe'] === 'ilkokul' ? 'selected' : '' ?>>İlkokul</option>
                                <option value="ortaokul" <?= $class['kademe'] === 'ortaokul' ? 'selected' : '' ?>>Ortaokul</option>
                                <option value="lise" <?= $class['kademe'] === 'lise' ? 'selected' : '' ?>>Lise</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $class['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= $class['status'] === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Değişiklikleri Kaydet
                        </button>
                        <a href="classes.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Delete Confirmation Form -->
<form method="POST" id="deleteForm" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
function deleteClass(id) {
    if (confirm('Bu sınıfı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
        const form = document.getElementById('deleteForm');
        form.action = 'classes.php?action=delete&id=' + id;
        form.submit();
    }
}
</script>

<?php render_footer(); ?>
