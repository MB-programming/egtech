<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

/* ---- Handle quick actions ---- */
$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id) {
        $slide = dgtec_slide_get($id);
        if ($slide) {
            dgtec_db()->prepare("UPDATE hero_slides SET is_active = ? WHERE id = ?")
                ->execute([$slide['is_active'] ? 0 : 1, $id]);
            $msg = 'Slide status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        $slide = dgtec_slide_get($id);
        if ($slide && $slide['bg_image'] && strpos($slide['bg_image'], 'assets/images/slides/') !== false) {
            $path = dirname(__DIR__) . '/' . $slide['bg_image'];
            if (file_exists($path)) unlink($path);
        }
        dgtec_slide_delete($id);
        $msg = 'Slide deleted.';
    } elseif ($action === 'move_up' && $id) {
        dgtec_slide_move($id, 'up');
        $msg = 'Slide moved up.';
    } elseif ($action === 'move_down' && $id) {
        dgtec_slide_move($id, 'down');
        $msg = 'Slide moved down.';
    }
}

$slides      = dgtec_slides_all();
$unreadCount = dgtec_submissions_unread_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hero Slides – DGTEC Admin</title>
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
      <a href="slides.php" class="active"><i class="fas fa-images"></i> Hero Slides</a>
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
      <div class="topbar-title">Hero <span>Slides</span></div>
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
          <h1>Hero Slides</h1>
          <p>Manage the homepage hero slider. Drag to reorder or use arrows.</p>
        </div>
        <a href="slide-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Slide</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Slides (<?= count($slides) ?>)</h2>
          <a href="../index.php#home" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Preview</a>
        </div>

        <?php if (empty($slides)): ?>
        <div class="card-body" style="text-align:center;padding:60px;color:var(--gray)">
          <i class="fas fa-images" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          No slides yet. <a href="slide-form.php" style="color:var(--btn)">Add your first slide</a>.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="slides-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Thumbnail</th>
              <th>Label / Title</th>
              <th>Gradient</th>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($slides as $i => $s): ?>
            <tr>
              <td style="color:var(--gray);font-weight:700"><?= $s['position'] ?></td>

              <td>
                <?php if ($s['bg_image']): ?>
                <img src="../<?= htmlspecialchars($s['bg_image']) ?>"
                     class="slide-thumb"
                     style="border:2px solid rgba(<?= implode(',', sscanf($s['gradient_color1'], '#%02x%02x%02x') ?: [24,63,150]) ?>,.3)"
                     alt="Slide" />
                <?php else: ?>
                <div class="slide-thumb-placeholder"><i class="fas fa-image"></i></div>
                <?php endif; ?>
              </td>

              <td>
                <div style="font-size:11px;color:var(--acc);font-weight:600;letter-spacing:.5px;margin-bottom:2px"><?= htmlspecialchars($s['label']) ?></div>
                <div style="font-weight:700;font-size:13px">
                  <?= nl2br(htmlspecialchars($s['title'])) ?>
                  <?php if ($s['highlight_text']): ?>
                  <em style="color:var(--btn)"><?= htmlspecialchars($s['highlight_text']) ?></em>
                  <?php endif; ?>
                </div>
              </td>

              <td>
                <div style="width:60px;height:28px;border-radius:6px;background:linear-gradient(105deg,<?= hex_rgba($s['gradient_color1'], $s['gradient_opacity1']) ?> 0%,<?= hex_rgba($s['gradient_color2'], $s['gradient_opacity2']) ?> 100%)"></div>
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
                  <?php if ($i < count($slides) - 1): ?>
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
                  <a href="slide-form.php?id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['label'] ?: 'this slide')) ?>')">
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
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Slide</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this slide?</p>
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
