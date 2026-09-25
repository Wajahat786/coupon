<section class="hero">
  <h1>Save Big with <span class="grad">Hand-Approved</span> Coupons & Referral Links</h1>
  <p class="lead"><?= e(setting('tagline', 'Every code is tested & approved by our team — no dead coupons, no spam.')) ?></p>
  <div class="hero-actions">
    <a class="btn btn-primary btn-lg" href="/coupons">Browse Coupons</a>
    <a class="btn btn-outline btn-lg" href="/referrals">Referral Deals</a>
  </div>
  <form class="hero-search" method="get" action="/coupons">
    <input type="search" name="q" placeholder="Search store or code… (e.g. Amazon, 50% OFF)" maxlength="80" aria-label="Search coupons">
    <button class="btn btn-primary" type="submit">Search</button>
  </form>
</section>

<?php if ($coupons): ?>
<section class="section">
  <div class="section-head"><h2><?= icon("flame", 20) ?> Latest Coupons</h2><a class="see-all" href="/coupons">See all <?= icon("arrow-right", 14) ?></a></div>
  <div class="grid">
    <?php foreach ($coupons as $c): include BASE_PATH . '/views/partials/coupon_card.php'; endforeach; ?>
  </div>
</section>
<?php else: ?>
  <div class="empty">No approved coupons yet — be the first to <a href="/submit-coupon">submit one</a>!</div>
<?php endif; ?>

<?php if ($referrals): ?>
<section class="section">
  <div class="section-head"><h2><?= icon("share", 20) ?> Popular Referral Links</h2><a class="see-all" href="/referrals">See all <?= icon("arrow-right", 14) ?></a></div>
  <div class="grid">
    <?php foreach ($referrals as $r): include BASE_PATH . '/views/partials/referral_card.php'; endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="how-it-works">
  <h2>How it works</h2>
  <div class="steps">
    <div class="step"><span>1</span><h3>Submit</h3><p>Share a coupon code or your referral link in seconds.</p></div>
    <div class="step"><span>2</span><h3>We Verify</h3><p>Our admin team tests every deal before approving it.</p></div>
    <div class="step"><span>3</span><h3>You Save</h3><p>Copy the code or share links and earn rewards.</p></div>
  </div>
</section>
