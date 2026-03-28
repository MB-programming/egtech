<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$type    = 'service';
$msg     = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id) {
        $item = dgtec_item_get($type, $id);
        if ($item) {
            dgtec_db()->prepare("UPDATE services SET is_active=? WHERE id=?")
                ->execute([$item['is_active'] ? 0 : 1, $id]);
            $msg = 'Service status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        dgtec_item_delete($type, $id);
        $msg = 'Service deleted.';
    } elseif ($action === 'move_up' && $id) {
        dgtec_item_move($type, $id, 'up');
        $msg = 'Moved up.';
    } elseif ($action === 'move_down' && $id) {
        dgtec_item_move($type, $id, 'down');
        $msg = 'Moved down.';
    }
}

$items       = dgtec_items_all($type);
$unreadCount = dgtec_submissions_unread_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Services – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-brand">
      <img src="../assets/images/logo.webp" alt="DGTEC" />
      <span>Admin</span>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-section">Content</p>
      <a href="slides.php"><i class="fas fa-images"></i> Hero Slides</a>
      <a href="services.php" class="active"><i class="fas fa-briefcase"></i> Services</a>
      <a href="solutions.php"><i class="fas fa-lightbulb"></i> Solutions</a>
      <p class="nav-section">Inbox</p>
      <a href="submissions.php">
        <i class="fas fa-envelope"></i> Submissions
        <?php if ($unreadCount > 0): ?>
        <span style="margin-left:auto;background:#dc2626;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700"><?= $unreadCount ?></span>
        <?php endif; ?>
      </a>
      <p class="nav-section">Site</p>
      <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Our <span>Services</span></div>
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
          <h1>Services</h1>
          <p>Manage the services displayed on the Services page.</p>
        </div>
        <a href="item-form.php?type=service" class="btn btn-primary"><i class="fas fa-plus"></i> Add Service</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Services (<?= count($items) ?>)</h2>
          <a href="../services.php" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Preview</a>
        </div>

        <?php if (empty($items)): ?>
        <div class="card-body" style="text-align:center;padding:60px;color:var(--gray)">
          <i class="fas fa-briefcase" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          No services yet. <a href="item-form.php?type=service" style="color:var(--btn)">Add your first service</a>.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="slides-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Image</th>
              <th>Icon</th>
              <th>Title</th>
              <th>Features</th>
              <th>Page URL</th>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $i => $s): ?>
          <?php $featureCount = count(array_filter(explode('|', $s['features']))); ?>
            <tr>
              <td style="color:var(--gray);font-weight:700"><?= $s['position'] ?></td>

              <td>
                <?php if ($s['image']): ?>
                <img src="../<?= htmlspecialchars($s['image']) ?>"
                     class="slide-thumb"
                     alt="<?= htmlspecialchars($s['title']) ?>" />
                <?php else: ?>
                <div class="slide-thumb-placeholder"><i class="fas fa-image"></i></div>
                <?php endif; ?>
              </td>

              <td style="font-size:20px;color:var(--p);text-align:center">
                <i class="<?= htmlspecialchars($s['icon']) ?>"></i>
              </td>

              <td>
                <div style="font-weight:700;font-size:13px"><?= htmlspecialchars($s['title']) ?></div>
                <div style="font-size:11px;color:var(--gray);margin-top:2px"><?= htmlspecialchars($s['slug']) ?></div>
              </td>

              <td style="color:var(--gray);font-size:12px"><?= $featureCount ?> feature<?= $featureCount !== 1 ? 's' : '' ?></td>

              <td style="font-size:12px;color:var(--gray)">
                <a href="../<?= htmlspecialchars($s['page_url']) ?>" target="_blank" style="color:var(--p)">
                  <?= htmlspecialchars($s['page_url']) ?>
                </a>
              </td>

              <td>
                <form method="post" style="display:inline">
                  <input type="hidden" name="action" value="toggle" />
                  <input type="hidden" name="id" value="<?= $s['id'] ?>" />
                  <button type="submit" class="badge <?= $s['is_active'] ? 'badge-active' : 'badge-inactive' ?>" style="border:none;cursor:pointer">
                    <?= $s['is_active'] ? 'Active' : 'Hidden' ?>
                  </button>
                </form>
              </td>

              <td>
                <div class="order-btns">
                  <?php if ($i > 0): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="move_up" />
                    <input type="hidden" name="id" value="<?= $s['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move up"><i class="fas fa-chevron-up"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($i < count($items) - 1): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="move_down" />
                    <input type="hidden" name="id" value="<?= $s['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move down"><i class="fas fa-chevron-down"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>

              <td>
                <div style="display:flex;gap:6px">
                  <a href="item-form.php?type=service&id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['title'] ?: 'this service')) ?>')">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
        <?php endif; ?>
      </div><!-- /.card -->

    </div><!-- /.admin-content -->
  </main>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-backdrop" id="deleteModal">
  <div class="modal">
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Service</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this service?</p>
    <div class="modal-btns">
      <button class="btn btn-secondary" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
      <form method="post" id="deleteForm" style="display:inline">
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" id="deleteId" value="" />
        <button type="submit" class="btn btn-danger">Delete</button>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDelete(id, label) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteMsg').textContent = 'Delete "' + label + '"? This action cannot be undone.';
  document.getElementById('deleteModal').classList.add('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>
