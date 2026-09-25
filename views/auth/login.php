<h1>Welcome back 👋</h1>
<p class="muted">Sign in to manage your submissions.</p>
<form method="post" action="/login" class="form" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e(safe_next($_GET['next'] ?? null, '/')) ?>">
  <label>Email
    <input type="email" name="email" required maxlength="190" autocomplete="email" autofocus>
  </label>
  <label>Password
    <input type="password" name="password" required minlength="10" autocomplete="current-password">
  </label>
  <button class="btn btn-primary btn-block" type="submit">Sign in</button>
</form>
<p class="muted center">No account? <a href="/register">Create one free</a></p>
