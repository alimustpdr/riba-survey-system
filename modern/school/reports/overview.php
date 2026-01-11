<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/ensure_schema.php';

require_school_admin();
$user = get_logged_in_user();

$page_title = 'Raporlar';
$active_nav = 'reports';

$survey_id = (int)($_GET['survey_id'] ?? 0);
$mode = (string)($_GET['mode'] ?? 'school'); // school | class | grade | target
$class_id = (int)($_GET['class_id'] ?? 0);
$grade_filter = (string)($_GET['grade'] ?? '');
$target_filter = (string)($_GET['target_group'] ?? '');

// Surveys list
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.status, s.created_at, s.link_token, s.response_count,
           s.form_template_id, ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([(int)$user['school_id']]);
$surveys = $stmt->fetchAll();
if ($survey_id <= 0 && !empty($surveys)) $survey_id = (int)$surveys[0]['id'];

$survey = null;
foreach ($surveys as $s) {
    if ((int)$s['id'] === $survey_id) { $survey = $s; break; }
}
if (!$survey) {
    set_flash_message('Anket bulunamadı!', 'danger');
    header('Location: /modern/school/dashboard.php');
    exit;
}

// Classes list
$stmt = $pdo->prepare("SELECT id, name, kademe FROM classes WHERE school_id = ? AND status = 'active' ORDER BY kademe, name");
$stmt->execute([(int)$user['school_id']]);
$classes = $stmt->fetchAll();

// Questions for this survey
$stmt = $pdo->prepare("SELECT id, question_number, option_a, option_b FROM questions WHERE form_template_id = ? ORDER BY question_number");
$stmt->execute([(int)$survey['form_template_id']]);
$questions = $stmt->fetchAll();

// Detect if response_context table exists
$has_ctx = false;
try {
    $pdo->query("SELECT 1 FROM response_context LIMIT 1");
    $has_ctx = true;
} catch (Throwable $e) {
    $has_ctx = false;
}

// Fetch responses (read-only)
$responses = [];
if ($has_ctx) {
    $sql = "
        SELECT r.id, r.answers, r.created_at, rc.class_id, rc.class_name, rc.grade, rc.target_group, rc.scope
        FROM responses r
        LEFT JOIN response_context rc ON rc.response_id = r.id
        WHERE r.survey_id = ?
    ";
    $params = [(int)$survey['id']];

    if ($mode === 'class' && $class_id > 0) {
        $sql .= " AND rc.class_id = ? ";
        $params[] = $class_id;
    } elseif ($mode === 'grade' && $grade_filter !== '') {
        $sql .= " AND rc.grade = ? ";
        $params[] = $grade_filter;
    } elseif ($mode === 'target' && $target_filter !== '') {
        $sql .= " AND rc.target_group = ? ";
        $params[] = $target_filter;
    }

    $sql .= " ORDER BY r.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $responses = $stmt->fetchAll();
} else {
    // Fallback: use responses only (limited filters)
    $stmt = $pdo->prepare("SELECT id, answers, created_at, class_name FROM responses WHERE survey_id = ? ORDER BY created_at ASC");
    $stmt->execute([(int)$survey['id']]);
    $responses = $stmt->fetchAll();
}

// Aggregate counts A/B per question_id
$counts = []; // qid => ['A'=>int,'B'=>int]
foreach ($questions as $q) {
    $qid = (int)$q['id'];
    $counts[$qid] = ['A' => 0, 'B' => 0];
}

$valid_responses = 0;
foreach ($responses as $r) {
    $ans = json_decode($r['answers'] ?? '', true);
    if (!is_array($ans)) continue;
    $valid_responses++;
    foreach ($ans as $qid => $choice) {
        $qid = (int)$qid;
        $c = strtoupper((string)$choice);
        if (!isset($counts[$qid])) continue;
        if ($c === 'A' || $c === 'B') $counts[$qid][$c]++;
    }
}

// Grade options (from context table if present)
$grade_options = [];
$target_options = ['student' => 'Öğrenci', 'parent' => 'Veli', 'teacher' => 'Öğretmen'];
if ($has_ctx) {
    $stmt = $pdo->prepare("SELECT DISTINCT grade FROM response_context WHERE survey_id = ? AND grade IS NOT NULL AND grade != '' ORDER BY grade");
    $stmt->execute([(int)$survey['id']]);
    foreach ($stmt->fetchAll() as $row) {
        $grade_options[] = (string)$row['grade'];
    }
}

require_once __DIR__ . '/../../partials/layout-header.php';

function mode_label(string $mode): string {
    if ($mode === 'class') return 'Sınıf';
    if ($mode === 'grade') return 'Sınıf Düzeyi';
    if ($mode === 'target') return 'Hedef Grup';
    return 'Okul';
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h3 class="mb-1">Raporlar</h3>
        <div class="muted">Seçilen anket için A/B dağılımları (okul/sınıf/düzey).</div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-light" onclick="window.print()">
            <i class="fas fa-print"></i> PDF / Yazdır
        </button>
    </div>
</div>

<?= modern_flash_html() ?>

<?php if (!$has_ctx): ?>
    <div class="alert alert-warning cardx border-0">
        <strong>Not:</strong> Bağlam raporları için modern doldurma linkleri kullanılmalı.
        Şu an sadece temel özet gösteriliyor. (Tablo yok: <code>response_context</code>)
    </div>
<?php endif; ?>

<div class="cardx p-3 mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-lg-6">
            <label class="form-label muted small mb-1">Anket</label>
            <select name="survey_id" class="form-select">
                <?php foreach ($surveys as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === (int)$survey['id'] ? 'selected' : '' ?>>
                        [<?= e($s['kademe']) ?>/<?= e($s['role']) ?>] <?= e($s['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-3">
            <label class="form-label muted small mb-1">Kapsam</label>
            <select name="mode" class="form-select" onchange="this.form.submit()">
                <option value="school" <?= $mode === 'school' ? 'selected' : '' ?>>Okul geneli</option>
                <option value="class" <?= $mode === 'class' ? 'selected' : '' ?>>Sınıf</option>
                <option value="grade" <?= $mode === 'grade' ? 'selected' : '' ?>>Sınıf düzeyi</option>
                <option value="target" <?= $mode === 'target' ? 'selected' : '' ?>>Hedef grup</option>
            </select>
        </div>
        <div class="col-12 col-lg-3 d-grid">
            <button class="btn btn-grad" type="submit">
                <i class="fas fa-filter"></i> Uygula
            </button>
        </div>

        <?php if ($mode === 'class'): ?>
            <div class="col-12 col-lg-6">
                <label class="form-label muted small mb-1">Sınıf</label>
                <select name="class_id" class="form-select">
                    <option value="0">Seçiniz...</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === $class_id ? 'selected' : '' ?>>
                            <?= e($c['kademe']) ?> / <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php elseif ($mode === 'grade'): ?>
            <div class="col-12 col-lg-6">
                <label class="form-label muted small mb-1">Düzey</label>
                <select name="grade" class="form-select">
                    <option value="">Seçiniz...</option>
                    <?php foreach ($grade_options as $g): ?>
                        <option value="<?= e($g) ?>" <?= $g === $grade_filter ? 'selected' : '' ?>><?= e($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php elseif ($mode === 'target'): ?>
            <div class="col-12 col-lg-6">
                <label class="form-label muted small mb-1">Hedef grup</label>
                <select name="target_group" class="form-select">
                    <option value="">Seçiniz...</option>
                    <?php foreach ($target_options as $k => $lbl): ?>
                        <option value="<?= e($k) ?>" <?= $k === $target_filter ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="cardx p-3 mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0"><?= e($survey['title']) ?></h5>
            <div class="muted small">
                Kapsam: <strong><?= e(mode_label($mode)) ?></strong>
                · Yanıt: <strong><?= (int)$valid_responses ?></strong>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark"><?= e($survey['kademe']) ?></span>
            <span class="badge bg-info text-dark"><?= e($survey['role']) ?></span>
        </div>
    </div>
</div>

<div class="cardx p-3 mb-3">
    <h5 class="mb-2"><i class="fas fa-chart-column"></i> Özet Grafik</h5>
    <div class="muted small mb-2">Her soruda A/B işaretlenme sayısı.</div>
    <canvas id="abChart" height="120"></canvas>
</div>

<div class="cardx p-3">
    <h5 class="mb-2"><i class="fas fa-table"></i> Soru Bazlı Dağılım</h5>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
            <tr>
                <th>#</th>
                <th>A</th>
                <th class="text-end">A (sayı)</th>
                <th>B</th>
                <th class="text-end">B (sayı)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($questions as $q): ?>
                <?php
                $qid = (int)$q['id'];
                $a = (int)$counts[$qid]['A'];
                $b = (int)$counts[$qid]['B'];
                $tot = max(1, $a + $b);
                $ap = round(($a / $tot) * 100);
                $bp = 100 - $ap;
                ?>
                <tr>
                    <td class="fw-semibold"><?= (int)$q['question_number'] ?></td>
                    <td style="min-width:220px;">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: <?= (int)$ap ?>%"></div>
                        </div>
                        <div class="muted small mt-1"><?= (int)$ap ?>%</div>
                    </td>
                    <td class="text-end"><?= $a ?></td>
                    <td style="min-width:220px;">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: <?= (int)$bp ?>%; filter: saturate(0.6)"></div>
                        </div>
                        <div class="muted small mt-1"><?= (int)$bp ?>%</div>
                    </td>
                    <td class="text-end"><?= $b ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .topbar, .navpills, .btn, .alert { display: none !important; }
    .cardx { background: #fff !important; color:#000 !important; border: 1px solid #ddd !important; }
    .table { color: #000 !important; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const labels = <?= json_encode(array_map(fn($q) => (string)$q['question_number'], $questions)) ?>;
        const dataA = <?= json_encode(array_map(fn($q) => (int)$counts[(int)$q['id']]['A'], $questions)) ?>;
        const dataB = <?= json_encode(array_map(fn($q) => (int)$counts[(int)$q['id']]['B'], $questions)) ?>;

        const ctx = document.getElementById('abChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'A', data: dataA, backgroundColor: 'rgba(99,102,241,0.75)' },
                    { label: 'B', data: dataB, backgroundColor: 'rgba(34,197,94,0.70)' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, ticks: { color: 'rgba(229,231,235,0.85)' }, grid: { color: 'rgba(255,255,255,0.06)' } },
                    y: { stacked: true, ticks: { color: 'rgba(229,231,235,0.85)' }, grid: { color: 'rgba(255,255,255,0.06)' } }
                },
                plugins: {
                    legend: { labels: { color: 'rgba(229,231,235,0.85)' } },
                    tooltip: { enabled: true }
                }
            }
        });
    })();
</script>

<?php require_once __DIR__ . '/../../partials/layout-footer.php'; ?>

