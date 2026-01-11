<?php
// Header layout component
if (!function_exists('render_header')) {
    function render_header($title, $active_page = '') {
        $user = get_current_user();
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                :root {
                    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                body {
                    background-color: #f8f9fa;
                }
                .navbar-custom {
                    background: var(--primary-gradient);
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .sidebar {
                    background: white;
                    min-height: calc(100vh - 56px);
                    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
                }
                .sidebar .nav-link {
                    color: #6c757d;
                    padding: 12px 20px;
                    border-left: 3px solid transparent;
                    transition: all 0.3s;
                }
                .sidebar .nav-link:hover {
                    background-color: #f8f9fa;
                    color: #667eea;
                }
                .sidebar .nav-link.active {
                    background-color: #f0f0ff;
                    color: #667eea;
                    border-left-color: #667eea;
                    font-weight: 600;
                }
                .sidebar .nav-link i {
                    width: 20px;
                    margin-right: 10px;
                }
                .card {
                    border: none;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 20px;
                }
                .card-header {
                    background: var(--primary-gradient);
                    color: white;
                    border-radius: 10px 10px 0 0 !important;
                    padding: 15px 20px;
                    font-weight: 600;
                }
                .btn-primary {
                    background: var(--primary-gradient);
                    border: none;
                }
                .btn-primary:hover {
                    opacity: 0.9;
                    transform: translateY(-1px);
                }
                .table-responsive {
                    border-radius: 10px;
                    overflow: hidden;
                }
                .badge {
                    padding: 5px 10px;
                }
            </style>
        </head>
        <body>
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
                <div class="container-fluid">
                    <a class="navbar-brand" href="<?= url('/') ?>">
                        <i class="fas fa-clipboard-check"></i> <?= e(APP_NAME) ?>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> <?= e($user['name']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= url('logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row">
        <?php
    }
}

if (!function_exists('render_sidebar')) {
    function render_sidebar($items, $active_page) {
        ?>
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <?php foreach ($items as $item): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_page === $item['page'] ? 'active' : '' ?>" 
                               href="<?= e($item['url']) ?>">
                                <i class="<?= e($item['icon']) ?>"></i>
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
        <?php
    }
}

if (!function_exists('render_footer')) {
    function render_footer() {
        ?>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                // Auto-hide alerts after 5 seconds
                setTimeout(function() {
                    document.querySelectorAll('.alert').forEach(function(alert) {
                        if (alert.querySelector('.btn-close')) {
                            alert.querySelector('.btn-close').click();
                        }
                    });
                }, 5000);
            </script>
        </body>
        </html>
        <?php
    }
}
?>
