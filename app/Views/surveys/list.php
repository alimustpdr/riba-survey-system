<?php
declare(strict_types=1);
?>
<h1>Surveys</h1>

<?php if (empty($surveys)): ?>
  <p>No surveys found.</p>
<?php else: ?>
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($surveys as $s): ?>
        <tr>
          <td><?= htmlspecialchars((string)$s['title'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string)$s['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><a href="/surveys/<?= (int)$s['id'] ?>/answer">Answer</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
