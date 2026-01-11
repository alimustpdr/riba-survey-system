<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/link_v2.php';
require_once __DIR__ . '/../includes/ensure_schema.php';
require_once __DIR__ . '/../../includes/riba_instructions.php';
require_once __DIR__ . '/../../includes/link.php'; // for legacy class link signature support

// Token
$token = (string)($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    die('Geçersiz anket linki!');
}

// Load survey + template
$stmt = $pdo->prepare("
    SELECT s.*, ft.title as form_title, ft.kademe, ft.role, ft.question_count,
           sch.name as school_name
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    JOIN schools sch ON s.school_id = sch.id
    WHERE s.link_token = ? AND s.status = 'active'
");
$stmt->execute([$token]);
$survey = $stmt->fetch();
if (!$survey) {
    http_response_code(404);
    die('Anket bulunamadı veya kapatılmış!');
}

// Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE form_template_id = ? ORDER BY question_number");
$stmt->execute([(int)$survey['form_template_id']]);
$questions = $stmt->fetchAll();
if (empty($questions)) {
    http_response_code(500);
    die('Bu anket için soru bulunamadı!');
}

// Context: either modern ctx/csig OR legacy c/sig
$ctx_b64 = (string)($_GET['ctx'] ?? ($_POST['ctx'] ?? ''));
$csig = (string)($_GET['csig'] ?? ($_POST['csig'] ?? ''));
$ctx = null;
if ($ctx_b64 !== '' || $csig !== '') {
    $ctx = modern_verify_context($token, $ctx_b64, $csig);
}

$class = null;
$class_id = 0;
$target_group = null; // student / parent / teacher
$scope = null;        // class / school
$grade = null;
$branch = null;
$class_label_for_storage = null;

if (is_array($ctx)) {
    $target_group = isset($ctx['t']) ? (string)$ctx['t'] : null;
    $scope = isset($ctx['scope']) ? (string)$ctx['scope'] : null;
    $class_id = isset($ctx['class_id']) ? (int)$ctx['class_id'] : 0;
    if ($class_id > 0) {
        // Ensure class belongs to this school AND is targeted by this survey
        $stmt = $pdo->prepare("
            SELECT c.*
            FROM survey_target_classes stc
            JOIN classes c ON c.id = stc.class_id
            WHERE stc.survey_id = ? AND stc.class_id = ? AND stc.is_all_classes = FALSE AND c.school_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$survey['id'], $class_id, (int)$survey['school_id']]);
        $class = $stmt->fetch();
        if (!$class) {
            http_response_code(403);
            die('Bu sınıf bu ankete ait değil!');
        }
        $parsed = modern_parse_grade_branch_from_class_name($class['name'] ?? '');
        $grade = $parsed['grade'];
        $branch = $parsed['branch'];
        $class_label_for_storage = (string)$class['name'];
    } else {
        // School-wide flow (teacher/parent)
        $class_label_for_storage = 'OKUL_GENELI';
    }
} else {
    // Legacy class signed link (token + c + sig)
    $class_id = isset($_GET['c']) ? (int)$_GET['c'] : (isset($_POST['c']) ? (int)$_POST['c'] : 0);
    $sig = (string)($_GET['sig'] ?? ($_POST['sig'] ?? ''));
    if ($class_id > 0) {
        // If class id provided without signature, validate it then redirect to signed link (compat UX).
        if ($sig === '') {
            $stmt = $pdo->prepare("
                SELECT c.id
                FROM survey_target_classes stc
                JOIN classes c ON c.id = stc.class_id
                WHERE stc.survey_id = ? AND stc.class_id = ? AND stc.is_all_classes = FALSE AND c.school_id = ?
                LIMIT 1
            ");
            $stmt->execute([(int)$survey['id'], $class_id, (int)$survey['school_id']]);
            $ok = $stmt->fetch();
            if (!$ok) {
                http_response_code(403);
                die('Bu sınıf bu ankete ait değil!');
            }
            $signed = sign_survey_class_token($survey['link_token'], $class_id);
            $redir = '/modern/survey/fill.php?token=' . urlencode($survey['link_token']) . '&c=' . $class_id . '&sig=' . urlencode($signed);
            header('Location: ' . $redir);
            exit;
        }

        if (!verify_survey_class_token($survey['link_token'], $class_id, (string)$sig)) {
            http_response_code(403);
            die('Geçersiz veya değiştirilmiş sınıf linki!');
        }
        $stmt = $pdo->prepare("
            SELECT c.*
            FROM survey_target_classes stc
            JOIN classes c ON c.id = stc.class_id
            WHERE stc.survey_id = ? AND stc.class_id = ? AND stc.is_all_classes = FALSE AND c.school_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$survey['id'], $class_id, (int)$survey['school_id']]);
        $class = $stmt->fetch();
        if (!$class) {
            http_response_code(403);
            die('Bu sınıf bu ankete ait değil!');
        }
        $parsed = modern_parse_grade_branch_from_class_name($class['name'] ?? '');
        $grade = $parsed['grade'];
        $branch = $parsed['branch'];
        $class_label_for_storage = (string)$class['name'];
        $target_group = null;
        $scope = 'class';
    }
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender = isset($_POST['gender']) ? (string)$_POST['gender'] : null;
    if ($gender !== null && !in_array($gender, ['erkek', 'kiz'], true)) {
        $gender = null;
    }

    // Collect answers keyed by question id
    $answers = [];
    foreach ($questions as $q) {
        $qid = (int)$q['id'];
        $val = $_POST['q_' . $qid] ?? null;
        if ($val === 'A' || $val === 'B') {
            $answers[$qid] = $val;
        }
    }

    // Validation
    if (count($answers) !== count($questions)) {
        $error = 'Lütfen tüm soruları cevaplayın.';
    } elseif (!$class_label_for_storage) {
        // Modern UI supports school-wide links, but must have a storage label.
        $class_label_for_storage = 'OKUL_GENELI';
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            // Save response (existing structure)
            $stmt = $pdo->prepare("
                INSERT INTO responses (survey_id, class_name, gender, ip_address, user_agent, answers)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                (int)$survey['id'],
                $class_label_for_storage,
                $gender,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode($answers, JSON_UNESCAPED_UNICODE)
            ]);
            $response_id = (int)$pdo->lastInsertId();

            // Update survey counter (existing behavior)
            $stmt = $pdo->prepare("UPDATE surveys SET response_count = response_count + 1 WHERE id = ?");
            $stmt->execute([(int)$survey['id']]);

            // Store additive context metadata (best-effort)
            try {
                modern_ensure_response_context_table($pdo);

                $ctx_payload = $ctx;
                if (!is_array($ctx_payload)) {
                    $ctx_payload = [
                        't' => $target_group,
                        'scope' => $scope,
                        'class_id' => $class ? (int)$class['id'] : null,
                        'class_name' => $class_label_for_storage,
                    ];
                }
                $stmt = $pdo->prepare("
                    INSERT INTO response_context
                    (response_id, survey_id, school_id, class_id, class_name, kademe, role, target_group, scope, grade, branch, ctx_json)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $response_id,
                    (int)$survey['id'],
                    (int)$survey['school_id'],
                    $class ? (int)$class['id'] : null,
                    $class_label_for_storage,
                    (string)$survey['kademe'],
                    (string)$survey['role'],
                    $target_group,
                    $scope,
                    $grade,
                    $branch,
                    json_encode($ctx_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            } catch (Throwable $ignored) {
                // Context storage is additive; if it fails, core response must still succeed.
            }

            $pdo->commit();
            header('Location: /modern/survey/thank-you.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Yanıt kaydedilirken hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

$instructions = get_riba_instructions_text((string)$survey['role']);
$total = count($questions);

function badge_for_target(?string $t): ?string {
    if ($t === 'student') return 'Öğrenci';
    if ($t === 'parent') return 'Veli';
    if ($t === 'teacher') return 'Öğretmen';
    return null;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($survey['title']) ?> - RİBA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg: #0b1220;
            --card: rgba(255,255,255,0.06);
            --card-border: rgba(255,255,255,0.10);
            --muted: rgba(229,231,235,0.75);
            --accent1: #6366f1;
            --accent2: #22c55e;
        }
        body { background: radial-gradient(900px 600px at 10% 10%, rgba(99,102,241,0.18), transparent 60%),
                        radial-gradient(900px 600px at 90% 20%, rgba(34,197,94,0.14), transparent 60%),
                        linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
               color: #e5e7eb; min-height: 100vh; padding: 18px; }
        .shell { max-width: 860px; margin: 0 auto; }
        .cardx { background: var(--card); border: 1px solid var(--card-border); border-radius: 16px; }
        .muted { color: var(--muted); }
        .badge-grad { background: linear-gradient(135deg, var(--accent1) 0%, var(--accent2) 100%); }
        .q-step { display: none; }
        .q-step.active { display: block; }
        .option {
            display: block;
            cursor: pointer;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.04);
        }
        .option:hover { border-color: rgba(255,255,255,0.30); }
        .option input { margin-right: 10px; }
        .option.selected { border-color: rgba(99,102,241,0.80); background: rgba(99,102,241,0.12); }
        .progress { background: rgba(255,255,255,0.10); }
        .progress-bar { background: linear-gradient(135deg, var(--accent1) 0%, var(--accent2) 100%); }
        .form-check-input { background-color: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.20); }
        .btn-grad { background: linear-gradient(135deg, var(--accent1) 0%, var(--accent2) 100%); border: none; }
        .btn-grad:hover { filter: brightness(1.05); }
    </style>
</head>
<body>
<div class="shell">
    <div class="cardx p-3 p-md-4 mb-3">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-grad rounded-pill px-3 py-2"><i class="fas fa-clipboard-check"></i> RİBA</span>
                    <span class="muted small"><?= e($survey['school_name']) ?></span>
                </div>
                <h3 class="mb-1"><?= e($survey['title']) ?></h3>
                <?php if (!empty($survey['description'])): ?>
                    <div class="muted"><?= e($survey['description']) ?></div>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <span class="badge bg-light text-dark"><?= e($survey['kademe']) ?></span>
                    <span class="badge bg-info text-dark"><?= e($survey['role']) ?></span>
                    <?php $tBadge = badge_for_target($target_group); ?>
                    <?php if ($tBadge): ?>
                        <span class="badge bg-success"><?= e($tBadge) ?></span>
                    <?php endif; ?>
                </div>
                <div class="muted small mt-2">
                    <?php if ($class): ?>
                        <i class="fas fa-users"></i> Sınıf: <strong><?= e($class['name']) ?></strong>
                    <?php else: ?>
                        <i class="fas fa-school"></i> Okul geneli
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr class="border-white border-opacity-10 my-3">
        <div class="muted" style="white-space: pre-line;"><?= e($instructions) ?></div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger cardx border-0">
            <i class="fas fa-triangle-exclamation"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="surveyForm" class="cardx p-3 p-md-4">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <?php if ($ctx_b64 !== '' && $csig !== ''): ?>
            <input type="hidden" name="ctx" value="<?= e($ctx_b64) ?>">
            <input type="hidden" name="csig" value="<?= e($csig) ?>">
        <?php endif; ?>
        <?php if (!$ctx && $class && isset($_GET['sig'])): ?>
            <input type="hidden" name="c" value="<?= (int)$class['id'] ?>">
            <input type="hidden" name="sig" value="<?= e((string)($_GET['sig'] ?? '')) ?>">
        <?php endif; ?>

        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="muted small">
                <span id="progressText">1 / <?= (int)$total ?></span>
            </div>
            <div class="flex-grow-1">
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
            <div class="muted small d-none d-sm-block">
                <i class="fas fa-mobile-screen"></i> Mobil uyumlu
            </div>
        </div>

        <?php if ((int)$survey['gender_field_enabled'] === 1): ?>
            <div class="mt-3">
                <div class="muted small mb-2">Cinsiyet (opsiyonel)</div>
                <div class="d-flex gap-3 flex-wrap">
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="gender" value="erkek">
                        <span class="form-check-label">Erkek</span>
                    </label>
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="gender" value="kiz">
                        <span class="form-check-label">Kız</span>
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <hr class="border-white border-opacity-10 my-3">

        <?php foreach ($questions as $i => $q): ?>
            <?php $qid = (int)$q['id']; ?>
            <div class="q-step <?= $i === 0 ? 'active' : '' ?>" data-step="<?= (int)$i ?>">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-grad rounded-pill px-3 py-2">Soru <?= (int)($i + 1) ?></span>
                    <span class="muted small">Hangisi sizin için daha önemli?</span>
                </div>

                <div class="d-grid gap-2">
                    <label class="option" data-for="A">
                        <input type="radio" name="q_<?= $qid ?>" value="A">
                        <strong>A)</strong> <?= e($q['option_a']) ?>
                    </label>
                    <label class="option" data-for="B">
                        <input type="radio" name="q_<?= $qid ?>" value="B">
                        <strong>B)</strong> <?= e($q['option_b']) ?>
                    </label>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center gap-2 mt-4">
            <button type="button" class="btn btn-outline-light" id="prevBtn" disabled>
                <i class="fas fa-arrow-left"></i> Geri
            </button>

            <div class="muted small text-center d-none d-sm-block">
                İpucu: Seçim yaptıktan sonra <strong>İleri</strong> ile devam edin.
            </div>

            <button type="button" class="btn btn-grad" id="nextBtn">
                İleri <i class="fas fa-arrow-right"></i>
            </button>
            <button type="submit" class="btn btn-grad d-none" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Gönder
            </button>
        </div>
    </form>
</div>

<script>
    (function () {
        const steps = Array.from(document.querySelectorAll('.q-step'));
        const total = steps.length;
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');

        let current = 0;

        function setActiveStep(idx) {
            current = Math.max(0, Math.min(total - 1, idx));
            steps.forEach((el, i) => el.classList.toggle('active', i === current));
            prevBtn.disabled = current === 0;
            const pct = Math.round(((current + 1) / total) * 100);
            progressText.textContent = (current + 1) + ' / ' + total;
            progressBar.style.width = pct + '%';

            const isLast = current === total - 1;
            nextBtn.classList.toggle('d-none', isLast);
            submitBtn.classList.toggle('d-none', !isLast);

            // scroll nicely on mobile
            const top = document.querySelector('.shell');
            if (top) top.scrollIntoView({behavior: 'smooth', block: 'start'});
        }

        function currentAnswered() {
            const step = steps[current];
            const radio = step.querySelector('input[type="radio"]:checked');
            return !!radio;
        }

        function updateSelectedStyles(stepEl) {
            const options = stepEl.querySelectorAll('.option');
            options.forEach(opt => opt.classList.remove('selected'));
            const checked = stepEl.querySelector('input[type="radio"]:checked');
            if (checked) {
                const label = checked.closest('.option');
                if (label) label.classList.add('selected');
            }
        }

        steps.forEach(step => {
            step.addEventListener('change', () => updateSelectedStyles(step));
            updateSelectedStyles(step);
        });

        prevBtn.addEventListener('click', () => setActiveStep(current - 1));
        nextBtn.addEventListener('click', () => {
            if (!currentAnswered()) {
                alert('Lütfen bu soruda A veya B seçiniz.');
                return;
            }
            setActiveStep(current + 1);
        });

        // Initialize
        setActiveStep(0);
    })();
</script>
</body>
</html>

