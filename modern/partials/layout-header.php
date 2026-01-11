<?php
/** @var string $page_title */
/** @var array|null $user */
/** @var string|null $active_nav */
$page_title = $page_title ?? 'Modern Panel';
$active_nav = $active_nav ?? null;
$user = $user ?? null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - RİBA</title>
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
               color: #e5e7eb; min-height: 100vh; }
        .shell { max-width: 1200px; }
        .topbar { backdrop-filter: blur(8px); background: rgba(2,6,23,0.55); border: 1px solid rgba(255,255,255,0.08); }
        .brand-badge { background: linear-gradient(135deg, var(--accent1) 0%, var(--accent2) 100%); }
        .navpills .btn { border-color: rgba(255,255,255,0.14); color: rgba(229,231,235,0.90); }
        .navpills .btn:hover { border-color: rgba(255,255,255,0.30); color: #fff; }
        .navpills .btn.active { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.30); }
        .cardx { background: var(--card); border: 1px solid var(--card-border); border-radius: 16px; }
        .muted { color: var(--muted); }
        .kpi { font-size: 1.6rem; font-weight: 700; letter-spacing: -0.02em; }
        .btn-grad { background: linear-gradient(135deg, var(--accent1) 0%, var(--accent2) 100%); border: none; }
        .btn-grad:hover { filter: brightness(1.05); }
        a { color: #a5b4fc; }
        .table thead th { color: rgba(229,231,235,0.85); }
        .table td, .table th { border-color: rgba(255,255,255,0.08); }
        .form-select, .form-control { background-color: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.12); color: #e5e7eb; }
        .form-select:focus, .form-control:focus { border-color: rgba(99,102,241,0.65); box-shadow: 0 0 0 .25rem rgba(99,102,241,0.15); }
        .form-select option { background: #0b1220; color: #e5e7eb; }
        @media (max-width: 576px) {
            .kpi { font-size: 1.35rem; }
        }
    </style>
</head>
<body>
<div class="container shell py-4">
    <div class="topbar rounded-4 p-3 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge brand-badge rounded-pill px-3 py-2"><i class="fas fa-clipboard-check"></i> RİBA</span>
                <span class="muted small">Modern UI</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <span class="muted small d-none d-sm-inline">
                        <i class="fas fa-user"></i> <?= e($user['email'] ?? $user['name'] ?? '') ?>
                    </span>
                    <a class="btn btn-outline-light btn-sm" href="/logout.php">
                        <i class="fas fa-right-from-bracket"></i> Çıkış
                    </a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm" href="/login.php">Giriş</a>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($user): ?>
            <div class="mt-3 d-flex flex-wrap gap-2 navpills">
                <?php if (($user['role'] ?? '') === 'school_admin'): ?>
                    <a class="btn btn-sm <?= $active_nav === 'dashboard' ? 'active' : '' ?>" href="/modern/school/dashboard.php">
                        <i class="fas fa-gauge-high"></i> Dashboard
                    </a>
                    <a class="btn btn-sm <?= $active_nav === 'reports' ? 'active' : '' ?>" href="/modern/school/reports/overview.php">
                        <i class="fas fa-chart-column"></i> Raporlar
                    </a>
                    <a class="btn btn-sm <?= $active_nav === 'links' ? 'active' : '' ?>" href="/modern/school/smart-links.php">
                        <i class="fas fa-link"></i> Akıllı Linkler
                    </a>
                    <a class="btn btn-sm" href="/school/index.php">
                        <i class="fas fa-arrow-up-right-from-square"></i> Klasik Panel
                    </a>
                <?php elseif (($user['role'] ?? '') === 'super_admin'): ?>
                    <a class="btn btn-sm <?= $active_nav === 'dashboard' ? 'active' : '' ?>" href="/modern/admin/dashboard.php">
                        <i class="fas fa-gauge-high"></i> Dashboard
                    </a>
                    <a class="btn btn-sm" href="/admin/index.php">
                        <i class="fas fa-arrow-up-right-from-square"></i> Klasik Admin
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

