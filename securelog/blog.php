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
        $post = dgtec_blog_get($id);
        if ($post) {
            dgtec_db()->prepare("UPDATE `blog_posts` SET `is_active`=? WHERE `id`=?")
                ->execute([$post['is_active'] ? 0 : 1, $id]);
            $msg = 'Post status updated.';
        }
    } elseif ($action === 'delete' && $id) {
        $post = dgtec_blog_get($id);
        if ($post && $post['image'] && strpos($post['image'], 'assets/images/blog/') !== false) {
            $path = dirname(__DIR__) . '/' . $post['image'];
            if (file_exists($path)) unlink($path);
        }
        dgtec_blog_delete($id);
        $msg = 'Post deleted.';
    } elseif ($action === 'move_up' && $id) {
        dgtec_blog_move($id, 'up');
        $msg = 'Moved up.';
    } elseif ($action === 'move_down' && $id) {
        dgtec_blog_move($id, 'down');
        $msg = 'Moved down.';
    }
}

$posts       = dgtec_blogs_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'blog';

if (isset($_GET['saved'])) $msg = 'Post saved successfully.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blog Posts – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Blog <span>Posts</span></div>
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
          <h1>Blog Posts</h1>
          <p>Manage articles shown on the Blog &amp; Insights page.</p>
        </div>
        <a href="blog-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Post</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Posts (<?= count($posts) ?>)</h2>
          <a href="../blog.php" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Preview</a>
        </div>

        <?php if (empty($posts)): ?>
        <div class="card-body" style="text-align:center;padding:60px;color:var(--gray)">
          <i class="fas fa-newspaper" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          No posts yet. <a href="blog-form.php" style="color:var(--btn)">Write your first post</a>.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="slides-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Image</th>
              <th>Title</th>
              <th>Category</th>
              <th>Published</th>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($posts as $i => $p): ?>
            <tr>
              <td style="color:var(--gray);font-weight:700"><?= $p['position'] ?></td>

              <td>
                <?php if ($p['image']): ?>
                <img src="../<?= htmlspecialchars($p['image']) ?>" class="slide-thumb" alt="" />
                <?php else: ?>
                <div class="slide-thumb-placeholder"><i class="fas fa-image"></i></div>
                <?php endif; ?>
              </td>

              <td>
                <div style="font-weight:700;font-size:13px"><?= htmlspecialchars($p['title']) ?></div>
                <div style="font-size:11px;color:var(--gray);margin-top:2px">/blog/<?= htmlspecialchars($p['slug']) ?></div>
              </td>

              <td>
                <span class="badge badge-active" style="font-size:11px"><?= htmlspecialchars($p['category']) ?></span>
              </td>

              <td style="font-size:12px;color:var(--gray)">
                <?= $p['published_at'] ? date('M j, Y', strtotime($p['published_at'])) : '—' ?>
              </td>

              <td>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle" />
                  <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                  <button type="submit" class="badge <?= $p['is_active'] ? 'badge-active' : 'badge-inactive' ?>" style="border:none;cursor:pointer">
                    <?= $p['is_active'] ? 'Published' : 'Draft' ?>
                  </button>
                </form>
              </td>

              <td>
                <div class="order-btns">
                  <?php if ($i > 0): ?>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="move_up" />
                    <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move up"><i class="fas fa-chevron-up"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($i < count($posts) - 1): ?>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="move_down" />
                    <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Move down"><i class="fas fa-chevron-down"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>

              <td>
                <div style="display:flex;gap:6px">
                  <a href="blog-form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <a href="../blog-post.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>')">
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
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Post</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this post?</p>
    <div class="modal-btns">
      <button class="btn btn-secondary" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
      <form method="post" style="display:inline">
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
  document.getElementById('deleteMsg').textContent = 'Delete "' + label + '"? This cannot be undone.';
  document.getElementById('deleteModal').classList.add('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>
