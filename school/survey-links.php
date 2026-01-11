<?php
$page_title = 'Sınıf Linkleri';
require_once 'header.php';
require_once __DIR__ . '/../includes/link.php';

$survey_id = (int)($_GET['survey_id'] ?? 0);
if ($survey_id <= 0) {
    set_flash_message('Geçersiz anket!', 'danger');
    header('Location: surveys.php');
    exit;
}

// Survey + template
$stmt = $pdo->prepare("
    SELECT s.*, ft.kademe, ft.role, ft.title as form_title
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.id = ? AND s.school_id = ?
");
$stmt->execute([$survey_id, $user['school_id']]);
$survey = $stmt->fetch();

if (!$survey) {
    set_flash_message('Anket bulunamadı!', 'danger');
    header('Location: surveys.php');
    exit;
}

// Target classes
$stmt = $pdo->prepare("
    SELECT c.*
    FROM survey_target_classes stc
    JOIN classes c ON c.id = stc.class_id
    WHERE stc.survey_id = ? AND stc.is_all_classes = FALSE
    ORDER BY c.kademe, c.name
");
$stmt->execute([$survey_id]);
$classes = $stmt->fetchAll();

function build_class_link(string $baseUrl, string $surveyToken, int $classId): string {
    $sig = sign_survey_class_token($surveyToken, $classId);
    return rtrim($baseUrl, '/') . '/survey/fill.php?token=' . urlencode($surveyToken) . '&c=' . $classId . '&sig=' . urlencode($sig);
}

$baseUrl = defined('APP_URL') ? APP_URL : (($_SERVER['HTTPS'] ?? '') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1"><?= e($survey['title']) ?></h5>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-secondary"><?= e($survey['kademe']) ?></span>
            <span class="badge bg-info"><?= e($survey['role']) ?></span>
            <span class="badge bg-primary"><?= e($survey['form_title']) ?></span>
        </div>
        <small class="text-muted">Her sınıf için ayrı link üretildi. Bu linkler sınıf dışında kullanılamaz.</small>
    </div>
    <a href="surveys.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Anketlere Dön
    </a>
</div>

<?php if (empty($classes)): ?>
    <div class="alert alert-warning">
        Bu anket için hedef sınıf seçilmemiş. Yeni anket oluştururken sınıf seçmelisiniz.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-link"></i> Sınıf Linkleri</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Sınıf</th>
                            <th>Kademe</th>
                            <th>Öğrenci Sayısı</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <?php $link = build_class_link($baseUrl, $survey['link_token'], (int)$class['id']); ?>
                            <tr>
                                <td><strong><?= e($class['name']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= e($class['kademe']) ?></span></td>
                                <td><?= (int)$class['student_count'] ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-success" onclick="showLink(<?= json_encode($link) ?>)">
                                        <i class="fas fa-copy"></i> Link
                                    </button>
                                    <a class="btn btn-sm btn-primary" href="<?= e($link) ?>" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="linkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sınıf Linki</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Bu linki sınıf öğretmeni/katılımcılarla paylaşın:</p>
                    <div class="input-group">
                        <input type="text" class="form-control" id="classSurveyLink" readonly>
                        <button class="btn btn-primary" onclick="copyClassLink()">
                            <i class="fas fa-copy"></i> Kopyala
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showLink(url) {
        document.getElementById('classSurveyLink').value = url;
        new bootstrap.Modal(document.getElementById('linkModal')).show();
    }
    function copyClassLink() {
        const el = document.getElementById('classSurveyLink');
        el.select();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(el.value).then(() => alert('Link kopyalandı!')).catch(() => {
                document.execCommand('copy');
                alert('Link kopyalandı!');
            });
        } else {
            document.execCommand('copy');
            alert('Link kopyalandı!');
        }
    }
    </script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

