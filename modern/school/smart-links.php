<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/link_v2.php';
require_once __DIR__ . '/../includes/ensure_schema.php';

require_school_admin();
$user = get_logged_in_user();

$page_title = 'Akıllı Linkler';
$active_nav = 'links';

$survey_id = (int)($_GET['survey_id'] ?? 0);

// Load surveys list
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.status, s.created_at, s.link_token, s.response_count,
           ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([(int)$user['school_id']]);
$surveys = $stmt->fetchAll();

if ($survey_id <= 0 && !empty($surveys)) {
    $survey_id = (int)$surveys[0]['id'];
}

$survey = null;
foreach ($surveys as $s) {
    if ((int)$s['id'] === $survey_id) { $survey = $s; break; }
}

if (!$survey) {
    set_flash_message('Anket bulunamadı!', 'danger');
    header('Location: /modern/school/dashboard.php');
    exit;
}

// Target classes for this survey (for class-scoped smart links)
$stmt = $pdo->prepare("
    SELECT c.*
    FROM survey_target_classes stc
    JOIN classes c ON c.id = stc.class_id
    WHERE stc.survey_id = ? AND stc.is_all_classes = FALSE
    ORDER BY c.kademe, c.name
");
$stmt->execute([$survey_id]);
$classes = $stmt->fetchAll();

$base = modern_base_url();

function modern_build_link(string $baseUrl, string $surveyToken, array $ctx): string {
    $signed = modern_sign_context($surveyToken, $ctx);
    return rtrim($baseUrl, '/') . '/modern/survey/fill.php?token=' . urlencode($surveyToken)
        . '&ctx=' . urlencode($signed['ctx'])
        . '&csig=' . urlencode($signed['csig']);
}

require_once __DIR__ . '/../partials/layout-header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h3 class="mb-1">Akıllı Linkler</h3>
        <div class="muted">Aynı anketi farklı bağlamlarda (sınıf / okul geneli) güvenli şekilde paylaşın.</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-light" href="/school/survey-links.php?survey_id=<?= (int)$survey['id'] ?>">
            <i class="fas fa-link"></i> Klasik Sınıf Linkleri
        </a>
    </div>
</div>

<?= modern_flash_html() ?>

<div class="cardx p-3 mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label muted small mb-1">Anket seç</label>
            <select name="survey_id" class="form-select">
                <?php foreach ($surveys as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === (int)$survey['id'] ? 'selected' : '' ?>>
                        [<?= e($s['kademe']) ?>/<?= e($s['role']) ?>] <?= e($s['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4 d-grid">
            <button class="btn btn-grad" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Linkleri Getir
            </button>
        </div>
    </form>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="cardx p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-0"><?= e($survey['title']) ?></h5>
                    <div class="muted small mt-1">
                        <span class="badge bg-light text-dark"><?= e($survey['kademe']) ?></span>
                        <span class="badge bg-info text-dark"><?= e($survey['role']) ?></span>
                        <span class="badge bg-primary"><?= (int)$survey['response_count'] ?> yanıt</span>
                    </div>
                </div>
                <div class="muted small">
                    Modern doldurma linki: <code>/modern/survey/fill.php</code>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="cardx p-3 h-100">
            <h5 class="mb-2"><i class="fas fa-school"></i> Okul Geneli Linkleri</h5>
            <div class="muted small mb-3">
                Veli/Öğretmen gibi okul geneli hedeflemelerde kullanılabilir. (Sınıf seçimi gerekmez.)
            </div>

            <?php
            $teacherSchool = modern_build_link($base, (string)$survey['link_token'], [
                't' => 'teacher',
                'scope' => 'school',
                'school_id' => (int)$user['school_id'],
            ]);
            $parentSchool = modern_build_link($base, (string)$survey['link_token'], [
                't' => 'parent',
                'scope' => 'school',
                'school_id' => (int)$user['school_id'],
            ]);
            ?>

            <div class="d-grid gap-2">
                <button class="btn btn-outline-light text-start" type="button" onclick="showLink('Öğretmen (Okul Geneli)', <?= json_encode($teacherSchool) ?>)">
                    <i class="fas fa-chalkboard-teacher"></i> Öğretmen (Okul Geneli)
                </button>
                <button class="btn btn-outline-light text-start" type="button" onclick="showLink('Veli (Okul Geneli)', <?= json_encode($parentSchool) ?>)">
                    <i class="fas fa-user-friends"></i> Veli (Okul Geneli)
                </button>
            </div>

            <div class="muted small mt-3">
                Not: Anketin form rolü (<code><?= e($survey['role']) ?></code>) ile hedef grup farklı seçilirse, raporlama tarafında <em>target_group</em> alanı ile ayrıştırılır.
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="cardx p-3 h-100">
            <h5 class="mb-2"><i class="fas fa-users"></i> Sınıf Bazlı Linkler</h5>
            <div class="muted small mb-3">
                Öğrenci/Veli gibi sınıfa bağlı hedeflemelerde kullanılabilir. Link sınıfı değiştirmek mümkün değildir.
            </div>

            <?php if (empty($classes)): ?>
                <div class="alert alert-warning cardx border-0 mb-0">
                    Bu anket için hedef sınıf bulunamadı. (Klasik panelde anket oluştururken sınıf seçilmiş olmalı.)
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                        <thead>
                        <tr>
                            <th>Sınıf</th>
                            <th class="text-end">Öğrenci</th>
                            <th class="text-end"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($classes as $c): ?>
                            <?php
                            $parsed = modern_parse_grade_branch_from_class_name($c['name'] ?? '');
                            $studentClass = modern_build_link($base, (string)$survey['link_token'], [
                                't' => 'student',
                                'scope' => 'class',
                                'school_id' => (int)$user['school_id'],
                                'class_id' => (int)$c['id'],
                                'grade' => $parsed['grade'],
                                'branch' => $parsed['branch'],
                            ]);
                            $parentClass = modern_build_link($base, (string)$survey['link_token'], [
                                't' => 'parent',
                                'scope' => 'class',
                                'school_id' => (int)$user['school_id'],
                                'class_id' => (int)$c['id'],
                                'grade' => $parsed['grade'],
                                'branch' => $parsed['branch'],
                            ]);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($c['name']) ?></div>
                                    <div class="muted small">
                                        <span class="badge bg-light text-dark"><?= e($c['kademe']) ?></span>
                                        <?php if ($parsed['grade'] || $parsed['branch']): ?>
                                            <span class="badge bg-success">Grade <?= e((string)$parsed['grade']) ?><?= $parsed['branch'] ? ' / ' . e((string)$parsed['branch']) : '' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-end muted small"><?= (int)$c['student_count'] ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-light" type="button" onclick="showLink('Öğrenci — <?= e($c['name']) ?>', <?= json_encode($studentClass) ?>)">
                                        <i class="fas fa-user-graduate"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-light" type="button" onclick="showLink('Veli — <?= e($c['name']) ?>', <?= json_encode($parentClass) ?>)">
                                        <i class="fas fa-user-friends"></i>
                                    </button>
                                    <a class="btn btn-sm btn-grad" href="<?= e($studentClass) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-arrow-up-right-from-square"></i>
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

<!-- Link modal -->
<div class="modal fade" id="linkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: rgba(2,6,23,0.98); color:#e5e7eb; border:1px solid rgba(255,255,255,0.10);">
            <div class="modal-header" style="border-color: rgba(255,255,255,0.10);">
                <h5 class="modal-title" id="linkTitle">Link</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="muted small mb-2">Bu linki paylaşın:</div>
                <div class="input-group">
                    <input type="text" class="form-control" id="linkValue" readonly>
                    <button class="btn btn-grad" type="button" onclick="copyLink()">
                        <i class="fas fa-copy"></i> Kopyala
                    </button>
                </div>
                <div class="muted small mt-2">
                    Güvenlik: Link, bağlamı (sınıf/okul) imzalı taşır; link içindeki değerler değiştirilemez.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showLink(title, url) {
        document.getElementById('linkTitle').textContent = title;
        document.getElementById('linkValue').value = url;
        new bootstrap.Modal(document.getElementById('linkModal')).show();
    }
    function copyLink() {
        const el = document.getElementById('linkValue');
        el.select();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(el.value).then(() => alert('Link kopyalandı!')).catch(() => {
                document.execCommand('copy'); alert('Link kopyalandı!');
            });
        } else {
            document.execCommand('copy'); alert('Link kopyalandı!');
        }
    }
</script>

<?php require_once __DIR__ . '/../partials/layout-footer.php'; ?>

