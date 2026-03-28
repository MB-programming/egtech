<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$msg     = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id) {
        $row = dgtec_social_link_get($id);
        if ($row) {
            dgtec_db()->prepare("UPDATE `social_links` SET `is_active`=? WHERE `id`=?")
                ->execute([$row['is_active'] ? 0 : 1, $id]);
            $msg = 'Status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        dgtec_social_link_delete($id);
        $msg = 'Deleted.';
    } elseif ($action === 'move_up' && $id) {
        dgtec_social_link_move($id, 'up');
    } elseif ($action === 'move_down' && $id) {
        dgtec_social_link_move($id, 'down');
    }
}

$links       = dgtec_social_links_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'social-media';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Social Media – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Social <span>Media</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>

    <div class="admin-content">

      <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1>Social Media Links</h1>
          <p>Manage the social media links shown in the site header and footer.</p>
        </div>
        <a href="social-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Link</a>
      </div>

      <div class="card">
        <div class="card-header"><h2>All Social Links</h2></div>
        <div class="card-body" style="padding:0">
          <?php if (empty($links)): ?>
          <p style="padding:24px;color:var(--gray);text-align:center">No social links yet.</p>
          <?php else: ?>
          <table class="admin-table">
            <thead><tr>
              <th style="width:40px">#</th>
              <th>Platform</th>
              <th>Icon</th>
              <th>URL</th>
              <th>Status</th>
              <th style="width:160px">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($links as $link): ?>
            <tr>
              <td><?= (int)$link['position'] ?></td>
              <td><strong><?= htmlspecialchars($link['platform']) ?></strong></td>
              <td><i class="<?= htmlspecialchars($link['icon']) ?>" style="font-size:18px;color:var(--p)"></i></td>
              <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" style="color:var(--btn)">
                  <?= htmlspecialchars($link['url'] ?: '—') ?>
                </a>
              </td>
              <td>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle" />
                  <input type="hidden" name="id" value="<?= $link['id'] ?>" />
                  <button type="submit" class="badge <?= $link['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $link['is_active'] ? 'Active' : 'Hidden' ?>
                  </button>
                </form>
              </td>
              <td>
                <div style="display:flex;gap:6px;align-items:center">
                  <form method="post" style="display:inline"><<?= csrf_field() ?><input type="hidden" name="action" value="move_up" /><input type="hidden" name="id" value="<?= $link['id'] ?>" /><button type="submit" class="btn-icon" title="Move Up"><i class="fas fa-arrow-up"></i></button></form>
                  <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="move_down" /><input type="hidden" name="id" value="<?= $link['id'] ?>" /><button type="submit" class="btn-icon" title="Move Down"><i class="fas fa-arrow-down"></i></button></form>
                  <a href="social-form.php?id=<?= $link['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                  <form method="post" style="display:inline" onsubmit="return confirm('Delete this link?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?= $link['id'] ?>" />
                    <button type="submit" class="btn-icon btn-icon-danger" title="Delete"><i class="fas fa-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>
</body>
</html>
