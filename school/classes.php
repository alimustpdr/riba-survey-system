<?php
$page_title = 'Sınıf Yönetimi';
require_once 'header.php';
require_once __DIR__ . '/../includes/settings.php';

$enabled_kademes = get_enabled_kademes_for_school((int)$user['school_id']);
$kademe_labels = [
    'okuloncesi' => 'Okul Öncesi',
    'ilkokul' => 'İlkokul',
    'ortaokul' => 'Ortaokul',
    'lise' => 'Lise',
];

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $kademe = $_POST['kademe'] ?? '';
            $student_count = (int)($_POST['student_count'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            
            if (empty($name) || empty($kademe)) {
                set_flash_message('Lütfen tüm alanları doldurun!', 'danger');
            } elseif (!in_array($kademe, $enabled_kademes, true)) {
                set_flash_message('Bu kademe paketinize dahil değil!', 'danger');
            } else {
                $stmt = $pdo->prepare("INSERT INTO classes (school_id, name, kademe, student_count, status) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$user['school_id'], $name, $kademe, $student_count, $status])) {
                    set_flash_message('Sınıf başarıyla eklendi!', 'success');
                } else {
                    set_flash_message('Sınıf eklenirken hata oluştu!', 'danger');
                }
            }
            header('Location: classes.php');
            exit;
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $kademe = $_POST['kademe'] ?? '';
            $student_count = (int)($_POST['student_count'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            
            if (empty($name) || empty($kademe) || $id <= 0) {
                set_flash_message('Geçersiz veri!', 'danger');
            } elseif (!in_array($kademe, $enabled_kademes, true)) {
                set_flash_message('Bu kademe paketinize dahil değil!', 'danger');
            } else {
                $stmt = $pdo->prepare("UPDATE classes SET name = ?, kademe = ?, student_count = ?, status = ? WHERE id = ? AND school_id = ?");
                if ($stmt->execute([$name, $kademe, $student_count, $status, $id, $user['school_id']])) {
                    set_flash_message('Sınıf başarıyla güncellendi!', 'success');
                } else {
                    set_flash_message('Sınıf güncellenirken hata oluştu!', 'danger');
                }
            }
            header('Location: classes.php');
            exit;
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ? AND school_id = ?");
                if ($stmt->execute([$id, $user['school_id']])) {
                    set_flash_message('Sınıf başarıyla silindi!', 'success');
                } else {
                    set_flash_message('Sınıf silinirken hata oluştu!', 'danger');
                }
            }
            header('Location: classes.php');
            exit;
        }
    }
}

// Sınıfları listele
$stmt = $pdo->prepare("SELECT * FROM classes WHERE school_id = ? ORDER BY kademe, name");
$stmt->execute([$user['school_id']]);
$classes = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users"></i> Sınıflar</h5>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <i class="fas fa-plus"></i> Yeni Sınıf Ekle
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($classes)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Henüz sınıf eklenmemiş. Anket oluşturmadan önce sınıflarınızı ekleyin.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sınıf Adı</th>
                                    <th>Kademe</th>
                                    <th>Öğrenci Sayısı</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><?= e($class['name']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= e($class['kademe']) ?></span>
                                        </td>
                                        <td><?= $class['student_count'] ?> öğrenci</td>
                                        <td>
                                            <?php if ($class['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick='editClass(<?= json_encode($class) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteClass(<?= $class['id'] ?>, '<?= e($class['name']) ?>')">
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

<!-- Yeni Sınıf Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Sınıf Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sınıf Adı</label>
                        <input type="text" name="name" class="form-control" placeholder="Örn: 5-A, 10-B" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kademe</label>
                        <select name="kademe" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($enabled_kademes as $k): ?>
                                <option value="<?= e($k) ?>"><?= e($kademe_labels[$k] ?? $k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Öğrenci Sayısı (Tahmini)</label>
                        <input type="number" name="student_count" class="form-control" value="0" min="0">
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

<!-- Sınıf Düzenle Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Sınıf Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sınıf Adı</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kademe</label>
                        <select name="kademe" id="edit_kademe" class="form-select" required>
                            <?php foreach ($enabled_kademes as $k): ?>
                                <option value="<?= e($k) ?>"><?= e($kademe_labels[$k] ?? $k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Öğrenci Sayısı</label>
                        <input type="number" name="student_count" id="edit_student_count" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
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
function editClass(classData) {
    document.getElementById('edit_id').value = classData.id;
    document.getElementById('edit_name').value = classData.name;
    document.getElementById('edit_kademe').value = classData.kademe;
    document.getElementById('edit_student_count').value = classData.student_count;
    document.getElementById('edit_status').value = classData.status;
    new bootstrap.Modal(document.getElementById('editClassModal')).show();
}

function deleteClass(id, name) {
    if (confirm('Bu sınıfı silmek istediğinizden emin misiniz?\n\nSınıf: ' + name + '\n\nBu işlem geri alınamaz!')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
