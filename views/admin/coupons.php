<?php use App\Core\Database; ?>
<section class="page-head row-between">
  <h1>Coupons</h1>
  <div class="tabs">
    <a class="tab <?= $status === null ? 'active' : '' ?>" href="/admin/coupons">All</a>
    <a class="tab <?= $status === 'pending' ? 'active' : '' ?>" href="/admin/coupons?status=pending">Pending</a>
    <a class="tab <?= $status === 'approved' ? 'active' : '' ?>" href="/admin/coupons?status=approved">Approved</a>
    <a class="tab <?= $status === 'rejected' ? 'active' : '' ?>" href="/admin/coupons?status=rejected">Rejected</a>
  </div>
</section>

<table class="table">
  <thead><tr><th>ID</th><th>Store / Title</th><th>Code</th><th>Discount</th><th>Expires</th><th>By</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($coupons as $c): $uid = (int) $c['id']; ?>
    <tr>
      <td>#<?= $uid ?></td>
      <td>
        <strong><?= e($c['store_name']) ?></strong><br>
        <span class="muted"><?= e($c['title']) ?></span>
        <?php if (!empty($c['link'])): ?><br><small class="muted">🔗 <?= e(mb_substr($c['link'], 0, 40)) ?></small><?php endif; ?>
      </td>
      <td><code class="deal-code deal-code-sm"><?= e($c['code']) ?></code></td>
      <td><?= e($c['discount_type']) ?> <?= e((string) $c['discount_value']) ?></td>
      <td class="muted"><?= $c['expires_at'] ? e(date('M j, Y', strtotime($c['expires_at']))) : '—' ?></td>
      <td><?= e($c['submitted_by'] ?? 'admin') ?></td>
      <td><span class="chip chip-<?= $c['status']==='approved'?'ok':($c['status']==='pending'?'warn':'danger') ?>"><?= e($c['status']) ?></span></td>
      <td class="actions-cell">
        <?php if ($c['status'] !== 'approved'): ?>
        <form method="post" action="/admin/coupons/review" class="inline-form"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $uid ?>"><input type="hidden" name="action" value="approve">
          <button class="btn btn-ok btn-xs" type="submit">✔ Approve</button>
        </form>
        <?php endif; ?>
        <?php if ($c['status'] !== 'rejected'): ?>
        <form method="post" action="/admin/coupons/review" class="inline-form"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $uid ?>"><input type="hidden" name="action" value="reject">
          <button class="btn btn-danger btn-xs" type="submit">✖ Reject</button>
        </form>
        <?php endif; ?>
        <form method="post" action="/admin/coupons/delete" class="inline-form" onsubmit="return confirm('Delete coupon #<?= $uid ?> permanently?');">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $uid ?>">
          <button class="btn btn-ghost btn-xs" type="submit">🗑</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$coupons): ?><tr><td colspan="8" class="center muted">Nothing here.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php include BASE_PATH . '/views/partials/pagination.php'; ?>

<details class="admin-create">
  <summary class="btn btn-outline">＋ Add coupon directly (auto-approved)</summary>
  <form method="post" action="/admin/coupons/store" class="form form-wide mt">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label>Store *<input name="store_name" required maxlength="100"></label>
      <label>Category<input name="category" maxlength="50" value="General"></label>
      <label class="span-2">Title *<input name="title" required maxlength="150"></label>
      <label>Code *<input name="code" required maxlength="32" style="text-transform:uppercase"></label>
      <label>Type<select name="discount_type"><option value="percent">Percent</option><option value="fixed">Fixed</option><option value="shipping">Shipping</option></select></label>
      <label>Value<input type="number" name="discount_value" min="0" step="0.01"></label>
      <label>Expires<input type="date" name="expires_at" min="<?= date('Y-m-d') ?>"></label>
      <label class="span-2">Link<input type="url" name="link" maxlength="500" pattern="https://.*" placeholder="https://…"></label>
      <label class="span-2">Description<textarea name="description" maxlength="2000" rows="3"></textarea></label>
    </div>
    <button class="btn btn-primary" type="submit">Create & approve</button>
  </form>
</details>
