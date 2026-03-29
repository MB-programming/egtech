<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && !empty($_POST['uid'])) {
        dgtec_user_delete((int)$_POST['uid']);
        header('Location: users.php?deleted=1'); exit;
    }
    if ($action === 'toggle' && !empty($_POST['uid'])) {
        $u = dgtec_user_get((int)$_POST['uid']);
        if ($u) dgtec_user_save(array_merge($u, ['is_active' => $u['is_active'] ? 0 : 1]));
        header('Location: users.php'); exit;
    }
}

$users       = dgtec_users_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'users';
$currentUid  = $_SESSION['dgtec_admin_uid'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Users – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Users <span>Management</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(),0,1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">User deleted.</div><?php endif; ?>
      <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">User saved.</div><?php endif; ?>

      <div class="page-header">
        <div><h1>Admin Users</h1><p>Manage admin accounts and permissions.</p></div>
        <div style="display:flex;gap:8px">
          <a href="roles.php" class="btn btn-secondary"><i class="fas fa-shield-halved"></i> Manage Roles</a>
          <a href="user-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body" style="padding:0">
          <table class="admin-table">
            <thead><tr><th>#</th><th>User</th><th>Role</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($u['display_name'] ?: $u['username']) ?></strong>
                <div style="font-size:12px;color:var(--gray)"><?= htmlspecialchars($u['username']) ?></div>
              </td>
              <td>
                <?php if ($u['role_name']): ?>
                <span style="background:rgba(3,134,158,.1);color:var(--btn);padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600">
                  <?= htmlspecialchars($u['role_name']) ?>
                </span>
                <?php else: ?>
                <span style="color:var(--gray);font-size:12px">No role</span>
                <?php endif; ?>
              </td>
              <td style="font-size:13px;color:var(--gray)"><?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Never' ?></td>
              <td>
                <?php if ((int)$u['id'] !== (int)$currentUid): ?>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle"/>
                  <input type="hidden" name="uid" value="<?= $u['id'] ?>"/>
                  <button type="submit" class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-secondary' ?>"
                          style="border:none;cursor:pointer;font-size:12px;padding:3px 10px;border-radius:20px">
                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                  </button>
                </form>
                <?php else: ?>
                <span class="badge badge-success" style="font-size:12px;padding:3px 10px;border-radius:20px">Active (You)</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="table-actions">
                  <a href="user-form.php?id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i></a>
                  <?php if ((int)$u['id'] !== (int)$currentUid): ?>
                  <form method="post" style="display:inline" onsubmit="return confirm('Delete this user?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete"/>
                    <input type="hidden" name="uid" value="<?= $u['id'] ?>"/>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
</body></html>
