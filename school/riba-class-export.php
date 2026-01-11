<?php
$page_title = 'RİBA - Sınıf Sonuç (Excel)';
require_once 'header.php';
require_once __DIR__ . '/../includes/xlsx_template.php';

function kademe_template_path(string $kademe): string {
    $base = __DIR__ . '/../templates/riba/';
    switch ($kademe) {
        case 'okuloncesi':
            return $base . 'okuloncesi_sinif_sonuc.xlsx';
        case 'ilkokul':
            return $base . 'ilkokul_sinif_sonuc.xlsx';
        case 'ortaokul':
            return $base . 'ortaokul_sinif_sonuc.xlsx';
        case 'lise':
            return $base . 'lise_sinif_sonuc.xlsx';
        default:
            throw new RuntimeException('Geçersiz kademe.');
    }
}

function col_letters(int $index): string {
    $letters = '';
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $letters = chr(65 + $mod) . $letters;
        $index = intdiv($index - 1, 26);
    }
    return $letters;
}

function fill_role_sheet(XlsxTemplateFiller $xlsx, string $sheetName, array $responses, array $questionIdToNumber, int $maxRows = 70): void {
    if (!$xlsx->hasSheet($sheetName)) return;

    $i = 0;
    foreach ($responses as $resp) {
        $i++;
        if ($i > $maxRows) break;

        $row = 2 + $i; // row3 = first participant (headers are row1/2)
        $xlsx->setNumber($sheetName, 'A' . $row, $i); // No

        $answers = json_decode($resp['answers'], true);
        if (!is_array($answers)) continue;

        foreach ($answers as $qid => $choice) {
            $qid = (int)$qid;
            if (!isset($questionIdToNumber[$qid])) continue;
            $qNum = (int)$questionIdToNumber[$qid];
            if ($qNum <= 0) continue;

            // Template layout: columns start at C for M1-A and D for M1-B
            $baseCol = 3 + (($qNum - 1) * 2);
            $colIndex = $baseCol + ((strtoupper((string)$choice) === 'B') ? 1 : 0);
            $cell = col_letters($colIndex) . $row;
            $xlsx->setNumber($sheetName, $cell, 1);
        }
    }
}

// Load classes
$stmt = $pdo->prepare("SELECT * FROM classes WHERE school_id = ? AND status = 'active' ORDER BY kademe, name");
$stmt->execute([$user['school_id']]);
$classes = $stmt->fetchAll();

// Load surveys grouped by kademe/role
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.status, s.created_at, ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$user['school_id']]);
$surveys = $stmt->fetchAll();

$byKademeRole = [];
foreach ($surveys as $s) {
    $byKademeRole[$s['kademe']][$s['role']][] = $s;
}

$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
        header('Location: riba-class-export.php');
        exit;
    }

    $class_id = (int)($_POST['class_id'] ?? 0);
    $kademe = $_POST['kademe'] ?? '';
    $ogrenci_survey_id = (int)($_POST['ogrenci_survey_id'] ?? 0);
    $veli_survey_id = (int)($_POST['veli_survey_id'] ?? 0);
    $ogretmen_survey_id = (int)($_POST['ogretmen_survey_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ? AND school_id = ? AND status = 'active'");
    $stmt->execute([$class_id, $user['school_id']]);
    $class = $stmt->fetch();
    if (!$class) {
        set_flash_message('Sınıf bulunamadı!', 'danger');
        header('Location: riba-class-export.php');
        exit;
    }
    if ($kademe !== $class['kademe']) {
        // keep simple: enforce same kademe as class
        $kademe = $class['kademe'];
    }

    // Determine required roles
    $needStudent = ($kademe !== 'okuloncesi');
    if ($needStudent && $ogrenci_survey_id <= 0) {
        set_flash_message('Öğrenci anketini seçmelisiniz!', 'danger');
        header('Location: riba-class-export.php');
        exit;
    }
    if ($veli_survey_id <= 0 || $ogretmen_survey_id <= 0) {
        set_flash_message('Veli ve öğretmen anketlerini seçmelisiniz!', 'danger');
        header('Location: riba-class-export.php');
        exit;
    }

    // Fetch question maps for each selected survey
    $questionIdToNumber = [];
    $roleToSurveyId = [
        'ogrenci' => $ogrenci_survey_id,
        'veli' => $veli_survey_id,
        'ogretmen' => $ogretmen_survey_id,
    ];
    foreach ($roleToSurveyId as $role => $sid) {
        if ($sid <= 0) continue;
        $stmt = $pdo->prepare("
            SELECT q.id, q.question_number
            FROM surveys s
            JOIN questions q ON q.form_template_id = s.form_template_id
            WHERE s.id = ? AND s.school_id = ?
        ");
        $stmt->execute([$sid, $user['school_id']]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $questionIdToNumber[$role][(int)$r['id']] = (int)$r['question_number'];
        }
    }

    // Fetch responses for each survey filtered by class_name
    $responsesByRole = [];
    foreach ($roleToSurveyId as $role => $sid) {
        if ($sid <= 0) continue;
        $stmt = $pdo->prepare("
            SELECT id, answers
            FROM responses
            WHERE survey_id = ? AND class_name = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$sid, $class['name']]);
        $responsesByRole[$role] = $stmt->fetchAll();
    }

    // Fill template (requires php-zip for ZipArchive)
    if (!class_exists('ZipArchive')) {
        set_flash_message('Excel export için PHP Zip (php-zip) eklentisi gerekli. Sunucuda etkinleştirin.', 'danger');
        header('Location: riba-class-export.php');
        exit;
    }

    // Fill template
    $templatePath = kademe_template_path($kademe);
    $xlsx = new XlsxTemplateFiller($templatePath);
    $xlsx->open();

    if ($needStudent) {
        fill_role_sheet($xlsx, 'ogrenci', $responsesByRole['ogrenci'] ?? [], $questionIdToNumber['ogrenci'] ?? []);
    }
    fill_role_sheet($xlsx, 'veli', $responsesByRole['veli'] ?? [], $questionIdToNumber['veli'] ?? []);
    fill_role_sheet($xlsx, 'ogretmen', $responsesByRole['ogretmen'] ?? [], $questionIdToNumber['ogretmen'] ?? []);

    $out = sys_get_temp_dir() . '/riba_sinif_' . bin2hex(random_bytes(6)) . '.xlsx';
    $xlsx->saveTo($out);

    $safeClass = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string)$class['name']);
    $filename = 'riba_' . $kademe . '_sinif_sonuc_' . $safeClass . '.xlsx';

    // Prevent any buffered output from corrupting the XLSX
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($out));
    readfile($out);
    @unlink($out);
    exit;
}

?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-excel"></i> Sınıf Sonuç Çizelgesi (Excel)</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Bu sayfa, seçtiğiniz anketlerin sınıf bazlı yanıtlarını resmi Excel şablonuna otomatik işler.
                    Excel'i indirip açtığınızda şablonun kendi formülleri sonucu hesaplayacaktır.
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                    <div class="mb-3">
                        <label class="form-label">Sınıf</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= e($c['kademe']) ?> / <?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Not: Kademe sınıfın kademesine göre otomatik ele alınır.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kademe</label>
                        <select name="kademe" class="form-select" required>
                            <option value="okuloncesi">okuloncesi</option>
                            <option value="ilkokul">ilkokul</option>
                            <option value="ortaokul">ortaokul</option>
                            <option value="lise">lise</option>
                        </select>
                    </div>

                    <hr>
                    <h6 class="mb-3">Anket Seçimi (Bu kademeye ait)</h6>

                    <div class="mb-3">
                        <label class="form-label">Öğrenci Anketi (okulöncesi hariç)</label>
                        <select name="ogrenci_survey_id" class="form-select">
                            <option value="">Seçiniz...</option>
                            <?php foreach (($byKademeRole['ilkokul']['ogrenci'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[ilkokul] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['ortaokul']['ogrenci'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[ortaokul] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['lise']['ogrenci'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[lise] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Veli Anketi</label>
                        <select name="veli_survey_id" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach (($byKademeRole['okuloncesi']['veli'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[okuloncesi] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['ilkokul']['veli'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[ilkokul] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['ortaokul']['veli'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[ortaokul] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['lise']['veli'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[lise] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Öğretmen Anketi</label>
                        <select name="ogretmen_survey_id" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach (($byKademeRole['okuloncesi']['ogretmen'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[okuloncesi] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['ilkokul']['ogretmen'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[ilkokul] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['ortaokul']['ogretmen'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[ortaokul] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                            <?php foreach (($byKademeRole['lise']['ogretmen'] ?? []) as $s): ?>
                                <option value="<?= (int)$s['id'] ?>">[lise] <?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-download"></i> Excel İndir
                        </button>
                    </div>
                </form>

                <hr>
                <small class="text-muted">
                    Not: Bu ilk sürümde yanıtlar anonimdir; Excel’de “Ad Soyadı” alanı boş bırakılır.
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

