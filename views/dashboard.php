<?php $statusChip = ['pending'=>'warn','approved'=>'ok','rejected'=>'danger']; ?>
<section class="page-head row-between">
  <h1>My Submissions</h1>
  <div>
    <a class="btn btn-outline btn-sm" href="/submit-coupon">+ Coupon</a>
    <a class="btn btn-outline btn-sm" href="/submit-referral">+ Referral</a>
  </div>
</section>

<h2 class="mt">Coupons</h2>
<?php if ($coupons): ?>
<table class="table">
  <thead><tr><th>Store</th><th>Title</th><th>Code</th><th>Status</th><th>Clicks</th><th>Posted</th></tr></thead>
  <tbody>
  <?php foreach ($coupons as $c): ?>
    <tr>
      <td><?= e($c['store_name']) ?></td>
      <td><?php if ($c['status']==='approved'): ?><a href="/coupon/<?= (int)$c['id'] ?>"><?= e($c['title']) ?></a><?php else: ?><?= e($c['title']) ?><?php endif; ?></td>
      <td><code class="deal-code deal-code-sm"><?= e($c['code']) ?></code></td>
      <td><span class="chip chip-<?= e($statusChip[$c['status']] ?? '') ?>"><?= e($c['status']) ?></span></td>
      <td><?= (int) $c['clicks'] ?></td>
      <td class="muted"><?= e(time_ago($c['created_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?><div class="empty">No coupons yet.</div><?php endif; ?>

<h2 class="mt">Referral links</h2>
<?php if ($referrals): ?>
<table class="table">
  <thead><tr><th>Program</th><th>Title</th><th>Status</th><th>Clicks</th><th>Posted</th></tr></thead>
  <tbody>
  <?php foreach ($referrals as $r): ?>
    <tr>
      <td><?= e($r['program_name']) ?></td>
      <td><?php if ($r['status']==='approved'): ?><a href="/referral/<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a><?php else: ?><?= e($r['title']) ?><?php endif; ?></td>
      <td><span class="chip chip-<?= e($statusChip[$r['status']] ?? '') ?>"><?= e($r['status']) ?></span></td>
      <td><?= (int) $r['clicks'] ?></td>
      <td class="muted"><?= e(time_ago($r['created_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?><div class="empty">No referral links yet.</div><?php endif; ?>
