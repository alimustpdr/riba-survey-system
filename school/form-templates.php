<?php
$page_title = 'Form Şablonları';
require_once 'header.php';
require_once __DIR__ . '/../includes/riba_instructions.php';

$kademe = $_GET['kademe'] ?? '';
$role = $_GET['role'] ?? '';
$template_id = (int)($_GET['id'] ?? 0);

$kademes = ['okuloncesi', 'ilkokul', 'ortaokul', 'lise'];
$roles = ['ogrenci', 'veli', 'ogretmen'];

// Template detail view
if ($template_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM form_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();

    if (!$template) {
        set_flash_message('Form şablonu bulunamadı!', 'danger');
        header('Location: form-templates.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM questions WHERE form_template_id = ? ORDER BY question_number");
    $stmt->execute([$template_id]);
    $questions = $stmt->fetchAll();

    $instructions = get_riba_instructions_text($template['role']);
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><?= e($template['title']) ?></h4>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-secondary"><?= e($template['kademe']) ?></span>
                <span class="badge bg-info"><?= e($template['role']) ?></span>
                <span class="badge bg-success"><?= (int)$template['question_count'] ?> soru</span>
            </div>
        </div>
        <a href="form-templates.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Listeye Dön
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Yönerge</h6>
        </div>
        <div class="card-body">
            <div class="text-muted" style="white-space: pre-line;"><?= e($instructions) ?></div>
            <hr>
            <small class="text-muted">
                Bu formda PDF’te ayrıca genellikle <strong>Tarih / Sınıf / Cinsiyet</strong> alanları yer alır.
                (Sınıfa özel link akışında bu alanı otomatik dolduracağız.)
            </small>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-list"></i> Sorular (A/B)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($questions)): ?>
                <div class="alert alert-warning mb-0">Bu şablon için soru bulunamadı.</div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($questions as $q): ?>
                        <div class="list-group-item">
                            <div class="fw-bold mb-2">Madde <?= (int)$q['question_number'] ?></div>
                            <div class="mb-2"><strong>A)</strong> <?= e($q['option_a']) ?></div>
                            <div><strong>B)</strong> <?= e($q['option_b']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    require_once 'footer.php';
    exit;
}

// List view (with filters)
$where = [];
$params = [];
if (in_array($kademe, $kademes, true)) {
    $where[] = "kademe = ?";
    $params[] = $kademe;
}
if (in_array($role, $roles, true)) {
    $where[] = "role = ?";
    $params[] = $role;
}
$sql = "SELECT * FROM form_templates";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY kademe, role";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$templates = $stmt->fetchAll();

?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Form Şablonları</h5>
                <a href="form-templates.php" class="btn btn-light btn-sm">
                    <i class="fas fa-rotate-right"></i> Filtreyi Sıfırla
                </a>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-6 col-md-4">
                        <select name="kademe" class="form-select">
                            <option value="">Kademe (tümü)</option>
                            <?php foreach ($kademes as $k): ?>
                                <option value="<?= e($k) ?>" <?= $kademe === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <select name="role" class="form-select">
                            <option value="">Rol (tümü)</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= e($r) ?>" <?= $role === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-filter"></i> Filtrele
                        </button>
                    </div>
                </form>

                <?php if (empty($templates)): ?>
                    <div class="alert alert-info mb-0">Şablon bulunamadı.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Kademe</th>
                                    <th>Rol</th>
                                    <th>Soru</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $t): ?>
                                    <tr>
                                        <td><strong><?= e($t['title']) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= e($t['kademe']) ?></span></td>
                                        <td><span class="badge bg-info"><?= e($t['role']) ?></span></td>
                                        <td><span class="badge bg-success"><?= (int)$t['question_count'] ?></span></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-primary" href="form-templates.php?id=<?= (int)$t['id'] ?>">
                                                <i class="fas fa-eye"></i> Görüntüle
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
</div>

<?php require_once 'footer.php'; ?>

