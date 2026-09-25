<section class="page-head">
  <h1>🔗 Share a Referral Link</h1>
  <p class="muted">Paste your referral link — approved links get shown to thousands of bargain hunters.</p>
</section>

<form method="post" action="/submit-referral" class="form form-wide" novalidate>
  <?= csrf_field() ?>
  <div class="form-grid">
    <label>Program name *
      <input type="text" name="program_name" required maxlength="100" placeholder="e.g. Dropbox">
    </label>
    <label>Category
      <input type="text" name="category" maxlength="50" list="cat-list" placeholder="e.g. Software">
      <datalist id="cat-list"><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist>
    </label>
    <label class="span-2">Title *
      <input type="text" name="title" required maxlength="150" placeholder="e.g. Get 500MB free space per friend">
    </label>
    <label class="span-2">Your referral link * (https only)
      <input type="url" name="link" required maxlength="500" placeholder="https://dropbox.com/ref/xYz123" pattern="https://.*">
    </label>
    <label>Reward text (optional)
      <input type="text" name="reward_text" maxlength="150" placeholder="e.g. $10 for you + $10 for me">
    </label>
    <label class="span-2">How it works (optional)
      <textarea name="description" maxlength="2000" rows="4" placeholder="Steps to claim the reward…"></textarea>
    </label>
  </div>
  <button class="btn btn-primary btn-lg" type="submit">Submit for review</button>
</form>
