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
        $p = dgtec_page_get($id);
        if ($p) {
            dgtec_db()->prepare("UPDATE `pages` SET `is_active`=? WHERE `id`=?")
                ->execute([$p['is_active'] ? 0 : 1, $id]);
            $msg = 'Status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        dgtec_page_delete($id);
        $msg = 'Page deleted.';
    }
}

$pages       = dgtec_pages_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'pages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pages – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Custom <span>Pages</span></div>
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
          <h1>Custom Pages</h1>
          <p>Create and manage custom pages. Each page gets a unique URL based on its slug.</p>
        </div>
        <a href="page-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Page</a>
      </div>

      <div class="card">
        <div class="card-header"><h2>All Pages</h2></div>
        <div class="card-body" style="padding:0">
          <?php if (empty($pages)): ?>
          <p style="padding:24px;color:var(--gray);text-align:center">No custom pages yet. Click "Add Page" to create one.</p>
          <?php else: ?>
          <table class="admin-table">
            <thead><tr>
              <th>Title</th>
              <th>Slug / URL</th>
              <th>Status</th>
              <th style="width:160px">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($pages as $page): ?>
            <tr>
              <td><strong><?= htmlspecialchars($page['title']) ?></strong></td>
              <td>
                <code style="font-size:12px;color:var(--gray)">page.php?slug=<?= htmlspecialchars($page['slug']) ?></code>
                <a href="../page.php?slug=<?= urlencode($page['slug']) ?>" target="_blank"
                   style="margin-left:6px;color:var(--btn);font-size:12px">
                  <i class="fas fa-arrow-up-right-from-square"></i> Preview
                </a>
              </td>
              <td>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle" />
                  <input type="hidden" name="id" value="<?= $page['id'] ?>" />
                  <button type="submit" class="badge <?= $page['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $page['is_active'] ? 'Published' : 'Draft' ?>
                  </button>
                </form>
              </td>
              <td>
                <div style="display:flex;gap:6px;align-items:center">
                  <a href="page-form.php?id=<?= $page['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                  <form method="post" style="display:inline" onsubmit="return confirm('Delete this page?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?= $page['id'] ?>" />
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
