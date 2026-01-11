<?php
$page_title = 'Anketler';
require_once 'header.php';

// Anketleri listele
$stmt = $pdo->prepare("
    SELECT s.*, ft.title as form_title, ft.kademe, ft.role
    FROM surveys s
    JOIN form_templates ft ON s.form_template_id = ft.id
    WHERE s.school_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$user['school_id']]);
$surveys = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Anketlerim</h5>
                <a href="/school/survey-create.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Yeni Anket Oluştur
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($surveys)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Henüz anket oluşturmadınız. 
                        <a href="/school/survey-create.php" class="alert-link">İlk anketinizi oluşturun</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Form Türü</th>
                                    <th>Durum</th>
                                    <th>Yanıt Sayısı</th>
                                    <th>Oluşturulma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surveys as $survey): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($survey['title']) ?></strong>
                                            <?php if ($survey['description']): ?>
                                                <br><small class="text-muted"><?= e($survey['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= e($survey['kademe']) ?></span>
                                            <span class="badge bg-info"><?= e($survey['role']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($survey['status'] === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kapalı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= $survey['response_count'] ?> yanıt</span>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($survey['created_at'])) ?></td>
                                        <td>
                                            <?php if ($survey['status'] === 'active'): ?>
                                                <button class="btn btn-sm btn-success" onclick="copyLink('<?= e($survey['link_token']) ?>')">
                                                    <i class="fas fa-copy"></i> Link
                                                </button>
                                                <a href="/survey/fill.php?token=<?= e($survey['link_token']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
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

<!-- Link modal -->
<div class="modal fade" id="linkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anket Linki</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bu linki katılımcılarla paylaşın:</p>
                <div class="input-group">
                    <input type="text" class="form-control" id="surveyLink" readonly>
                    <button class="btn btn-primary" onclick="copyToClipboard()">
                        <i class="fas fa-copy"></i> Kopyala
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyLink(token) {
    const url = window.location.origin + '/survey/fill.php?token=' + token;
    document.getElementById('surveyLink').value = url;
    new bootstrap.Modal(document.getElementById('linkModal')).show();
}

function copyToClipboard() {
    const linkInput = document.getElementById('surveyLink');
    linkInput.select();
    
    // Modern Clipboard API ile kopyalama
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(linkInput.value).then(() => {
            alert('Link kopyalandı!');
        }).catch(() => {
            // Fallback to execCommand
            document.execCommand('copy');
            alert('Link kopyalandı!');
        });
    } else {
        // Fallback for older browsers
        document.execCommand('copy');
        alert('Link kopyalandı!');
    }
}
</script>

<?php require_once 'footer.php'; ?>
