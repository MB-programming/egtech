<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('users');

$id   = (int)($_GET['id'] ?? 0);
$role = $id ? dgtec_role_get($id) : null;
$isEdit   = !empty($role);
$allPerms = dgtec_all_permissions();
$errors   = [];

$existingPerms = [];
if ($role) {
    $ep = json_decode($role['permissions_json'] ?: '[]', true);
    $existingPerms = is_array($ep) ? $ep : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $displayName = trim($_POST['display_name'] ?? '');
    $selected    = array_keys(array_filter($_POST['perm'] ?? [], fn($v) => $v == '1'));

    if (!$displayName) $errors[] = 'Role name is required.';

    if (empty($errors)) {
        dgtec_role_save(['id' => $id, 'display_name' => $displayName, 'permissions' => $selected]);
        header('Location: roles.php?saved=1'); exit;
    }
    $existingPerms = $selected;
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'users';
$pageTitle   = $isEdit ? 'Edit Role' : 'New Role';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
  <style>
    .perm-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:10px; margin-top:12px; }
    .perm-item { display:flex; align-items:center; gap:10px; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; cursor:pointer; transition:.15s; }
    .perm-item:has(input:checked) { border-color:var(--btn); background:rgba(3,134,158,.06); }
    .perm-item input { width:16px; height:16px; accent-color:var(--btn); cursor:pointer; }
    .perm-item label { font-size:13px; font-weight:600; color:var(--dark); cursor:pointer; flex:1; }
  </style>
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
        <a href="roles.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if ($errors): ?>
      <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="card">
          <div class="card-body">
            <div class="form-group" style="max-width:400px;margin-bottom:28px">
              <label class="form-label">Role Name <span style="color:#dc2626">*</span></label>
              <input type="text" name="display_name" class="form-input"
                     value="<?= htmlspecialchars($role['display_name'] ?? '') ?>"
                     placeholder="e.g. Content Editor"
                     <?= ($role['is_system'] ?? 0) ? 'readonly' : '' ?> required />
              <?php if ($role['is_system'] ?? 0): ?>
              <p class="form-hint">System role — name cannot be changed.</p>
              <?php endif; ?>
            </div>

            <h3 style="font-size:15px;font-weight:700;color:var(--dark);margin-bottom:4px">Permissions</h3>
            <p style="font-size:13px;color:var(--gray);margin-bottom:4px">Select what this role can access.</p>

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
              <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(true)">Select All</button>
              <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(false)">Clear All</button>
            </div>

            <div class="perm-grid">
              <?php foreach ($allPerms as $key => $label): ?>
              <div class="perm-item" onclick="this.querySelector('input').click()">
                <input type="checkbox" name="perm[<?= $key ?>]" value="1" id="p_<?= $key ?>"
                       <?= in_array($key, $existingPerms) ? 'checked' : '' ?>
                       onclick="event.stopPropagation()"
                       <?= ($role['is_system'] ?? 0) ? 'disabled' : '' ?> />
                <label for="p_<?= $key ?>" onclick="event.preventDefault()"><?= htmlspecialchars($label) ?></label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if (!($role['is_system'] ?? 0)): ?>
          <div class="card-body" style="border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px">
            <a href="roles.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Role</button>
          </div>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </main>
</div>
<script>
function toggleAll(checked) {
  document.querySelectorAll('.perm-item input[type=checkbox]').forEach(function(cb){ cb.checked = checked; });
}
</script>
</body></html>
