<section class="page-head">
  <h1><?= icon("coupon", 22) ?> Submit a Coupon</h1>
  <p class="muted">Every submission is reviewed by an admin before going live.</p>
</section>

<form method="post" action="/submit-coupon" class="form form-wide" novalidate>
  <?= csrf_field() ?>
  <div class="form-grid">
    <label>Store name *
      <input type="text" name="store_name" required maxlength="100" placeholder="e.g. Amazon">
    </label>
    <label>Category
      <input type="text" name="category" maxlength="50" list="cat-list" placeholder="e.g. Fashion">
      <datalist id="cat-list"><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist>
    </label>
    <label class="span-2">Deal title *
      <input type="text" name="title" required maxlength="150" placeholder="e.g. 25% off all electronics">
    </label>
    <label>Coupon code *
      <input type="text" name="code" required maxlength="32" placeholder="SAVE25" style="text-transform:uppercase">
    </label>
    <label>Discount type
      <select name="discount_type">
        <option value="percent">Percent off</option>
        <option value="fixed">Fixed amount</option>
        <option value="shipping">Free shipping</option>
      </select>
    </label>
    <label>Discount value
      <input type="number" name="discount_value" min="0" max="1000000" step="0.01" placeholder="25">
    </label>
    <label>Expires on (optional)
      <input type="date" name="expires_at" min="<?= date('Y-m-d') ?>">
    </label>
    <label class="span-2">Store / deal link (https only, optional)
      <input type="url" name="link" maxlength="500" placeholder="https://store.com/d/eal" pattern="https://.*">
    </label>
    <label class="span-2">Description (optional)
      <textarea name="description" maxlength="2000" rows="4" placeholder="Any terms, minimum spend, exclusions…"></textarea>
    </label>
  </div>
  <button class="btn btn-primary btn-lg" type="submit">Submit for review</button>
</form>
