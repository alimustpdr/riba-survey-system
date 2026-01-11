<?php
require_once 'includes/auth.php';

// Modern UI varsayılan olsun (additive).
// Eski ana sayfayı görmek için: /index.php?classic=1
if (!isset($_GET['classic']) && is_dir(__DIR__ . '/modern')) {
    header('Location: /modern/');
    exit;
}

// Giriş yaptıysa role göre yönlendir
if (is_logged_in()) {
    if ($_SESSION['user_role'] === 'super_admin') {
        header('Location: admin/index.php');
        exit;
    }
    if ($_SESSION['user_role'] === 'school_admin') {
        header('Location: school/index.php');
        exit;
    }
    logout();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RİBA Anket Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0b1220; color: #e5e7eb; }
        .hero {
            background: radial-gradient(1000px 600px at 10% 10%, rgba(99,102,241,0.25), transparent 60%),
                        radial-gradient(900px 500px at 90% 20%, rgba(16,185,129,0.20), transparent 60%),
                        linear-gradient(180deg, #0b1220 0%, #0b1220 60%, #0f172a 100%);
            padding: 70px 0;
        }
        .cardx { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10); border-radius: 16px; }
        .muted { color: rgba(229,231,235,0.75); }
        .btn-grad {
            background: linear-gradient(135deg, #6366f1 0%, #22c55e 100%);
            border: none;
        }
        .btn-grad:hover { filter: brightness(1.05); }
        a { color: #a5b4fc; }
        .badge-soft { background: rgba(34,197,94,0.15); color: #86efac; border: 1px solid rgba(34,197,94,0.25); }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-clipboard-check fa-lg"></i>
                    <strong>RİBA</strong>
                    <span class="badge badge-soft">MVP</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="/login.php" class="btn btn-outline-light btn-sm">Giriş</a>
                    <a href="/register.php" class="btn btn-grad btn-sm"><i class="fas fa-rocket"></i> Üyelik Oluştur</a>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-6 fw-bold mb-3">RİBA Anket Sürecinizi Uçtan Uca Yönetin</h1>
                    <p class="muted lead mb-4">
                        Rehberlik İhtiyacı Belirleme Anketleri (RİBA) için sınıf bazlı link oluşturma, veri toplama ve
                        sınıf/okul sonuç çizelgeleri üretme sürecini tek panelde toplayın.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/register.php" class="btn btn-grad btn-lg">
                            <i class="fas fa-user-plus"></i> Hemen Başla
                        </a>
                        <a href="/login.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-right-to-bracket"></i> Giriş Yap
                        </a>
                    </div>
                    <div class="mt-4 muted">
                        <small>
                            Not: Form soruları sabittir. Seçtiğiniz kademeye uygun formlar ile çalışırsınız.
                        </small>
                    </div>
                </div>
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="cardx p-4">
                        <h5 class="mb-3"><i class="fas fa-diagram-project"></i> İş Akışı</h5>
                        <ol class="mb-0 muted">
                            <li>Okul üyeliği oluştur</li>
                            <li>Kademeni seç, sınıfları tanımla</li>
                            <li>Sınıf bazlı link üret ve paylaş</li>
                            <li>Sınıf sonuçlarını ve okul sonuçlarını üret</li>
                            <li>Excel çıktısını indir (RAM’a gönderim için)</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row mt-5 g-3">
                <div class="col-md-4">
                    <div class="cardx p-4 h-100">
                        <h6 class="mb-2"><i class="fas fa-lock"></i> Güvenli</h6>
                        <div class="muted">CSRF, prepared statements ve güvenli oturum ayarları.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cardx p-4 h-100">
                        <h6 class="mb-2"><i class="fas fa-link"></i> Sınıf Bazlı</h6>
                        <div class="muted">Her sınıf için ayrı link, doğru sınıfa doğru veri.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cardx p-4 h-100">
                        <h6 class="mb-2"><i class="fas fa-file-excel"></i> Kurumsal Çıktı</h6>
                        <div class="muted">Resmi şablon Excel çıktısı ile raporlama.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="text-center muted">
            <small>© <?= date('Y') ?> RİBA Anket Yönetim Sistemi</small>
        </div>
    </div>
</body>
</html>

