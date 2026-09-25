<?php use App\Core\Auth; ?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e(setting('default_theme', 'dark')) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? setting('site_name', 'DealHub')) ?></title>
<meta name="description" content="<?= e(setting('tagline', 'Hand-approved coupons & referral links')) ?>">
<link href="/assets/css/style.css" rel="stylesheet">
<script src="/assets/js/theme.js"></script>
</head>
<body>
<header class="site-header">
  <div class="container nav-row">
    <a class="brand" href="/"><?= icon("flame", 20) ?> <span><?= e(setting('site_name', 'DealHub')) ?></span></a>
    <nav class="main-nav">
      <a href="/coupons">Coupons</a>
      <a href="/referrals">Referral Links</a>
      <?php if (Auth::check()): ?>
        <a href="/dashboard">My Posts</a>
      <?php else: ?>
        <a href="/submit-coupon">Submit Deal</a>
      <?php endif; ?>
    </nav>
    <div class="nav-actions">
      <button id="themeToggle" class="icon-btn" type="button" aria-label="Toggle light/dark theme" title="Toggle theme">
        <span class="theme-icon-dark"><?= icon("moon", 17) ?></span><span class="theme-icon-light"><?= icon("sun", 17) ?></span>
      </button>
      <?php if ($u = Auth::user()): ?>
        <?php if ($u['role'] === 'admin'): ?><a class="btn btn-ghost btn-sm" href="/admin">Admin</a><?php endif; ?>
        <form method="post" action="/logout" class="inline-form"><?= csrf_field() ?>
          <button class="btn btn-outline btn-sm" type="submit">Logout (<?= e(mb_substr($u['name'], 0, 12)) ?>)</button>
        </form>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" href="/login">Sign in</a>
        <a class="btn btn-primary btn-sm" href="/register">Join Free</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="container">
<?php if ($msg = flash_message()): ?>
  <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['text']) ?></div>
<?php endif; ?>

<?= $content ?>

</main>

<footer class="site-footer">
  <div class="container">
    <p>© <?= date('Y') ?> <?= e(setting('site_name', 'DealHub')) ?> — All deals are community-submitted and admin-approved. We are not affiliated with listed stores.</p>
    <p class="muted"><a href="/coupons">Coupons</a> · <a href="/referrals">Referrals</a> · <a href="/submit-coupon">Submit a deal</a></p>
  </div>
</footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
