<section class="page-head">
  <h1>Referral Links <small>(<?= (int) $total ?>)</small></h1>
  <form class="filter-bar" method="get" action="/referrals">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search program…" maxlength="80">
    <select name="cat" aria-label="Category">
      <option value="">All categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>><?= e($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit">Apply</button>
  </form>
</section>

<?php if ($referrals): ?>
  <div class="grid">
    <?php foreach ($referrals as $r): include BASE_PATH . '/views/partials/referral_card.php'; endforeach; ?>
  </div>
  <?php include BASE_PATH . '/views/partials/pagination.php'; ?>
<?php else: ?>
  <div class="empty">No referral links yet — <a href="/submit-referral">share yours</a> and earn rewards!</div>
<?php endif; ?>
