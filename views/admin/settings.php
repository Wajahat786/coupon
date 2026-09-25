<h1>Site Settings</h1>
<form method="post" action="/admin/settings" class="form form-wide">
  <?= csrf_field() ?>
  <label>Site name
    <input type="text" name="site_name" maxlength="60" value="<?= e($settings['site_name'] ?? 'DealHub') ?>" required>
  </label>
  <label>Tagline (shown in hero & meta description)
    <input type="text" name="tagline" maxlength="200" value="<?= e($settings['tagline'] ?? '') ?>">
  </label>
  <label>Default theme for new visitors
    <select name="default_theme">
      <option value="dark"  <?= ($settings['default_theme'] ?? 'dark') === 'dark'  ? 'selected' : '' ?>>Dark</option>
      <option value="light" <?= ($settings['default_theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
    </select>
  </label>
  <button class="btn btn-primary" type="submit">Save settings</button>
</form>
