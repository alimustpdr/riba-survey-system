<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Geçersiz anket linki!');
}

// Get survey information
$stmt = $pdo->prepare("
    SELECT s.*, ft.title as form_title, ft.kademe, ft.role, ft.question_count,
           sch.name as school_name, sch.gender_field_enabled
    FROM surveys s
    LEFT JOIN form_templates ft ON s.form_template_id = ft.id
    LEFT JOIN schools sch ON s.school_id = sch.id
    WHERE s.link_token = ?
");
$stmt->execute([$token]);
$survey = $stmt->fetch();

if (!$survey) {
    die('Anket bulunamadı!');
}

if ($survey['status'] !== 'active') {
    die('Bu anket kapatılmış!');
}

// Get survey questions
$questions = get_form_questions($survey['form_template_id']);

// Get available classes for this survey
$available_classes = [];
if ($survey['target_all_classes']) {
    // Get all classes from school
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE school_id = ? AND status = 'active' ORDER BY kademe, name");
    $stmt->execute([$survey['school_id']]);
    $available_classes = $stmt->fetchAll();
} else {
    // Get only survey-specific classes
    $stmt = $pdo->prepare("
        SELECT c.* 
        FROM survey_classes sc
        JOIN classes c ON sc.class_id = c.id
        WHERE sc.survey_id = ? AND c.status = 'active'
        ORDER BY c.kademe, c.name
    ");
    $stmt->execute([$survey['id']]);
    $available_classes = $stmt->fetchAll();
}

$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $answers = $_POST['answers'] ?? [];
    
    // Validate answers
    if (count($answers) !== count($questions)) {
        $error = 'Lütfen tüm soruları cevaplayınız!';
    } else {
        // Check all questions are answered
        $all_answered = true;
        foreach ($questions as $q) {
            if (!isset($answers[$q['id']]) || empty($answers[$q['id']])) {
                $all_answered = false;
                break;
            }
        }
        
        if (!$all_answered) {
            $error = 'Lütfen tüm soruları cevaplayınız!';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Prepare answers JSON
                $answers_json = json_encode($answers, JSON_UNESCAPED_UNICODE);
                
                // Insert response
                $stmt = $pdo->prepare("
                    INSERT INTO responses (
                        survey_id, class_id, gender, ip_address, user_agent, answers, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                
                $stmt->execute([
                    $survey['id'],
                    $class_id,
                    $gender,
                    $ip_address,
                    $user_agent,
                    $answers_json
                ]);
                
                // Update survey response count
                $stmt = $pdo->prepare("UPDATE surveys SET response_count = response_count + 1 WHERE id = ?");
                $stmt->execute([$survey['id']]);
                
                $pdo->commit();
                
                $success = true;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Anket gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($survey['title']) ?> - RİBA Anketi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .survey-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .survey-header {
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .survey-body {
            background: white;
            padding: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .question-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .question-number {
            background: var(--primary-gradient);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .option-label {
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            margin-bottom: 10px;
        }
        .option-label:hover {
            border-color: #667eea;
            background-color: #f0f0ff;
        }
        .option-label input[type="radio"]:checked + .option-text {
            font-weight: bold;
        }
        .option-label input[type="radio"]:checked ~ * {
            color: #667eea;
        }
        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .success-card {
            background: white;
            padding: 50px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container survey-container">
        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="success-card">
                <i class="fas fa-check-circle success-icon"></i>
                <h1 class="mb-3">Teşekkür Ederiz!</h1>
                <p class="lead">Anketimize katılımınız için teşekkür ederiz.</p>
                <p class="text-muted">Yanıtlarınız başarıyla kaydedildi.</p>
                <hr class="my-4">
                <p class="mb-0">
                    <strong><?= e($survey['school_name']) ?></strong><br>
                    <small class="text-muted">RİBA Anket Sistemi</small>
                </p>
            </div>
        <?php else: ?>
            <!-- Survey Form -->
            <div class="survey-header">
                <h1 class="mb-2"><?= e($survey['title']) ?></h1>
                <?php if ($survey['description']): ?>
                    <p class="mb-2"><?= e($survey['description']) ?></p>
                <?php endif; ?>
                <p class="mb-0">
                    <small>
                        <i class="fas fa-school"></i> <?= e($survey['school_name']) ?> |
                        <i class="fas fa-clipboard"></i> <?= e($survey['form_title']) ?>
                    </small>
                </p>
            </div>
            
            <div class="survey-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="surveyForm">
                    <!-- Class Selection -->
                    <?php if (!empty($available_classes)): ?>
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-users-class"></i> Sınıfınız (Opsiyonel)
                            </label>
                            <select name="class_id" class="form-select">
                                <option value="">Seçmek istemiyorum</option>
                                <?php foreach ($available_classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= e($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Gender Field (if enabled) -->
                    <?php if ($survey['gender_field_enabled']): ?>
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-venus-mars"></i> Cinsiyet (Opsiyonel)
                            </label>
                            <select name="gender" class="form-select">
                                <option value="">Belirtmek istemiyorum</option>
                                <option value="male">Erkek</option>
                                <option value="female">Kadın</option>
                                <option value="other">Diğer</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Lütfen her soru için <strong>A</strong> veya <strong>B</strong> seçeneğini işaretleyin.
                    </div>
                    
                    <!-- Questions -->
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-card">
                            <div class="d-flex align-items-start mb-3">
                                <span class="question-number"><?= $question['question_number'] ?></span>
                                <div class="flex-grow-1">
                                    <p class="mb-0" style="line-height: 1.6;">
                                        Hangisini tercih edersiniz?
                                    </p>
                                </div>
                            </div>
                            
                            <div class="options">
                                <label class="option-label">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" name="answers[<?= $question['id'] ?>]" 
                                               value="A" class="form-check-input me-3" required>
                                        <div class="flex-grow-1">
                                            <strong class="option-text">A)</strong> 
                                            <?= e($question['option_a']) ?>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="option-label">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" name="answers[<?= $question['id'] ?>]" 
                                               value="B" class="form-check-input me-3" required>
                                        <div class="flex-grow-1">
                                            <strong class="option-text">B)</strong> 
                                            <?= e($question['option_b']) ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane"></i> Anketi Gönder
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Toplam <?= count($questions) ?> soru • Tahmini süre: <?= ceil(count($questions) / 3) ?> dakika
                        </small>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-lock"></i> Yanıtlarınız gizli tutulacaktır
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (!$success): ?>
    <script>
        // Form validation before submit
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const radios = document.querySelectorAll('input[type="radio"]');
            const questions = {};
            
            radios.forEach(radio => {
                const name = radio.name;
                if (!questions[name]) {
                    questions[name] = false;
                }
                if (radio.checked) {
                    questions[name] = true;
                }
            });
            
            const allAnswered = Object.values(questions).every(answered => answered);
            
            if (!allAnswered) {
                e.preventDefault();
                alert('Lütfen tüm soruları cevaplayınız!');
                
                // Scroll to first unanswered question
                for (let name in questions) {
                    if (!questions[name]) {
                        const element = document.querySelector(`input[name="${name}"]`);
                        if (element) {
                            element.closest('.question-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
                            element.closest('.question-card').style.borderColor = '#dc3545';
                            setTimeout(() => {
                                element.closest('.question-card').style.borderColor = '#667eea';
                            }, 2000);
                            break;
                        }
                    }
                }
            }
        });
        
        // Progress indicator
        const totalQuestions = <?= count($questions) ?>;
        let answeredCount = 0;
        
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Update progress
                const questions = {};
                document.querySelectorAll('input[type="radio"]:checked').forEach(checked => {
                    questions[checked.name] = true;
                });
                answeredCount = Object.keys(questions).length;
                
                // Could show a progress bar here
                console.log(`İlerleme: ${answeredCount}/${totalQuestions}`);
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
