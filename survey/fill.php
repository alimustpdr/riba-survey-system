<?php
require_once __DIR__ . '/../includes/db.php';

// Token kontrolü
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('Geçersiz anket linki!');
}

// Anketi ve form bilgilerini çek
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
    die('Anket bulunamadı veya kapatılmış!');
}

// Soruları çek
$stmt = $pdo->prepare("
    SELECT * FROM questions 
    WHERE form_template_id = ? 
    ORDER BY question_number
");
$stmt->execute([$survey['form_template_id']]);
$questions = $stmt->fetchAll();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
    $answers = [];
    
    // Cevapları topla
    foreach ($questions as $question) {
        $answer = $_POST['question_' . $question['id']] ?? null;
        if ($answer !== null) {
            $answers[$question['id']] = $answer;
        }
    }
    
    // Tüm sorular cevaplanmış mı?
    if (count($answers) !== count($questions)) {
        $error = 'Lütfen tüm soruları cevaplayın!';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Yanıtı kaydet
            $stmt = $pdo->prepare("
                INSERT INTO responses (survey_id, gender, ip_address, user_agent, answers) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $survey['id'],
                $gender,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode($answers)
            ]);
            
            // Anket yanıt sayısını artır
            $stmt = $pdo->prepare("UPDATE surveys SET response_count = response_count + 1 WHERE id = ?");
            $stmt->execute([$survey['id']]);
            
            $pdo->commit();
            
            // Teşekkür sayfasına yönlendir
            header('Location: thank-you.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Yanıt kaydedilirken hata oluştu!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .survey-container { max-width: 800px; margin: 0 auto; }
        .survey-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); margin-bottom: 20px; }
        .survey-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px 15px 0 0; }
        .question-card { border-left: 4px solid #667eea; padding: 20px; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; }
        .option-label { cursor: pointer; padding: 12px; border: 2px solid #dee2e6; border-radius: 8px; transition: all 0.3s; display: block; margin-bottom: 10px; }
        .option-label:hover { background: #f8f9fa; border-color: #667eea; }
        .option-label input[type="radio"]:checked + .option-text { font-weight: bold; }
        /* Checked state styling with fallback */
        .option-label.checked { background: #e7f3ff; border-color: #667eea; }
    </style>
</head>
<body>
    <div class="survey-container">
        <div class="survey-card">
            <div class="survey-header text-center">
                <h2><?= htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ($survey['description']): ?>
                    <p class="mb-0"><?= htmlspecialchars($survey['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <hr class="my-3 border-white">
                <div class="d-flex justify-content-center gap-3">
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($survey['kademe'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($survey['role'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="badge bg-light text-dark"><?= count($questions) ?> Soru</span>
                </div>
            </div>
            
            <div class="p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="surveyForm">
                    <?php if ($survey['gender_field_enabled']): ?>
                        <div class="question-card">
                            <label class="form-label"><strong>Cinsiyetiniz (Opsiyonel)</strong></label>
                            <div>
                                <label class="option-label">
                                    <input type="radio" name="gender" value="erkek" class="form-check-input me-2">
                                    <span class="option-text">Erkek</span>
                                </label>
                                <label class="option-label">
                                    <input type="radio" name="gender" value="kiz" class="form-check-input me-2">
                                    <span class="option-text">Kız</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-card">
                            <h5 class="mb-3">
                                <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                Hangisi sizin için daha önemli?
                            </h5>
                            
                            <label class="option-label">
                                <input type="radio" name="question_<?= $question['id'] ?>" value="A" 
                                       class="form-check-input me-2" required>
                                <span class="option-text">
                                    <strong>A)</strong> <?= htmlspecialchars($question['option_a'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </label>
                            
                            <label class="option-label">
                                <input type="radio" name="question_<?= $question['id'] ?>" value="B" 
                                       class="form-check-input me-2" required>
                                <span class="option-text">
                                    <strong>B)</strong> <?= htmlspecialchars($question['option_b'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Anketi Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center text-white">
            <small>
                <i class="fas fa-school"></i> <?= htmlspecialchars($survey['school_name'], ENT_QUOTES, 'UTF-8') ?>
                | RİBA Anket Sistemi
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add checked class to option labels for better browser compatibility
        document.addEventListener('DOMContentLoaded', function() {
            const radioInputs = document.querySelectorAll('input[type="radio"]');
            radioInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Remove checked class from all labels with same name
                    const name = this.name;
                    document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                        radio.closest('.option-label').classList.remove('checked');
                    });
                    // Add checked class to selected label
                    if (this.checked) {
                        this.closest('.option-label').classList.add('checked');
                    }
                });
            });
        });
    </script>
</body>
</html>
