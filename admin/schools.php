<?php
$page_title = 'Okullar';
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
            $slug = trim($_POST['slug'] ?? '');
            $status = $_POST['status'] ?? 'active';
            
            if (empty($name) || empty($slug)) {
                set_flash_message('Lütfen tüm alanları doldurun!', 'danger');
            } else {
                // Slug benzersizliğini kontrol et
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM schools WHERE slug = ?");
                $stmt->execute([$slug]);
                if ($stmt->fetchColumn() > 0) {
                    set_flash_message('Bu slug zaten kullanılıyor!', 'danger');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO schools (name, slug, status) VALUES (?, ?, ?)");
                    if ($stmt->execute([$name, $slug, $status])) {
                        set_flash_message('Okul başarıyla eklendi!', 'success');
                    } else {
                        set_flash_message('Okul eklenirken hata oluştu!', 'danger');
                    }
                }
            }
            header('Location: schools.php');
            exit;
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $status = $_POST['status'] ?? 'active';
            
            if (empty($name) || empty($slug) || $id <= 0) {
                set_flash_message('Geçersiz veri!', 'danger');
            } else {
                // Slug benzersizliğini kontrol et (kendi ID'si hariç)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM schools WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $id]);
                if ($stmt->fetchColumn() > 0) {
                    set_flash_message('Bu slug zaten kullanılıyor!', 'danger');
                } else {
                    $stmt = $pdo->prepare("UPDATE schools SET name = ?, slug = ?, status = ? WHERE id = ?");
                    if ($stmt->execute([$name, $slug, $status, $id])) {
                        set_flash_message('Okul başarıyla güncellendi!', 'success');
                    } else {
                        set_flash_message('Okul güncellenirken hata oluştu!', 'danger');
                    }
                }
            }
            header('Location: schools.php');
            exit;
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM schools WHERE id = ?");
                if ($stmt->execute([$id])) {
                    set_flash_message('Okul başarıyla silindi!', 'success');
                } else {
                    set_flash_message('Okul silinirken hata oluştu!', 'danger');
                }
            }
            header('Location: schools.php');
            exit;
        }
    }
}

// Okulları listele
$stmt = $pdo->query("SELECT * FROM schools ORDER BY created_at DESC");
$schools = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-school"></i> Okullar</h5>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addSchoolModal">
                    <i class="fas fa-plus"></i> Yeni Okul Ekle
                </button>
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
                                    <th>Durum</th>
                                    <th>Oluşturulma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schools as $school): ?>
                                    <tr>
                                        <td><?= $school['id'] ?></td>
                                        <td><?= e($school['name']) ?></td>
                                        <td><code><?= e($school['slug']) ?></code></td>
                                        <td>
                                            <?php if ($school['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php elseif ($school['status'] === 'inactive'): ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Süresi Dolmuş</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($school['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick='editSchool(<?= json_encode($school) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSchool(<?= $school['id'] ?>, '<?= e($school['name']) ?>')">
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

<!-- Yeni Okul Modal -->
<div class="modal fade" id="addSchoolModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Okul Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Okul Adı</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" required pattern="[a-z0-9-]+" 
                               title="Sadece küçük harf, rakam ve tire kullanın">
                        <small class="text-muted">Örnek: okul-adi</small>
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

<!-- Okul Düzenle Modal -->
<div class="modal fade" id="editSchoolModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editSchoolForm">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Okul Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Okul Adı</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="edit_slug" class="form-control" required pattern="[a-z0-9-]+">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                            <option value="expired">Süresi Dolmuş</option>
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
function editSchool(school) {
    document.getElementById('edit_id').value = school.id;
    document.getElementById('edit_name').value = school.name;
    document.getElementById('edit_slug').value = school.slug;
    document.getElementById('edit_status').value = school.status;
    new bootstrap.Modal(document.getElementById('editSchoolModal')).show();
}

function deleteSchool(id, name) {
    if (confirm('Bu okulu silmek istediğinizden emin misiniz?\n\nOkul: ' + name + '\n\nBu işlem geri alınamaz!')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
