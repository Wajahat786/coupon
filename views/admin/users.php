<h1>Users</h1>

<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last login</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): $uid = (int) $u['id']; ?>
    <tr>
      <td>#<?= $uid ?></td>
      <td><?= e($u['name']) ?></td>
      <td class="muted"><?= e($u['email']) ?></td>
      <td><span class="chip chip-<?= $u['role']==='admin'?'grad':'info' ?>"><?= e($u['role']) ?></span></td>
      <td><span class="chip chip-<?= (int)$u['is_active']===1?'ok':'danger' ?>"><?= (int)$u['is_active']===1?'active':'disabled' ?></span></td>
      <td class="muted"><?= e(time_ago($u['last_login_at'] ?? null)) ?: '—' ?></td>
      <td>
        <form method="post" action="/admin/users/toggle" class="inline-form" onsubmit="return confirm('Toggle this user?');">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $uid ?>">
          <button class="btn btn-outline btn-xs" type="submit"><?= (int)$u['is_active']===1 ? 'Disable' : 'Enable' ?></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<details class="admin-create">
  <summary class="btn btn-outline">＋ Add user</summary>
  <form method="post" action="/admin/users/add" class="form form-wide mt">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label>Name *<input name="name" required minlength="2" maxlength="100"></label>
      <label>Role
        <select name="role"><option value="submitter">Submitter</option><option value="admin">Admin</option></select>
      </label>
      <label>Email *<input type="email" name="email" required maxlength="190"></label>
      <label>Password * (min 10)<input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
    </div>
    <button class="btn btn-primary" type="submit">Create user</button>
  </form>
</details>
