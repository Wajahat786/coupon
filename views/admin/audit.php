<h1>Audit Log <small class="muted">(last 200 events)</small></h1>
<table class="table table-compact">
  <thead><tr><th>#</th><th>When</th><th>User</th><th>Action</th><th>Target</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($logs as $l): ?>
    <tr>
      <td class="muted"><?= (int) $l['id'] ?></td>
      <td class="muted"><?= e(date('Y-m-d H:i', strtotime($l['created_at']))) ?></td>
      <td><?= e($l['user_name'] ?? 'system') ?></td>
      <td><code><?= e($l['action']) ?></code></td>
      <td class="muted"><?= e($l['target']) ?></td>
      <td class="muted"><?= e($l['ip']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$logs): ?><tr><td colspan="6" class="center muted">No activity yet.</td></tr><?php endif; ?>
  </tbody>
</table>
