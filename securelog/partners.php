<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$msg     = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id) {
        $p = dgtec_partner_get($id);
        if ($p) {
            dgtec_db()->prepare("UPDATE `partners` SET `is_active`=? WHERE `id`=?")
                ->execute([$p['is_active'] ? 0 : 1, $id]);
            $msg = 'Partner status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        $p = dgtec_partner_get($id);
        if ($p && $p['logo'] && strpos($p['logo'], 'assets/images/partners/') !== false) {
            $path = dirname(__DIR__) . '/' . $p['logo'];
            if (file_exists($path)) unlink($path);
        }
        dgtec_partner_delete($id);
        $msg = 'Partner deleted.';
    } elseif ($action === 'move_up' && $id) {
        dgtec_partner_move($id, 'up');
        $msg = 'Moved up.';
    } elseif ($action === 'move_down' && $id) {
        dgtec_partner_move($id, 'down');
        $msg = 'Moved down.';
    }
}

$partners    = dgtec_partners_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'partners';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Partners – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Our <span>Partners</span></div>
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
          <h1>Partners</h1>
          <p>Manage partner logos displayed in the homepage marquee strip.</p>
        </div>
        <a href="partner-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Partner</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Partners (<?= count($partners) ?>)</h2>
          <a href="../index.php#clients" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Preview</a>
        </div>

        <?php if (empty($partners)): ?>
        <div class="card-body" style="text-align:center;padding:60px;color:var(--gray)">
          <i class="fas fa-handshake" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          No partners yet. <a href="partner-form.php" style="color:var(--btn)">Add your first partner</a>.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="slides-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Logo</th>
              <th>Name</th>
              <th>Website</th>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($partners as $i => $p): ?>
            <tr>
              <td style="color:var(--gray);font-weight:700"><?= $p['position'] ?></td>

              <td>
                <?php if ($p['logo']): ?>
                <img src="../<?= htmlspecialchars($p['logo']) ?>" class="slide-thumb"
                     style="object-fit:contain;background:#f8fafc;border:1px solid var(--border)"
                     alt="<?= htmlspecialchars($p['name']) ?>" />
                <?php else: ?>
                <div class="slide-thumb-placeholder"><i class="fas fa-image"></i></div>
                <?php endif; ?>
              </td>

              <td style="font-weight:700;font-size:13px"><?= htmlspecialchars($p['name']) ?></td>

              <td style="font-size:12px;color:var(--gray)">
                <?php if ($p['website_url']): ?>
                <a href="<?= htmlspecialchars($p['website_url']) ?>" target="_blank" style="color:var(--p)"><?= htmlspecialchars($p['website_url']) ?></a>
                <?php else: ?>
                <span>—</span>
                <?php endif; ?>
              </td>

              <td>
                <form method="post" style="display:inline">
                  <input type="hidden" name="action" value="toggle" />
                  <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                  <button type="submit" class="badge <?= $p['is_active'] ? 'badge-active' : 'badge-inactive' ?>" style="border:none;cursor:pointer">
                    <?= $p['is_active'] ? 'Active' : 'Hidden' ?>
                  </button>
                </form>
              </td>

              <td>
                <div class="order-btns">
                  <?php if ($i > 0): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="move_up" />
                    <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move up"><i class="fas fa-chevron-up"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($i < count($partners) - 1): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="move_down" />
                    <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move down"><i class="fas fa-chevron-down"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>

              <td>
                <div style="display:flex;gap:6px">
                  <a href="partner-form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'] ?: 'this partner')) ?>')">
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
      </div>

    </div>
  </main>
</div>

<div class="modal-backdrop" id="deleteModal">
  <div class="modal">
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Partner</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this partner?</p>
    <div class="modal-btns">
      <button class="btn btn-secondary" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
      <form method="post" style="display:inline">
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
