<section class="page-head row-between">
  <h1>Referral Links</h1>
  <div class="tabs">
    <a class="tab <?= $status === null ? 'active' : '' ?>" href="/admin/referrals">All</a>
    <a class="tab <?= $status === 'pending' ? 'active' : '' ?>" href="/admin/referrals?status=pending">Pending</a>
    <a class="tab <?= $status === 'approved' ? 'active' : '' ?>" href="/admin/referrals?status=approved">Approved</a>
    <a class="tab <?= $status === 'rejected' ? 'active' : '' ?>" href="/admin/referrals?status=rejected">Rejected</a>
  </div>
</section>

<table class="table">
  <thead><tr><th>ID</th><th>Program / Title</th><th>Link</th><th>Reward</th><th>By</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($referrals as $r): $rid = (int) $r['id']; ?>
    <tr>
      <td>#<?= $rid ?></td>
      <td><strong><?= e($r['program_name']) ?></strong><br><span class="muted"><?= e($r['title']) ?></span></td>
      <td><small class="muted"><?= e(mb_substr((string) $r['link'], 0, 45)) ?>…</small></td>
      <td><?= e($r['reward_text'] ?: '—') ?></td>
      <td><?= e($r['submitted_by'] ?? 'admin') ?></td>
      <td><span class="chip chip-<?= $r['status']==='approved'?'ok':($r['status']==='pending'?'warn':'danger') ?>"><?= e($r['status']) ?></span></td>
      <td class="actions-cell">
        <?php if ($r['status'] !== 'approved'): ?>
        <form method="post" action="/admin/referrals/review" class="inline-form"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $rid ?>"><input type="hidden" name="action" value="approve">
          <button class="btn btn-ok btn-xs" type="submit"><?= icon("check", 14) ?> Approve</button>
        </form>
        <?php endif; ?>
        <?php if ($r['status'] !== 'rejected'): ?>
        <form method="post" action="/admin/referrals/review" class="inline-form"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $rid ?>"><input type="hidden" name="action" value="reject">
          <button class="btn btn-danger btn-xs" type="submit"><?= icon("x", 14) ?> Reject</button>
        </form>
        <?php endif; ?>
        <form method="post" action="/admin/referrals/delete" class="inline-form" onsubmit="return confirm('Delete referral #<?= $rid ?> permanently?');">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $rid ?>">
          <button class="btn btn-ghost btn-xs" type="submit"><?= icon("trash", 14) ?></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$referrals): ?><tr><td colspan="7" class="center muted">Nothing here.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php include BASE_PATH . '/views/partials/pagination.php'; ?>

<details class="admin-create">
  <summary class="btn btn-outline">＋ Add referral link directly (auto-approved)</summary>
  <form method="post" action="/admin/referrals/store" class="form form-wide mt">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label>Program *<input name="program_name" required maxlength="100"></label>
      <label>Category<input name="category" maxlength="50" value="General"></label>
      <label class="span-2">Title *<input name="title" required maxlength="150"></label>
      <label class="span-2">Link *<input type="url" name="link" required maxlength="500" pattern="https://.*" placeholder="https://…"></label>
      <label>Reward text<input name="reward_text" maxlength="150"></label>
      <label class="span-2">Description<textarea name="description" maxlength="2000" rows="3"></textarea></label>
    </div>
    <button class="btn btn-primary" type="submit">Create & approve</button>
  </form>
</details>
