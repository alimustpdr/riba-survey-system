<?php
declare(strict_types=1);
?>
<h1>Create survey</h1>

<?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="/surveys/create" class="card">
  <?= csrf_field() ?>

  <label>
    Title
    <input type="text" name="title" required>
  </label>

  <label>
    Questions (one per line)
    <textarea name="questions" rows="8" required></textarea>
  </label>

  <button type="submit">Create</button>
</form>
