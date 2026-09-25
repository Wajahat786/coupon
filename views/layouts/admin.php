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
  <a class="brand" href="/admin">⚡ Admin</a>
  <nav>
    <a href="/admin">📊 Dashboard</a>
    <a href="/admin/coupons">🎟️ Coupons</a>
    <a href="/admin/referrals">🔗 Referral Links</a>
    <a href="/admin/users">👥 Users</a>
    <a href="/admin/settings">⚙️ Settings</a>
    <a href="/admin/audit">📜 Audit Log</a>
    <a href="/">← View Site</a>
  </nav>
  <div class="sidebar-foot">
    <span class="muted"><?= e(Auth::user()['name'] ?? '') ?></span>
    <form method="post" action="/logout"><?= csrf_field() ?>
      <button class="btn btn-outline btn-sm btn-block" type="submit">Logout</button>
    </form>
    <button id="themeToggle" class="icon-btn" type="button" aria-label="Toggle theme">
      <span class="theme-icon-dark">🌙</span><span class="theme-icon-light">☀️</span>
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
