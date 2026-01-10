<?php
declare(strict_types=1);

$title = $title ?? 'RIBA Survey System';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <header class="topbar">
    <div class="container">
      <a class="brand" href="/dashboard">RIBA Survey</a>
      <nav>
        <?php if ($user): ?>
          <a href="/surveys">Surveys</a>
          <?php if (has_role('admin')): ?>
            <a href="/surveys/create">Create survey</a>
          <?php endif; ?>
          <form method="post" action="/logout" class="inline">
            <?= csrf_field() ?>
            <button type="submit">Logout</button>
          </form>
        <?php else: ?>
          <a href="/login">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container">
    <?= $content ?>
  </main>

  <script src="/assets/js/app.js"></script>
</body>
</html>
