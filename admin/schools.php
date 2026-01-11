<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';

// Require super admin role
require_role('super_admin');

$action = $_GET['action'] ?? 'list';
$school_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash('error', 'Güvenlik doğrulaması başarısız!');
        redirect('schools.php');
    }
    
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $admin_name = trim($_POST['admin_name'] ?? '');
        $admin_email = trim($_POST['admin_email'] ?? '');
        $admin_password = $_POST['admin_password'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $gender_field = isset($_POST['gender_field']) ? 1 : 0;
        
        if (empty($name) || empty($slug) || empty($admin_name) || empty($admin_email) || empty($admin_password)) {
            set_flash('error', 'Lütfen tüm zorunlu alanları doldurun!');
        } elseif (!is_valid_email($admin_email)) {
            set_flash('error', 'Geçersiz email adresi!');
        } elseif (!is_unique_slug($slug)) {
            set_flash('error', 'Bu slug zaten kullanılıyor!');
        } else {
            try {
                $pdo->beginTransaction();
                
                // Create school admin user
                $hashed_pass = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'school_admin', 'active', NOW())");
                $stmt->execute([$admin_name, $admin_email, $hashed_pass]);
                $user_id = $pdo->lastInsertId();
                
                // Create school
                $stmt = $pdo->prepare("INSERT INTO schools (name, slug, user_id, gender_field_enabled, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $user_id, $gender_field, $status]);
                $school_id = $pdo->lastInsertId();
                
                // Update user's school_id
                $stmt = $pdo->prepare("UPDATE users SET school_id = ? WHERE id = ?");
                $stmt->execute([$school_id, $user_id]);
                
                $pdo->commit();
                
                set_flash('success', 'Okul ve yönetici başarıyla oluşturuldu!');
                redirect('schools.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                set_flash('error', 'Bir hata oluştu: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'edit' && $school_id) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $gender_field = isset($_POST['gender_field']) ? 1 : 0;
        
        if (empty($name) || empty($slug)) {
            set_flash('error', 'Lütfen tüm zorunlu alanları doldurun!');
        } elseif (!is_unique_slug($slug, $school_id)) {
            set_flash('error', 'Bu slug zaten kullanılıyor!');
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE schools SET name = ?, slug = ?, gender_field_enabled = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $slug, $gender_field, $status, $school_id]);
                
                set_flash('success', 'Okul başarıyla güncellendi!');
                redirect('schools.php');
            } catch (PDOException $e) {
                set_flash('error', 'Bir hata oluştu: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete' && $school_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM schools WHERE id = ?");
            $stmt->execute([$school_id]);
            
            set_flash('success', 'Okul başarıyla silindi!');
            redirect('schools.php');
        } catch (PDOException $e) {
            set_flash('error', 'Okul silinirken bir hata oluştu!');
        }
    }
}

// Get school data for edit
$school = null;
if ($action === 'edit' && $school_id) {
    $stmt = $pdo->prepare("SELECT s.*, u.name as admin_name, u.email as admin_email FROM schools s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
    $stmt->execute([$school_id]);
    $school = $stmt->fetch();
    
    if (!$school) {
        set_flash('error', 'Okul bulunamadı!');
        redirect('schools.php');
    }
}

// Get all schools for list
$schools = [];
if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT s.*, u.name as admin_name, u.email as admin_email,
               (SELECT COUNT(*) FROM surveys WHERE school_id = s.id) as survey_count
        FROM schools s 
        LEFT JOIN users u ON s.user_id = u.id 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $schools = $stmt->fetchAll();
}

// Sidebar items
$sidebar_items = [
    ['page' => 'dashboard', 'url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Panel'],
    ['page' => 'schools', 'url' => 'schools.php', 'icon' => 'fas fa-school', 'label' => 'Okullar'],
    ['page' => 'settings', 'url' => 'settings.php', 'icon' => 'fas fa-cog', 'label' => 'Ayarlar'],
];

render_header('Okul Yönetimi', 'schools');
render_sidebar($sidebar_items, 'schools');
?>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-school"></i> Okul Yönetimi</h1>
        <?php if ($action === 'list'): ?>
            <a href="schools.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Okul Ekle
            </a>
        <?php endif; ?>
    </div>

    <?= show_flash('success', 'success') ?>
    <?= show_flash('error', 'error') ?>

    <?php if ($action === 'list'): ?>
        <!-- Schools List -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Tüm Okullar
            </div>
            <div class="card-body">
                <?php if (empty($schools)): ?>
                    <p class="text-muted">Henüz okul eklenmemiş.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Okul Adı</th>
                                    <th>Slug</th>
                                    <th>Yönetici</th>
                                    <th>Email</th>
                                    <th>Anket Sayısı</th>
                                    <th>Cinsiyet Alanı</th>
                                    <th>Durum</th>
                                    <th>Oluşturma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schools as $s): ?>
                                    <tr>
                                        <td><?= $s['id'] ?></td>
                                        <td><strong><?= e($s['name']) ?></strong></td>
                                        <td><code><?= e($s['slug']) ?></code></td>
                                        <td><?= e($s['admin_name'] ?? '-') ?></td>
                                        <td><?= e($s['admin_email'] ?? '-') ?></td>
                                        <td><span class="badge bg-info"><?= $s['survey_count'] ?></span></td>
                                        <td>
                                            <?php if ($s['gender_field_enabled']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($s['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php elseif ($s['status'] === 'inactive'): ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Süresi Dolmuş</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= format_date($s['created_at']) ?></td>
                                        <td>
                                            <a href="schools.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-primary" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSchool(<?= $s['id'] ?>)" title="Sil">
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

    <?php elseif ($action === 'create'): ?>
        <!-- Create School Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus"></i> Yeni Okul Ekle
            </div>
            <div class="card-body">
                <form method="POST" id="schoolForm">
                    <?= csrf_field() ?>
                    
                    <h5 class="mb-3">Okul Bilgileri</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Okul Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required id="schoolName">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" required id="schoolSlug">
                            <small class="text-muted">URL dostu isim (otomatik oluşturulur)</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durum</label>
                            <select name="status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Pasif</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="gender_field" value="1" class="form-check-input" id="genderField">
                                <label class="form-check-label" for="genderField">
                                    Cinsiyet alanını aktif et
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">Okul Yöneticisi Bilgileri</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="admin_name" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Şifre <span class="text-danger">*</span></label>
                            <input type="password" name="admin_password" class="form-control" required minlength="6">
                            <small class="text-muted">En az 6 karakter</small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Okulu Kaydet
                        </button>
                        <a href="schools.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($action === 'edit' && $school): ?>
        <!-- Edit School Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Okul Düzenle
            </div>
            <div class="card-body">
                <form method="POST" id="schoolForm">
                    <?= csrf_field() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Okul Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required id="schoolName" value="<?= e($school['name']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" required id="schoolSlug" value="<?= e($school['slug']) ?>">
                            <small class="text-muted">URL dostu isim</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durum</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $school['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                                <option value="inactive" <?= $school['status'] === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                                <option value="expired" <?= $school['status'] === 'expired' ? 'selected' : '' ?>>Süresi Dolmuş</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="gender_field" value="1" class="form-check-input" id="genderField" 
                                       <?= $school['gender_field_enabled'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="genderField">
                                    Cinsiyet alanını aktif et
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Yönetici:</strong> <?= e($school['admin_name']) ?> (<?= e($school['admin_email']) ?>)
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Değişiklikleri Kaydet
                        </button>
                        <a href="schools.php" class="btn btn-secondary">
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
// Auto-generate slug from school name
<?php if ($action === 'create' || $action === 'edit'): ?>
document.getElementById('schoolName').addEventListener('input', function(e) {
    const slug = e.target.value
        .toLowerCase()
        .replace(/ş/g, 's').replace(/ı/g, 'i').replace(/ğ/g, 'g')
        .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('schoolSlug').value = slug;
});
<?php endif; ?>

function deleteSchool(id) {
    if (confirm('Bu okulu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve okulun tüm verileri silinecektir!')) {
        const form = document.getElementById('deleteForm');
        form.action = 'schools.php?action=delete&id=' + id;
        form.submit();
    }
}
</script>

<?php render_footer(); ?>
