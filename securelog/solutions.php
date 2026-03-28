<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$type    = 'solution';
$msg     = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id) {
        $item = dgtec_item_get($type, $id);
        if ($item) {
            dgtec_db()->prepare("UPDATE solutions SET is_active=? WHERE id=?")
                ->execute([$item['is_active'] ? 0 : 1, $id]);
            $msg = 'Solution status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        dgtec_item_delete($type, $id);
        $msg = 'Solution deleted.';
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
$activePage  = 'solutions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solutions – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Our <span>Solutions</span></div>
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
          <h1>Solutions</h1>
          <p>Manage the solutions displayed on the Solutions page.</p>
        </div>
        <a href="item-form.php?type=solution" class="btn btn-primary"><i class="fas fa-plus"></i> Add Solution</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Solutions (<?= count($items) ?>)</h2>
          <a href="../solutions.php" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Preview</a>
        </div>

        <?php if (empty($items)): ?>
        <div class="card-body" style="text-align:center;padding:60px;color:var(--gray)">
          <i class="fas fa-lightbulb" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          No solutions yet. <a href="item-form.php?type=solution" style="color:var(--btn)">Add your first solution</a>.
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
                  <?= csrf_field() ?>
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
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="move_up" />
                    <input type="hidden" name="id" value="<?= $s['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move up"><i class="fas fa-chevron-up"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($i < count($items) - 1): ?>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="move_down" />
                    <input type="hidden" name="id" value="<?= $s['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move down"><i class="fas fa-chevron-down"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>

              <td>
                <div style="display:flex;gap:6px">
                  <a href="item-form.php?type=solution&id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['title'] ?: 'this solution')) ?>')">
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
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Solution</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this solution?</p>
    <div class="modal-btns">
      <button class="btn btn-secondary" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
      <form method="post" id="deleteForm" style="display:inline">
        <?= csrf_field() ?>
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
