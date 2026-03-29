<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    if ($_POST['action'] === 'delete' && !empty($_POST['rid'])) {
        dgtec_role_delete((int)$_POST['rid']);
        header('Location: roles.php?deleted=1'); exit;
    }
}

$roles       = dgtec_roles_all();
$allPerms    = dgtec_all_permissions();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Roles – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Roles <span>& Permissions</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(),0,1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Role deleted.</div><?php endif; ?>
      <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Role saved.</div><?php endif; ?>

      <div class="page-header">
        <div><h1>Roles</h1><p>Define access levels for admin users.</p></div>
        <div style="display:flex;gap:8px">
          <a href="users.php" class="btn btn-secondary"><i class="fas fa-users"></i> Users</a>
          <a href="role-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Role</a>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px">
        <?php foreach ($roles as $role):
          $perms = json_decode($role['permissions_json'] ?: '[]', true) ?: [];
          $isAll = in_array('*', $perms) || count($perms) === count($allPerms);
          $userCount = (int)dgtec_db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role_id=?")->execute([$role['id']]) ? 0 : 0;
          $stmt = dgtec_db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role_id=?");
          $stmt->execute([$role['id']]);
          $userCount = (int)$stmt->fetchColumn();
        ?>
        <div class="card">
          <div class="card-body">
            <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:12px">
              <div>
                <h3 style="font-size:17px;font-weight:700;color:var(--primary)"><?= htmlspecialchars($role['display_name']) ?></h3>
                <div style="font-size:12px;color:var(--gray);margin-top:2px">
                  <code><?= htmlspecialchars($role['name']) ?></code>
                  <?= $role['is_system'] ? ' &middot; <span style="color:var(--btn)">System</span>' : '' ?>
                  &middot; <?= $userCount ?> user<?= $userCount !== 1 ? 's' : '' ?>
                </div>
              </div>
              <div style="display:flex;gap:6px">
                <?php if (!$role['is_system']): ?>
                <a href="role-form.php?id=<?= $role['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i></a>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete role? Users will lose this role.')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete"/>
                  <input type="hidden" name="rid" value="<?= $role['id'] ?>"/>
                  <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
                <?php else: ?>
                <a href="role-form.php?id=<?= $role['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($isAll): ?>
            <span style="background:rgba(22,163,74,.1);color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
              <i class="fas fa-check-circle"></i> Full Access
            </span>
            <?php else: ?>
            <div style="display:flex;flex-wrap:wrap;gap:5px">
              <?php foreach ($perms as $p):
                $label = $allPerms[$p] ?? $p;
              ?>
              <span style="background:rgba(3,134,158,.1);color:var(--btn);padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600"><?= htmlspecialchars($label) ?></span>
              <?php endforeach; ?>
              <?php if (empty($perms)): ?><span style="color:var(--gray);font-size:12px">No permissions</span><?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </main>
</div>
</body></html>
