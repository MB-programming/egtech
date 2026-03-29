<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && !empty($_POST['id'])) {
        dgtec_career_delete((int)$_POST['id']);
        header('Location: careers.php?deleted=1'); exit;
    }
    if ($action === 'toggle' && !empty($_POST['id'])) {
        $c = dgtec_career_get((int)$_POST['id']);
        if ($c) {
            dgtec_career_save(array_merge($c, ['is_active' => $c['is_active'] ? 0 : 1]));
        }
        header('Location: careers.php'); exit;
    }
}

$careers     = dgtec_careers_all();
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'careers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Careers – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Careers <span>Management</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">Job deleted.</div>
      <?php endif; ?>
      <?php if (isset($_GET['saved'])): ?>
      <div class="alert alert-success">Job saved.</div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1>Job Postings</h1>
          <p>Manage open positions and their application forms.</p>
        </div>
        <a href="career-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Job</a>
      </div>

      <div class="card">
        <div class="card-body" style="padding:0">
          <?php if (empty($careers)): ?>
          <div style="padding:48px;text-align:center;color:var(--gray)">
            <i class="fas fa-briefcase" style="font-size:40px;margin-bottom:12px;display:block;color:#d1d5db"></i>
            No job postings yet. <a href="career-form.php">Add the first one.</a>
          </div>
          <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Department</th>
                <th>Location</th>
                <th>Type</th>
                <th>Applications</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($careers as $job): ?>
              <?php
                $appCount = count(dgtec_career_applications((int)$job['id']));
                $newCount = (int)dgtec_db()->prepare("SELECT COUNT(*) FROM career_applications WHERE career_id=? AND status='new'")->execute([$job['id']]) ? 0 : 0;
                $stmt = dgtec_db()->prepare("SELECT COUNT(*) FROM career_applications WHERE career_id=? AND status='new'");
                $stmt->execute([$job['id']]);
                $newCount = (int)$stmt->fetchColumn();
              ?>
              <tr>
                <td><?= (int)$job['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($job['title']) ?></strong>
                  <div style="font-size:12px;color:var(--gray)"><?= htmlspecialchars($job['slug']) ?></div>
                </td>
                <td><?= htmlspecialchars($job['department']) ?></td>
                <td><?= htmlspecialchars($job['location']) ?></td>
                <td><?= htmlspecialchars($job['job_type']) ?></td>
                <td>
                  <a href="career-applications.php?career_id=<?= $job['id'] ?>" style="font-weight:600;color:var(--btn)">
                    <?= $appCount ?> total
                    <?php if ($newCount > 0): ?>
                    <span style="margin-left:4px;background:#dc2626;color:#fff;border-radius:20px;padding:1px 7px;font-size:11px"><?= $newCount ?> new</span>
                    <?php endif; ?>
                  </a>
                </td>
                <td>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle" />
                    <input type="hidden" name="id" value="<?= $job['id'] ?>" />
                    <button type="submit" class="badge <?= $job['is_active'] ? 'badge-success' : 'badge-secondary' ?>"
                            style="border:none;cursor:pointer;font-size:12px;padding:3px 10px;border-radius:20px">
                      <?= $job['is_active'] ? 'Active' : 'Hidden' ?>
                    </button>
                  </form>
                </td>
                <td>
                  <div class="table-actions">
                    <a href="career-form.php?id=<?= $job['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i></a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this job?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?= $job['id'] ?>" />
                      <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
