<?php
declare(strict_types=1);
?>
<h1>Dashboard</h1>

<p>Welcome, <strong><?= htmlspecialchars((string)($user['name'] ?? 'User'), ENT_QUOTES, 'UTF-8') ?></strong>.</p>

<ul>
  <li><a href="/surveys">View surveys</a></li>
  <?php if (has_role('admin')): ?>
    <li><a href="/surveys/create">Create a survey</a></li>
  <?php endif; ?>
</ul>
