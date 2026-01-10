<?php
declare(strict_types=1);
?>
<h1>Answer survey</h1>

<h2><?= htmlspecialchars((string)($survey['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($questions)): ?>
  <p>This survey has no questions yet.</p>
<?php else: ?>
  <form method="post" action="/surveys/<?= (int)$survey['id'] ?>/answer" class="card">
    <?= csrf_field() ?>

    <?php foreach ($questions as $q): ?>
      <div class="question">
        <div class="question-text">
          <?= htmlspecialchars((string)$q['question_text'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <input type="text" name="answers[<?= (int)$q['id'] ?>]" placeholder="Your answer">
      </div>
    <?php endforeach; ?>

    <button type="submit">Submit</button>
  </form>
<?php endif; ?>
