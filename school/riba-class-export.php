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

                <form method="POST" action="/school/riba-class-export-download.php">
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

