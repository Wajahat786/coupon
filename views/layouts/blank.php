<!DOCTYPE html>
<html lang="en" data-theme="<?= e(setting('default_theme', 'dark')) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title><?= e($pageTitle ?? 'DealHub') ?></title>
<link href="/assets/css/style.css" rel="stylesheet">
<script src="/assets/js/theme.js"></script>
</head>
<body class="centered-page">
<div class="auth-card">
  <a class="brand brand-lg" href="/">🔥 <?= e(setting('site_name', 'DealHub')) ?></a>
  <?php if ($msg = flash_message()): ?>
    <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['text']) ?></div>
  <?php endif; ?>
  <?= $content ?>
</div>
<button id="themeToggle" class="theme-fab icon-btn" type="button" aria-label="Toggle theme">
  <span class="theme-icon-dark">🌙</span><span class="theme-icon-light">☀️</span>
</button>
<script src="/assets/js/app.js"></script>
</body>
</html>
