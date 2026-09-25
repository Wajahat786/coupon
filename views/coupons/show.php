<?php
use App\Core\Security;
[$expText, $expClass] = expires_label($coupon['expires_at'] ?? null);
?>
<article class="detail">
  <a class="back-link" href="/coupons"><?= icon("arrow-left", 15) ?> Back to coupons</a>
  <div class="detail-head">
    <span class="store-badge"><?= e($coupon['store_name']) ?></span>
    <h1><?= e($coupon['title']) ?></h1>
    <p class="muted">Posted <?= e(time_ago($coupon['created_at'])) ?> by <?= e($coupon['submitted_by'] ?? 'DealHub team') ?>
       · <?= (int) $coupon['clicks'] ?> clicks · <?= e($coupon['category']) ?></p>
    <?php if ($expText): ?><p class="expiry <?= e($expClass) ?>"><?= e($expText) ?></p><?php endif; ?>
  </div>

  <div class="reveal-box">
    <div class="code-row code-row-lg">
      <code class="deal-code" id="main-code"><?= e($coupon['code']) ?></code>
      <button class="btn btn-copy" type="button" data-copy="<?= e($coupon['code']) ?>"><?= icon("copy", 16) ?> Copy Code</button>
    </div>
    <?php if (!empty($coupon['link'])): ?>
      <a class="btn btn-primary btn-lg" href="/go/coupon/<?= (int) $coupon['id'] ?>" target="_blank" rel="noopener nofollow ugc">
        Activate Deal at <?= e($coupon['store_name']) ?> <?= icon("external", 16) ?>
      </a>
    <?php endif; ?>
  </div>

  <?php if (!empty($coupon['description'])): ?>
    <div class="detail-desc"><h2>About this deal</h2><p><?= nl2br(e($coupon['description'])) ?></p></div>
  <?php endif; ?>

  <p class="fineprint muted">Tip: paste the code at checkout on the store's website. Deals are verified at approval time and may expire early.</p>
</article>
