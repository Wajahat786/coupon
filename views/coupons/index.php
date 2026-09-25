<section class="page-head">
  <h1>All Coupons <small>(<?= (int) $total ?>)</small></h1>
  <form class="filter-bar" method="get" action="/coupons">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search…" maxlength="80">
    <select name="cat" aria-label="Category">
      <option value="">All categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>><?= e($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="sort" aria-label="Sort">
      <option value="newest"  <?= $sort === 'newest'  ? 'selected' : '' ?>>Newest</option>
      <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most popular</option>
      <option value="expiry"  <?= $sort === 'expiry'  ? 'selected' : '' ?>>Expiring soon</option>
    </select>
    <button class="btn btn-primary" type="submit">Apply</button>
  </form>
</section>

<?php if ($coupons): ?>
  <div class="grid">
    <?php foreach ($coupons as $c): include BASE_PATH . '/views/partials/coupon_card.php'; endforeach; ?>
  </div>
  <?php include BASE_PATH . '/views/partials/pagination.php'; ?>
<?php else: ?>
  <div class="empty">No coupons matched. Try a different search or <a href="/submit-coupon">submit one</a>.</div>
<?php endif; ?>
