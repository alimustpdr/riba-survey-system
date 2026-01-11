<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/xlsx_template.php';

require_school_admin();
$user = get_logged_in_user();

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

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /school/riba-class-export.php');
        exit;
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('Geçersiz form gönderimi!', 'danger');
        header('Location: /school/riba-class-export.php');
        exit;
    }

    if (!class_exists('ZipArchive')) {
        set_flash_message('Excel export için PHP Zip (php-zip) eklentisi gerekli. Sunucuda etkinleştirin.', 'danger');
        header('Location: /school/riba-class-export.php');
        exit;
    }

    global $pdo;

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
        header('Location: /school/riba-class-export.php');
        exit;
    }
    if ($kademe !== $class['kademe']) {
        $kademe = $class['kademe'];
    }

    $needStudent = ($kademe !== 'okuloncesi');
    if ($needStudent && $ogrenci_survey_id <= 0) {
        set_flash_message('Öğrenci anketini seçmelisiniz!', 'danger');
        header('Location: /school/riba-class-export.php');
        exit;
    }
    if ($veli_survey_id <= 0 || $ogretmen_survey_id <= 0) {
        set_flash_message('Veli ve öğretmen anketlerini seçmelisiniz!', 'danger');
        header('Location: /school/riba-class-export.php');
        exit;
    }

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
        foreach ($stmt->fetchAll() as $r) {
            $questionIdToNumber[$role][(int)$r['id']] = (int)$r['question_number'];
        }
    }

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

    // Clean buffers to avoid corrupting the XLSX
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($out));
    readfile($out);
    @unlink($out);
    exit;
} catch (Throwable $e) {
    set_flash_message('Excel oluşturulurken hata: ' . $e->getMessage(), 'danger');
    header('Location: /school/riba-class-export.php');
    exit;
}

