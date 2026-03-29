<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

/* status update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'status' && !empty($_POST['app_id'])) {
        dgtec_career_application_status((int)$_POST['app_id'], $_POST['status'] ?? 'new');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }
    if ($action === 'delete' && !empty($_POST['app_id'])) {
        dgtec_db()->prepare("DELETE FROM career_applications WHERE id=?")->execute([(int)$_POST['app_id']]);
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }
}

$careerId = isset($_GET['career_id']) ? (int)$_GET['career_id'] : 0;
$viewId   = isset($_GET['view'])      ? (int)$_GET['view']      : 0;

/* single application view */
$viewApp = null;
if ($viewId) {
    $viewApp = dgtec_career_application_get($viewId);
    if ($viewApp && $viewApp['status'] === 'new') {
        dgtec_career_application_status($viewId, 'reviewed');
        $viewApp['status'] = 'reviewed';
    }
}

$career = $careerId ? dgtec_career_get($careerId) : null;
$apps   = $careerId ? dgtec_career_applications($careerId) : dgtec_career_applications_all();
$allJobs = dgtec_careers_all();

$statusColors = ['new'=>'#dc2626','reviewed'=>'#d97706','shortlisted'=>'#2563eb','rejected'=>'#6b7280','hired'=>'#16a34a'];
$statusLabels = ['new'=>'New','reviewed'=>'Reviewed','shortlisted'=>'Shortlisted','rejected'=>'Rejected','hired'=>'Hired'];

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'careers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Applications – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    .app-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:900; align-items:center; justify-content:center; }
    .app-modal-overlay.open { display:flex; }
    .app-modal { background:#fff; border-radius:14px; width:680px; max-width:calc(100vw - 32px); max-height:88vh; overflow-y:auto; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,.2); }
    .app-modal h2 { font-size:18px; font-weight:700; margin-bottom:4px; }
    .app-modal .meta { font-size:13px; color:var(--gray); margin-bottom:20px; }
    .app-field { margin-bottom:16px; }
    .app-field label { font-size:12px; font-weight:700; color:var(--gray); text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:4px; }
    .app-field .val  { font-size:14px; color:var(--dark); word-break:break-word; }
    .app-field .val a { color:var(--btn); text-decoration:underline; }
    .status-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; color:#fff; }
    .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
    .filter-bar select, .filter-bar input { padding:7px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13px; }
    .filter-bar select:focus, .filter-bar input:focus { border-color:var(--btn); outline:none; }
  </style>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Applications <span>– Careers</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <div class="page-header">
        <div>
          <h1><?= $career ? htmlspecialchars($career['title']) . ' — Applications' : 'All Applications' ?></h1>
          <p><?= count($apps) ?> application<?= count($apps) !== 1 ? 's' : '' ?></p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <?php if ($careerId): ?>
          <a href="career-applications.php" class="btn btn-secondary"><i class="fas fa-list"></i> All Jobs</a>
          <a href="career-export.php?career_id=<?= $careerId ?>" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export This Job</a>
          <?php else: ?>
          <a href="career-export.php?career_id=all" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export All</a>
          <?php endif; ?>
          <a href="careers.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
        </div>
      </div>

      <!-- Filter bar -->
      <div class="filter-bar">
        <select id="filterJob" onchange="applyFilter()">
          <option value="">All Jobs</option>
          <?php foreach ($allJobs as $j): ?>
          <option value="<?= $j['id'] ?>" <?= $careerId == $j['id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['title']) ?></option>
          <?php endforeach; ?>
        </select>
        <select id="filterStatus" onchange="applyFilter()">
          <option value="">All Statuses</option>
          <?php foreach ($statusLabels as $k => $v): ?>
          <option value="<?= $k ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" id="filterSearch" placeholder="Search name / email…" oninput="applyFilter()" />
      </div>

      <!-- Applications table -->
      <div class="card">
        <div class="card-body" style="padding:0">
          <?php if (empty($apps)): ?>
          <div style="padding:48px;text-align:center;color:var(--gray)">
            <i class="fas fa-inbox" style="font-size:40px;margin-bottom:12px;display:block;color:#d1d5db"></i>
            No applications yet.
          </div>
          <?php else: ?>
          <table class="admin-table" id="appsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Applicant</th>
                <th>Job</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($apps as $app):
                $appData = json_decode($app['data_json'], true) ?: [];
                $appFiles = json_decode($app['files_json'], true) ?: [];
                $name  = $appData['full_name'] ?? $appData['name'] ?? array_values($appData)[0] ?? '—';
                $email = $appData['email'] ?? '';
                $color = $statusColors[$app['status']] ?? '#6b7280';
              ?>
              <tr data-job="<?= $app['career_id'] ?>" data-status="<?= $app['status'] ?>" data-search="<?= htmlspecialchars(strtolower($name . ' ' . $email)) ?>">
                <td><?= (int)$app['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($name) ?></strong>
                  <?php if ($email): ?><div style="font-size:12px;color:var(--gray)"><?= htmlspecialchars($email) ?></div><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($app['career_title'] ?? '—') ?></td>
                <td style="font-size:13px;color:var(--gray)"><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                <td>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="status" />
                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>" />
                    <select name="status" onchange="this.form.submit()" style="border:none;background:<?= $color ?>22;color:<?= $color ?>;font-size:12px;font-weight:700;border-radius:20px;padding:3px 10px;cursor:pointer">
                      <?php foreach ($statusLabels as $k => $v): ?>
                      <option value="<?= $k ?>" <?= $app['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td>
                  <div class="table-actions">
                    <button type="button" class="btn btn-secondary btn-sm"
                            onclick="viewApp(<?= htmlspecialchars(json_encode($app), ENT_QUOTES) ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this application?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="app_id" value="<?= $app['id'] ?>" />
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

<!-- View Application Modal -->
<div class="app-modal-overlay" id="appModalOverlay" onclick="if(event.target===this)closeModal()">
  <div class="app-modal" id="appModalBody"></div>
</div>

<script>
var statusColors = <?= json_encode($statusColors) ?>;
var statusLabels = <?= json_encode($statusLabels) ?>;

function viewApp(app) {
  var data  = {};
  var files = {};
  try { data  = JSON.parse(app.data_json);  } catch(e){}
  try { files = JSON.parse(app.files_json); } catch(e){}

  var html = '<div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:16px">' +
    '<div><h2>' + escH(app.career_title||'Application') + '</h2>' +
    '<div class="meta">Submitted: ' + app.created_at + ' &nbsp;|&nbsp; IP: ' + escH(app.ip) + '</div></div>' +
    '<div style="display:flex;gap:8px;align-items:center">' +
    '<a href="career-export.php?app_id='+app.id+'" class="btn btn-secondary btn-sm" style="white-space:nowrap"><i class="fas fa-download"></i> CSV</a>' +
    '<button onclick="closeModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280"><i class="fas fa-xmark"></i></button>' +
    '</div></div>';

  /* status badge */
  var c = statusColors[app.status] || '#6b7280';
  html += '<div style="margin-bottom:20px"><span class="status-badge" style="background:'+c+'">'+escH(statusLabels[app.status]||app.status)+'</span></div>';

  /* fields */
  for (var key in data) {
    html += '<div class="app-field"><label>'+escH(key.replace(/_/g,' '))+'</label>' +
            '<div class="val">'+escH(String(data[key]))+'</div></div>';
  }

  /* files */
  for (var fkey in files) {
    var fpath = files[fkey];
    html += '<div class="app-field"><label>'+escH(fkey.replace(/_/g,' '))+' (file)</label>' +
            '<div class="val"><a href="../'+escH(fpath)+'" target="_blank"><i class="fas fa-download"></i> Download / View</a></div></div>';
  }

  document.getElementById('appModalBody').innerHTML = html;
  document.getElementById('appModalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('appModalOverlay').classList.remove('open');
}

function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ---- Filter ---- */
function applyFilter() {
  var job    = document.getElementById('filterJob').value;
  var status = document.getElementById('filterStatus').value;
  var search = document.getElementById('filterSearch').value.toLowerCase();
  var rows   = document.querySelectorAll('#appsTable tbody tr');
  rows.forEach(function(row){
    var show = true;
    if (job    && row.dataset.job    !== job)    show = false;
    if (status && row.dataset.status !== status) show = false;
    if (search && row.dataset.search.indexOf(search) === -1) show = false;
    row.style.display = show ? '' : 'none';
  });
}

document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModal(); });
</script>
</body>
</html>
