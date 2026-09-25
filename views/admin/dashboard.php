<h1>Dashboard</h1>

<div class="stat-grid">
  <div class="stat-card"><span class="stat-num"><?= (int) ($couponStats['p'] ?? 0) ?></span><span>Coupons pending review</span></div>
  <div class="stat-card"><span class="stat-num ok"><?= (int) ($couponStats['a'] ?? 0) ?></span><span>Coupons live</span></div>
  <div class="stat-card"><span class="stat-num"><?= (int) ($referralStats['p'] ?? 0) ?></span><span>Referrals pending</span></div>
  <div class="stat-card"><span class="stat-num ok"><?= (int) ($referralStats['a'] ?? 0) ?></span><span>Referrals live</span></div>
  <div class="stat-card"><span class="stat-num grad-text"><?= (int) (($couponStats['clicks'] ?? 0) + ($referralStats['clicks'] ?? 0)) ?></span><span>Total clicks</span></div>
</div>

<div class="admin-cols">
  <section>
    <div class="section-head"><h2>Latest coupon submissions</h2><a class="see-all" href="/admin/coupons?status=pending">Review queue →</a></div>
    <table class="table table-compact">
      <tbody>
      <?php foreach ($recentCoupons as $c): ?>
        <tr>
          <td><?= e($c['store_name']) ?> — <?= e($c['title']) ?></td>
          <td><span class="chip chip-<?= $c['status']==='approved'?'ok':($c['status']==='pending'?'warn':'danger') ?>"><?= e($c['status']) ?></span></td>
          <td class="muted"><?= e(time_ago($c['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section>
    <div class="section-head"><h2>Latest referral submissions</h2><a class="see-all" href="/admin/referrals?status=pending">Review queue →</a></div>
    <table class="table table-compact">
      <tbody>
      <?php foreach ($recentReferrals as $r): ?>
        <tr>
          <td><?= e($r['program_name']) ?> — <?= e($r['title']) ?></td>
          <td><span class="chip chip-<?= $r['status']==='approved'?'ok':($r['status']==='pending'?'warn':'danger') ?>"><?= e($r['status']) ?></span></td>
          <td class="muted"><?= e(time_ago($r['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>

<section>
  <div class="section-head"><h2>Recent activity</h2><a class="see-all" href="/admin/audit">Full log →</a></div>
  <table class="table table-compact">
    <tbody>
    <?php foreach ($audit as $a): ?>
      <tr>
        <td><code><?= e($a['action']) ?></code></td>
        <td class="muted"><?= e($a['target']) ?></td>
        <td><?= e($a['user_name'] ?? 'system') ?></td>
        <td class="muted"><?= e(time_ago($a['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
