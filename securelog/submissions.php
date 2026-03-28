<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$msg     = '';
$msgType = 'success';

/* ---- Handle POST actions ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'mark_read' && $id) {
        dgtec_submission_mark_read($id, 1);
        $msg = 'Submission marked as read.';
    } elseif ($action === 'mark_unread' && $id) {
        dgtec_submission_mark_read($id, 0);
        $msg = 'Submission marked as unread.';
    } elseif ($action === 'delete' && $id) {
        dgtec_submission_delete($id);
        $msg = 'Submission deleted.';
    }
}

$submissions  = dgtec_submissions_all();
$unreadCount  = dgtec_submissions_unread_count();
$totalCount   = count($submissions);
$activePage   = 'submissions';

function fmt_date(string $dt): string {
    if (!$dt) return '—';
    try {
        $d = new DateTime($dt);
        return $d->format('d M Y, H:i');
    } catch (Exception $e) {
        return htmlspecialchars($dt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Submissions – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    /* ---- Submissions-specific styles ---- */
    .stats-bar {
      display: flex;
      gap: 16px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .stat-card {
      flex: 1;
      min-width: 140px;
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 18px 22px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }
    .stat-icon.blue  { background: rgba(24,63,150,.12); color: var(--p); }
    .stat-icon.orange { background: rgba(220,80,40,.12); color: #dc5028; }
    .stat-number { font-size: 26px; font-weight: 800; color: var(--dark); line-height: 1; }
    .stat-label  { font-size: 12px; color: var(--gray); margin-top: 3px; text-transform: uppercase; letter-spacing: .5px; }

    /* Table */
    .sub-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .sub-table th {
      background: var(--bg);
      color: var(--gray);
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      padding: 10px 14px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    .sub-table td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }
    .sub-table tr:last-child td { border-bottom: none; }
    .sub-table tr.unread td { background: rgba(24,63,150,.035); }
    .sub-table tr:hover td { background: rgba(24,63,150,.05); }

    /* Badges */
    .badge-read   { background: #d1fae5; color: #065f46; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
    .badge-unread { background: #fee2e2; color: #991b1b; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 700; }

    /* Action buttons */
    .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }

    /* View Modal */
    .view-modal-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.55);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .view-modal-backdrop.open { display: flex; }
    .view-modal {
      background: var(--white);
      border-radius: 14px;
      width: 100%;
      max-width: 560px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,.25);
      padding: 32px;
      position: relative;
    }
    .view-modal h3 {
      margin: 0 0 20px;
      font-size: 18px;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .view-modal-close {
      position: absolute;
      top: 18px;
      right: 20px;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: var(--gray);
      line-height: 1;
    }
    .view-modal-close:hover { color: var(--dark); }
    .detail-row {
      display: grid;
      grid-template-columns: 120px 1fr;
      gap: 6px 12px;
      padding: 10px 0;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--gray); font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: .5px; padding-top: 2px; }
    .detail-value { color: var(--dark); word-break: break-word; white-space: pre-wrap; }
    .view-modal-btns { display: flex; justify-content: flex-end; margin-top: 22px; gap: 10px; }

    /* Truncated message */
    .msg-truncate { max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--gray); }

    /* Empty state */
    .empty-state { text-align: center; padding: 64px 24px; color: var(--gray); }
    .empty-state i { font-size: 48px; opacity: .25; display: block; margin-bottom: 16px; }
    .empty-state p { font-size: 14px; margin: 0; }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Contact <span>Submissions</span></div>
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
          <h1>Contact Submissions</h1>
          <p>Messages sent via the contact form on the website.</p>
        </div>
        <a href="submissions-export.php" class="btn btn-secondary" title="Download as Excel/CSV">
          <i class="fas fa-file-excel"></i> Export Excel
        </a>
      </div>

      <!-- Stats bar -->
      <div class="stats-bar">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="stat-number"><?= $totalCount ?></div>
            <div class="stat-label">Total Submissions</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="fas fa-envelope-open"></i></div>
          <div>
            <div class="stat-number"><?= $unreadCount ?></div>
            <div class="stat-label">Unread</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Messages (<?= $totalCount ?>)</h2>
        </div>

        <?php if (empty($submissions)): ?>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>No submissions yet. Messages from your contact form will appear here.</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="sub-table">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Service</th>
              <th>Message</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($submissions as $i => $s): ?>
          <?php
            $truncMsg = mb_strlen($s['message'] ?? '') > 80
              ? mb_substr($s['message'], 0, 80) . '…'
              : ($s['message'] ?? '');
            $isUnread = !(int)$s['is_read'];
          ?>
            <tr class="<?= $isUnread ? 'unread' : '' ?>">
              <td style="color:var(--gray);font-weight:700"><?= $i + 1 ?></td>
              <td style="font-weight:<?= $isUnread ? '700' : '400' ?>">
                <?= htmlspecialchars($s['name'] ?? '') ?>
              </td>
              <td>
                <a href="mailto:<?= htmlspecialchars($s['email'] ?? '') ?>" style="color:var(--p)">
                  <?= htmlspecialchars($s['email'] ?? '') ?>
                </a>
              </td>
              <td><?= htmlspecialchars($s['mobile'] ?? '') ?></td>
              <td><?= htmlspecialchars($s['service'] ?? '') ?></td>
              <td>
                <div class="msg-truncate" title="<?= htmlspecialchars($s['message'] ?? '') ?>">
                  <?= htmlspecialchars($truncMsg) ?>
                </div>
              </td>
              <td style="white-space:nowrap;color:var(--gray);font-size:12px"><?= fmt_date($s['created_at'] ?? '') ?></td>
              <td>
                <?php if ($isUnread): ?>
                <span class="badge-unread"><i class="fas fa-circle" style="font-size:7px;vertical-align:middle"></i> Unread</span>
                <?php else: ?>
                <span class="badge-read"><i class="fas fa-check" style="font-size:9px;vertical-align:middle"></i> Read</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="actions-cell">
                  <!-- View -->
                  <button class="btn btn-secondary btn-sm"
                    onclick="openViewModal(<?= htmlspecialchars(json_encode([
                      'name'       => $s['name'] ?? '',
                      'email'      => $s['email'] ?? '',
                      'mobile'     => $s['mobile'] ?? '',
                      'service'    => $s['service'] ?? '',
                      'message'    => $s['message'] ?? '',
                      'ip_address' => $s['ip_address'] ?? '',
                      'created_at' => fmt_date($s['created_at'] ?? ''),
                      'id'         => (int)$s['id'],
                      'is_read'    => (int)$s['is_read'],
                    ]), ENT_QUOTES) ?>)">
                    <i class="fas fa-eye"></i> View
                  </button>

                  <!-- Mark Read / Unread -->
                  <?php if ($isUnread): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="mark_read" />
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-sm" title="Mark as read">
                      <i class="fas fa-envelope-open"></i>
                    </button>
                  </form>
                  <?php else: ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="mark_unread" />
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>" />
                    <button type="submit" class="btn btn-secondary btn-sm" title="Mark as unread">
                      <i class="fas fa-envelope"></i>
                    </button>
                  </form>
                  <?php endif; ?>

                  <!-- Delete -->
                  <button class="btn btn-danger btn-sm"
                    onclick="confirmDeleteSub(<?= (int)$s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'] ?? 'this submission')) ?>')">
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

<!-- View Message Modal -->
<div class="view-modal-backdrop" id="viewModal">
  <div class="view-modal">
    <button class="view-modal-close" onclick="closeViewModal()" title="Close">&times;</button>
    <h3><i class="fas fa-envelope" style="color:var(--p)"></i> Message Details</h3>
    <div id="viewModalContent"></div>
    <div class="view-modal-btns">
      <form method="post" id="viewMarkForm" style="display:inline">
        <input type="hidden" name="id" id="viewMarkId" value="" />
        <input type="hidden" name="action" id="viewMarkAction" value="" />
        <button type="submit" class="btn btn-secondary" id="viewMarkBtn">Mark as Read</button>
      </form>
      <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
    </div>
  </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-backdrop" id="deleteModal">
  <div class="modal">
    <h3><i class="fas fa-triangle-exclamation" style="color:#dc2626"></i> Delete Submission</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this submission?</p>
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
/* ---- View Modal ---- */
function openViewModal(data) {
  var html = '';
  var fields = [
    ['Name',       data.name],
    ['Email',      data.email],
    ['Mobile',     data.mobile],
    ['Service',    data.service],
    ['Message',    data.message],
    ['IP Address', data.ip_address],
    ['Date',       data.created_at],
  ];
  fields.forEach(function(f) {
    html += '<div class="detail-row">'
          + '<div class="detail-label">' + f[0] + '</div>'
          + '<div class="detail-value">' + escHtml(f[1] || '—') + '</div>'
          + '</div>';
  });
  document.getElementById('viewModalContent').innerHTML = html;

  // Mark read/unread button
  var btn = document.getElementById('viewMarkBtn');
  var act = document.getElementById('viewMarkAction');
  document.getElementById('viewMarkId').value = data.id;
  if (data.is_read) {
    act.value = 'mark_unread';
    btn.textContent = 'Mark as Unread';
  } else {
    act.value = 'mark_read';
    btn.textContent = 'Mark as Read';
  }

  document.getElementById('viewModal').classList.add('open');
}

function closeViewModal() {
  document.getElementById('viewModal').classList.remove('open');
}

document.getElementById('viewModal').addEventListener('click', function(e) {
  if (e.target === this) closeViewModal();
});

/* ---- Delete Modal ---- */
function confirmDeleteSub(id, name) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteMsg').textContent = 'Delete submission from "' + name + '"? This cannot be undone.';
  document.getElementById('deleteModal').classList.add('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});

/* ---- Escape HTML helper ---- */
function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
    .replace(/\n/g, '<br>');
}
</script>
</body>
</html>
