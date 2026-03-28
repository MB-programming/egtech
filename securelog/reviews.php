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
        $r = dgtec_review_get($id);
        if ($r) {
            dgtec_db()->prepare("UPDATE `client_reviews` SET `is_active`=? WHERE `id`=?")
                ->execute([$r['is_active'] ? 0 : 1, $id]);
            $msg = 'Review status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        $r = dgtec_review_get($id);
        if ($r && $r['image'] && strpos($r['image'], 'assets/images/reviews/') !== false) {
            $path = dirname(__DIR__) . '/' . $r['image'];
            if (file_exists($path)) unlink($path);
        }
        dgtec_review_delete($id);
        $msg = 'Review deleted.';
    } elseif ($action === 'move_up' && $id) {
        dgtec_review_move($id, 'up');
        $msg = 'Moved up.';
    } elseif ($action === 'move_down' && $id) {
        dgtec_review_move($id, 'down');
        $msg = 'Moved down.';
    }
}

$reviews     = dgtec_reviews_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Client Reviews – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Client <span>Reviews</span></div>
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
          <h1>Client Reviews</h1>
          <p>Manage the "Our Clients Says" testimonials slider on the homepage.</p>
        </div>
        <a href="review-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Review</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Reviews (<?= count($reviews) ?>)</h2>
          <a href="../index.php#testimonials" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Preview</a>
        </div>

        <?php if (empty($reviews)): ?>
        <div class="card-body" style="text-align:center;padding:60px;color:var(--gray)">
          <i class="fas fa-star" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          No reviews yet. <a href="review-form.php" style="color:var(--btn)">Add your first review</a>.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="slides-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Photo</th>
              <th>Name / Job Title</th>
              <th>Stars</th>
              <th>Review</th>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($reviews as $i => $r): ?>
            <tr>
              <td style="color:var(--gray);font-weight:700"><?= $r['position'] ?></td>

              <td>
                <?php if ($r['image']): ?>
                <img src="../<?= htmlspecialchars($r['image']) ?>" class="slide-thumb"
                     style="border-radius:50%;object-fit:cover;width:44px;height:44px"
                     alt="<?= htmlspecialchars($r['name']) ?>" />
                <?php else: ?>
                <div style="width:44px;height:44px;border-radius:50%;background:var(--p);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700">
                  <?= strtoupper(substr($r['name'], 0, 1)) ?>
                </div>
                <?php endif; ?>
              </td>

              <td>
                <div style="font-weight:700;font-size:13px"><?= htmlspecialchars($r['name']) ?></div>
                <div style="font-size:11px;color:var(--gray);margin-top:2px"><?= htmlspecialchars($r['job_title']) ?></div>
              </td>

              <td>
                <span style="color:#f59e0b;font-size:14px">
                  <?= str_repeat('★', (int)$r['stars']) ?><?= str_repeat('☆', 5 - (int)$r['stars']) ?>
                </span>
              </td>

              <td style="font-size:12px;color:var(--gray);max-width:280px">
                <?= htmlspecialchars(mb_substr($r['review'], 0, 100)) ?>…
              </td>

              <td>
                <form method="post" style="display:inline">
                  <input type="hidden" name="action" value="toggle" />
                  <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                  <button type="submit" class="badge <?= $r['is_active'] ? 'badge-active' : 'badge-inactive' ?>" style="border:none;cursor:pointer">
                    <?= $r['is_active'] ? 'Active' : 'Hidden' ?>
                  </button>
                </form>
              </td>

              <td>
                <div class="order-btns">
                  <?php if ($i > 0): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="move_up" />
                    <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move up"><i class="fas fa-chevron-up"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($i < count($reviews) - 1): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="move_down" />
                    <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move down"><i class="fas fa-chevron-down"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>

              <td>
                <div style="display:flex;gap:6px">
                  <a href="review-form.php?id=<?= $r['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'] ?: 'this review')) ?>')">
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
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Review</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this review?</p>
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
  document.getElementById('deleteMsg').textContent = 'Delete review by "' + label + '"? This action cannot be undone.';
  document.getElementById('deleteModal').classList.add('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>
