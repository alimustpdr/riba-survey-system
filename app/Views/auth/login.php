<?php
declare(strict_types=1);
?>
<h1>Login</h1>

<?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="/login" class="card">
  <?= csrf_field() ?>

  <label>
    Email
    <input type="email" name="email" required>
  </label>

  <label>
    Password
    <input type="password" name="password" required>
  </label>

  <button type="submit">Sign in</button>
</form>
