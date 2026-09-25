<?php [$expText, $expClass] = expires_label($c['expires_at'] ?? null); ?>
<article class="card coupon-card">
  <div class="card-top">
    <span class="store-badge"><?= e($c['store_name']) ?></span>
    <span class="chip chip-<?= e($c['discount_type']) ?>">
      <?php if ($c['discount_type'] === 'percent'): ?><?= (int) $c['discount_value'] ?>% OFF
      <?php elseif ($c['discount_type'] === 'fixed'): ?>$<?= e(number_format((float)$c['discount_value'], 2)) ?> OFF
      <?php else: ?>Free Shipping<?php endif; ?>
    </span>
  </div>
  <h3 class="card-title"><a href="/coupon/<?= (int) $c['id'] ?>"><?= e($c['title']) ?></a></h3>
  <?php if ($expText): ?><span class="expiry <?= e($expClass) ?>"><?= e($expText) ?></span><?php endif; ?>
  <div class="code-row">
    <code class="deal-code" id="code-<?= (int) $c['id'] ?>"><?= e($c['code']) ?></code>
    <button class="btn btn-copy btn-sm" type="button" data-copy="<?= e($c['code']) ?>">Copy</button>
  </div>
  <div class="card-foot">
    <a class="btn btn-primary btn-sm" href="/go/coupon/<?= (int) $c['id'] ?>" target="_blank" rel="noopener nofollow ugc">Get Deal <?= icon("arrow-right", 14) ?></a>
    <span class="muted"><?= (int) $c['clicks'] ?> clicks</span>
  </div>
</article>
