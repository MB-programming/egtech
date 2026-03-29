<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('users');

$id   = (int)($_GET['id'] ?? 0);
$user = $id ? dgtec_user_get($id) : null;
$isEdit = !empty($user);
$roles  = dgtec_roles_all();
$errors = [];

$d = array_merge(['id'=>0,'username'=>'','display_name'=>'','role_id'=>'','is_active'=>1,'password'=>''], $user ?? []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $username     = trim($_POST['username'] ?? '');
    $displayName  = trim($_POST['display_name'] ?? '');
    $roleId       = (int)($_POST['role_id'] ?? 0) ?: null;
    $isActive     = isset($_POST['is_active']) ? 1 : 0;
    $password     = $_POST['password'] ?? '';
    $confirmPass  = $_POST['confirm_password'] ?? '';

    if (!$username) $errors[] = 'Username is required.';
    if (!$isEdit && !$password) $errors[] = 'Password is required for new users.';
    if ($password && $password !== $confirmPass) $errors[] = 'Passwords do not match.';
    if ($password && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    /* check username uniqueness */
    if ($username && !$isEdit) {
        $existing = dgtec_user_get_by_username($username);
        if ($existing) $errors[] = 'Username already exists.';
    }

    if (empty($errors)) {
        $save = [
            'id' => $id, 'username' => $username, 'display_name' => $displayName,
            'role_id' => $roleId, 'is_active' => $isActive,
        ];
        if ($password) $save['password'] = $password;
        dgtec_user_save($save);
        header('Location: users.php?saved=1'); exit;
    }
    $d = array_merge($d, compact('username','displayName','roleId','isActive'));
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'users';
$pageTitle   = $isEdit ? 'Edit User' : 'Add User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $pageTitle ?></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(),0,1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">
      <div class="page-header">
        <div><h1><?= $pageTitle ?></h1></div>
        <a href="users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if ($errors): ?>
      <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="card">
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Username <span style="color:#dc2626">*</span></label>
                <?php if ($isEdit): ?>
                <input type="text" class="form-input" value="<?= htmlspecialchars($d['username']) ?>" disabled />
                <?php else: ?>
                <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($d['username']) ?>" required placeholder="e.g. john.doe" />
                <?php endif; ?>
              </div>
              <div class="form-group">
                <label class="form-label">Display Name</label>
                <input type="text" name="display_name" class="form-input" value="<?= htmlspecialchars($d['display_name']) ?>" placeholder="Full name" />
              </div>
              <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role_id" class="form-input">
                  <option value="">— No Role —</option>
                  <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['id'] ?>" <?= (int)$d['role_id'] === (int)$r['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['display_name']) ?>
                    <?= $r['is_system'] ? '(System)' : '' ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label"><?= $isEdit ? 'New Password' : 'Password' ?> <?= !$isEdit ? '<span style="color:#dc2626">*</span>' : '' ?></label>
                <input type="password" name="password" class="form-input" placeholder="<?= $isEdit ? 'Leave empty to keep current' : 'Min 8 characters' ?>" autocomplete="new-password" />
              </div>
              <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Repeat password" autocomplete="new-password" />
              </div>
              <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;margin-top:22px">
                  <input type="checkbox" name="is_active" value="1" <?= $d['is_active'] ? 'checked' : '' ?> style="width:16px;height:16px" />
                  <strong>Active</strong> — user can log in
                </label>
              </div>
            </div>
          </div>
          <div class="card-body" style="border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px">
            <a href="users.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save User</button>
          </div>
        </div>
      </form>
    </div>
  </main>
</div>
</body></html>
