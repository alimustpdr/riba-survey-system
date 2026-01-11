<?php
$page_title = 'Okul Yöneticileri';
require_once 'header.php';

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $school_id = (int)($_POST['school_id'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            
            if (empty($name) || empty($email) || empty($password) || $school_id <= 0) {
                set_flash_message('Lütfen tüm alanları doldurun!', 'danger');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                set_flash_message('Geçerli bir e-posta adresi giriniz!', 'danger');
            } elseif (strlen($password) < 6) {
                set_flash_message('Şifre en az 6 karakter olmalıdır!', 'danger');
            } else {
                // Email benzersizliğini kontrol et
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    set_flash_message('Bu e-posta adresi zaten kullanılıyor!', 'danger');
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, school_id, status) VALUES (?, ?, ?, 'school_admin', ?, ?)");
                    if ($stmt->execute([$name, $email, $hashed_password, $school_id, $status])) {
                        set_flash_message('Okul yöneticisi başarıyla eklendi!', 'success');
                    } else {
                        set_flash_message('Okul yöneticisi eklenirken hata oluştu!', 'danger');
                    }
                }
            }
            header('Location: school-admins.php');
            exit;
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $school_id = (int)($_POST['school_id'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $password = $_POST['password'] ?? '';
            
            if (empty($name) || empty($email) || $school_id <= 0 || $id <= 0) {
                set_flash_message('Geçersiz veri!', 'danger');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                set_flash_message('Geçerli bir e-posta adresi giriniz!', 'danger');
            } else {
                // Email benzersizliğini kontrol et (kendi ID'si hariç)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                if ($stmt->fetchColumn() > 0) {
                    set_flash_message('Bu e-posta adresi zaten kullanılıyor!', 'danger');
                } else {
                    if (!empty($password)) {
                        if (strlen($password) < 6) {
                            set_flash_message('Şifre en az 6 karakter olmalıdır!', 'danger');
                            header('Location: school-admins.php');
                            exit;
                        }
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, school_id = ?, status = ? WHERE id = ? AND role = 'school_admin'");
                        $result = $stmt->execute([$name, $email, $hashed_password, $school_id, $status, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, school_id = ?, status = ? WHERE id = ? AND role = 'school_admin'");
                        $result = $stmt->execute([$name, $email, $school_id, $status, $id]);
                    }
                    
                    if ($result) {
                        set_flash_message('Okul yöneticisi başarıyla güncellendi!', 'success');
                    } else {
                        set_flash_message('Okul yöneticisi güncellenirken hata oluştu!', 'danger');
                    }
                }
            }
            header('Location: school-admins.php');
            exit;
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'school_admin'");
                if ($stmt->execute([$id])) {
                    set_flash_message('Okul yöneticisi başarıyla silindi!', 'success');
                } else {
                    set_flash_message('Okul yöneticisi silinirken hata oluştu!', 'danger');
                }
            }
            header('Location: school-admins.php');
            exit;
        }
    }
}

// Okul yöneticilerini listele
$stmt = $pdo->query("
    SELECT u.*, s.name as school_name 
    FROM users u 
    LEFT JOIN schools s ON u.school_id = s.id 
    WHERE u.role = 'school_admin' 
    ORDER BY u.created_at DESC
");
$admins = $stmt->fetchAll();

// Okulları listele
$stmt = $pdo->query("SELECT id, name FROM schools WHERE status = 'active' ORDER BY name");
$schools = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-tie"></i> Okul Yöneticileri</h5>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fas fa-plus"></i> Yeni Yönetici Ekle
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($admins)): ?>
                    <p class="text-muted">Henüz okul yöneticisi eklenmemiş.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Okul</th>
                                    <th>Durum</th>
                                    <th>Kayıt Tarihi</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?= $admin['id'] ?></td>
                                        <td><?= e($admin['name']) ?></td>
                                        <td><?= e($admin['email']) ?></td>
                                        <td><?= e($admin['school_name'] ?? 'Atanmamış') ?></td>
                                        <td>
                                            <?php if ($admin['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= e($admin['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($admin['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick='editAdmin(<?= json_encode($admin) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?= $admin['id'] ?>, '<?= e($admin['name']) ?>')">
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
    </div>
</div>

<!-- Yeni Yönetici Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Okul Yöneticisi Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <small class="text-muted">En az 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Okul</label>
                        <select name="school_id" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?= $school['id'] ?>"><?= e($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Yönetici Düzenle Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Yönetici Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="password" id="edit_password" class="form-control" minlength="6">
                        <small class="text-muted">Boş bırakırsanız değişmez</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Okul</label>
                        <select name="school_id" id="edit_school_id" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?= $school['id'] ?>"><?= e($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                            <option value="suspended">Askıya Alınmış</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Silme formu -->
<form method="POST" id="deleteForm" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editAdmin(admin) {
    document.getElementById('edit_id').value = admin.id;
    document.getElementById('edit_name').value = admin.name;
    document.getElementById('edit_email').value = admin.email;
    document.getElementById('edit_school_id').value = admin.school_id;
    document.getElementById('edit_status').value = admin.status;
    document.getElementById('edit_password').value = '';
    new bootstrap.Modal(document.getElementById('editAdminModal')).show();
}

function deleteAdmin(id, name) {
    if (confirm('Bu yöneticiyi silmek istediğinizden emin misiniz?\n\nYönetici: ' + name + '\n\nBu işlem geri alınamaz!')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
