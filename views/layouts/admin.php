<?php use App\Core\Auth; ?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e(setting('default_theme', 'dark')) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= e($pageTitle ?? 'Admin') ?> · <?= e(setting('site_name', 'DealHub')) ?></title>
<link href="/assets/css/style.css" rel="stylesheet">
<script src="/assets/js/theme.js"></script>
</head>
<body class="admin-body">
<aside class="admin-sidebar">
  <a class="brand" href="/admin"><?= icon("zap", 18) ?> Admin</a>
  <nav>
    <a href="/admin"><?= icon("chart", 17) ?> Dashboard</a>
    <a href="/admin/coupons"><?= icon("coupon", 17) ?> Coupons</a>
    <a href="/admin/referrals"><?= icon("link", 17) ?> Referral Links</a>
    <a href="/admin/users"><?= icon("users", 17) ?> Users</a>
    <a href="/admin/settings"><?= icon("settings", 17) ?> Settings</a>
    <a href="/admin/audit"><?= icon("history", 17) ?> Audit Log</a>
    <a href="/"><?= icon("external", 17) ?> View Site</a>
  </nav>
  <div class="sidebar-foot">
    <span class="muted"><?= e(Auth::user()['name'] ?? '') ?></span>
    <form method="post" action="/logout"><?= csrf_field() ?>
      <button class="btn btn-outline btn-sm btn-block" type="submit">Logout</button>
    </form>
    <button id="themeToggle" class="icon-btn" type="button" aria-label="Toggle theme">
      <span class="theme-icon-dark"><?= icon("moon", 17) ?></span><span class="theme-icon-light"><?= icon("sun", 17) ?></span>
    </button>
  </div>
</aside>
<div class="admin-main">
  <?php if ($msg = flash_message()): ?>
    <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['text']) ?></div>
  <?php endif; ?>
  <?= $content ?>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
