<h1>Create your account</h1>
<p class="muted">Join to submit coupons & share referral links.</p>
<form method="post" action="/register" class="form" novalidate>
  <?= csrf_field() ?>
  <label>Name
    <input type="text" name="name" required minlength="2" maxlength="100" autocomplete="name" autofocus>
  </label>
  <label>Email
    <input type="email" name="email" required maxlength="190" autocomplete="email">
  </label>
  <label>Password <small class="muted">(min 10 characters)</small>
    <input type="password" name="password" required minlength="10" autocomplete="new-password">
  </label>
  <label>Confirm password
    <input type="password" name="password_confirm" required minlength="10" autocomplete="new-password">
  </label>
  <button class="btn btn-primary btn-block" type="submit">Create account</button>
</form>
<p class="muted center">Already registered? <a href="/login">Sign in</a></p>
