<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id     = (int)($_GET['id'] ?? 0);
$link   = $id ? dgtec_social_link_get($id) : null;
$isEdit = !empty($link);
$errors = [];

$defaults = ['id'=>'','position'=>count(dgtec_social_links_all())+1,'is_active'=>1,'platform'=>'','icon'=>'','url'=>''];
$d = array_merge($defaults, $link ?? []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $d = [
        'id'        => $isEdit ? $id : null,
        'position'  => max(1, (int)($_POST['position'] ?? 1)),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'platform'  => sanitize_str($_POST['platform'] ?? '', 50),
        'icon'      => sanitize_str($_POST['icon']     ?? '', 100),
        'url'       => sanitize_url($_POST['url']      ?? ''),
    ];
    if (empty($d['platform'])) $errors[] = 'Platform name is required.';
    if (empty($d['url']))      $errors[] = 'URL is required.';
    if (empty($errors)) {
        dgtec_social_link_save($d);
        header('Location: social-media.php?saved=1');
        exit;
    }
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'social-media';
$pageTitle   = $isEdit ? 'Edit Social Link' : 'Add Social Link';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'Add' ?> <span>Social Link</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><strong>Fix the following:</strong>
        <ul style="margin-top:6px;padding-left:18px"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>

      <div class="page-header">
        <div><h1><?= htmlspecialchars($pageTitle) ?></h1></div>
        <a href="social-media.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <form method="post">
        <?= csrf_field() ?>
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-share-nodes" style="color:var(--acc)"></i> Link Details</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group">
                <label class="form-label">Platform Name *</label>
                <input type="text" name="platform" class="form-input"
                       value="<?= htmlspecialchars($d['platform']) ?>"
                       placeholder="e.g. LinkedIn, Twitter/X, Instagram" required />
              </div>

              <div class="form-group">
                <label class="form-label">Font Awesome Icon *</label>
                <input type="text" name="icon" id="iconInput" class="form-input"
                       value="<?= htmlspecialchars($d['icon']) ?>"
                       placeholder="e.g. fab fa-linkedin-in"
                       oninput="updateIconPreview()" />
                <div id="iconPreview" style="margin-top:8px;font-size:22px;color:var(--p);min-height:28px">
                  <?php if ($d['icon']): ?><i class="<?= htmlspecialchars($d['icon']) ?>"></i><?php endif; ?>
                </div>
              </div>

              <div class="form-group full">
                <label class="form-label">URL *</label>
                <input type="url" name="url" class="form-input"
                       value="<?= htmlspecialchars($d['url']) ?>"
                       placeholder="https://linkedin.com/company/dgtec" required />
              </div>

              <div class="form-group">
                <label class="form-label">Position</label>
                <input type="number" name="position" class="form-input"
                       value="<?= (int)$d['position'] ?>" min="1" />
              </div>

              <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:6px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px">
                  <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                         style="width:18px;height:18px;accent-color:var(--btn)" />
                  <span><strong>Active</strong> — visible on the website</span>
                </label>
              </div>

            </div>
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="social-media.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Link' : 'Save Link' ?>
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<script>
function updateIconPreview() {
  var cls = document.getElementById('iconInput').value.trim();
  document.getElementById('iconPreview').innerHTML = cls ? '<i class="' + cls + '"></i>' : '';
}
</script>
</body>
</html>
