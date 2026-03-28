<?php
/**
 * DGTEC Admin — Navigation Menus Editor
 * Manage header top-bar items and footer column groups.
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();

$activePage   = 'nav-menus';
$unreadCount  = dgtec_submissions_unread_count();
$msg          = '';
$msgType      = '';

/* ── POST: save header or footer nav ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $which = $_POST['nav_which'] ?? '';

    if ($which === 'header') {
        $raw = $_POST['header_nav_json'] ?? '[]';
        $arr = json_decode($raw, true);
        if (!is_array($arr)) $arr = [];

        /* Sanitize each item */
        $clean = [];
        $nextId = 1;
        foreach ($arr as $item) {
            $type = in_array($item['type'] ?? '', ['link','solutions_auto','services_auto'], true)
                  ? $item['type'] : 'link';
            $clean[] = [
                'id'       => $nextId++,
                'label'    => sanitize_str($item['label'] ?? '', 100),
                'url'      => sanitize_url($item['url'] ?? '#'),
                'type'     => $type,
                'target'   => ($item['target'] ?? '') === '_blank' ? '_blank' : '_self',
                'children' => [],
            ];
        }

        $db = dgtec_db();
        $db->prepare("UPDATE `site_info` SET `header_nav_json` = ? WHERE `id` = 1")
           ->execute([json_encode($clean)]);
        $msg = 'Header navigation saved.';
        $msgType = 'success';

    } elseif ($which === 'footer') {
        $raw = $_POST['footer_nav_json'] ?? '[]';
        $arr = json_decode($raw, true);
        if (!is_array($arr)) $arr = [];

        /* Sanitize each group and its links */
        $clean = [];
        foreach ($arr as $group) {
            $links = [];
            foreach ((array)($group['links'] ?? []) as $link) {
                $links[] = [
                    'label' => sanitize_str($link['label'] ?? '', 100),
                    'url'   => sanitize_url($link['url'] ?? '#'),
                ];
            }
            $clean[] = [
                'title' => sanitize_str($group['title'] ?? '', 100),
                'links' => $links,
            ];
        }

        $db = dgtec_db();
        $db->prepare("UPDATE `site_info` SET `footer_nav_json` = ? WHERE `id` = 1")
           ->execute([json_encode($clean)]);
        $msg = 'Footer navigation saved.';
        $msgType = 'success';
    }
}

/* ── Load current nav data ── */
$headerNav = dgtec_header_nav();
$footerNav = dgtec_footer_nav();

$headerNavJson = htmlspecialchars(json_encode($headerNav, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES);
$footerNavJson = htmlspecialchars(json_encode($footerNav, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Nav Menus — DGTEC Admin</title>
<link rel="stylesheet" href="../assets/css/admin.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
<style>
/* ── Nav editor styles ── */
.nav-tabs{display:flex;gap:8px;margin-bottom:24px}
.nav-tab{padding:8px 20px;border:1px solid #d1d5db;border-radius:8px;cursor:pointer;font-size:14px;background:#f9fafb;color:#374151;font-weight:500;transition:.15s}
.nav-tab.active{background:#2563eb;color:#fff;border-color:#2563eb}

.nav-panel{display:none}
.nav-panel.active{display:block}

/* Item list */
.nav-item-list{list-style:none;margin:0;padding:0;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden}
.nav-item{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fff;border-bottom:1px solid #f3f4f6;transition:background .15s}
.nav-item:last-child{border-bottom:0}
.nav-item:hover{background:#f9fafb}
.nav-item .drag-handle{color:#9ca3af;cursor:grab;font-size:16px;flex-shrink:0}
.nav-item .drag-handle:active{cursor:grabbing}
.nav-item input[type=text]{flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:6px 10px;font-size:13px;color:#111}
.nav-item input[type=text]:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 2px #dbeafe}
.nav-item select{border:1px solid #e5e7eb;border-radius:6px;padding:6px 10px;font-size:13px;color:#111}
.nav-item .del-btn{background:none;border:none;color:#ef4444;cursor:pointer;font-size:15px;padding:4px;flex-shrink:0}
.nav-item .del-btn:hover{color:#dc2626}

/* Footer groups */
.footer-group{border:1px solid #e5e7eb;border-radius:10px;margin-bottom:16px;overflow:hidden}
.footer-group-header{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb}
.footer-group-header input{flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:6px 10px;font-size:13px}
.footer-group-links{padding:8px 16px 12px}
.footer-link-item{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.footer-link-item input{flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:6px 10px;font-size:13px}

.add-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;border:1.5px dashed #93c5fd;background:#eff6ff;color:#2563eb;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;margin-top:10px}
.add-btn:hover{background:#dbeafe;border-color:#60a5fa}

.save-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;margin-top:18px;transition:.15s}
.save-btn:hover{background:#1d4ed8}

.badge-type{font-size:11px;font-weight:600;padding:2px 7px;border-radius:20px;background:#e0e7ff;color:#3730a3;flex-shrink:0}
</style>
</head>
<body class="admin-body">
<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <h1 class="admin-page-title">Navigation Menus</h1>
    <a href="../index.php" target="_blank" class="btn-secondary"><i class="fas fa-globe"></i> View Site</a>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msgType ?>" style="margin-bottom:20px"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- Tabs -->
  <div class="nav-tabs">
    <button class="nav-tab active" data-tab="header">
      <i class="fas fa-bars"></i> Header Menu
    </button>
    <button class="nav-tab" data-tab="footer">
      <i class="fas fa-table-columns"></i> Footer Columns
    </button>
  </div>

  <!-- ══════════ HEADER NAV PANEL ══════════ -->
  <div class="nav-panel active" id="panel-header">
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">Header Navigation Items</h2>
        <p style="margin:4px 0 0;color:#6b7280;font-size:13px">
          Drag to reorder. Type <em>solutions_auto</em> / <em>services_auto</em> to auto-populate dropdowns from the database.
        </p>
      </div>

      <form method="post" id="headerNavForm">
        <?= csrf_field() ?>
        <input type="hidden" name="nav_which" value="header" />
        <input type="hidden" name="header_nav_json" id="headerNavJson" />

        <ul class="nav-item-list" id="headerList">
          <!-- rendered by JS -->
        </ul>

        <button type="button" class="add-btn" id="addHeaderItem">
          <i class="fas fa-plus"></i> Add Item
        </button>

        <br />
        <button type="submit" class="save-btn">
          <i class="fas fa-save"></i> Save Header Menu
        </button>
      </form>
    </div>
  </div>

  <!-- ══════════ FOOTER NAV PANEL ══════════ -->
  <div class="nav-panel" id="panel-footer">
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">Footer Navigation Columns</h2>
        <p style="margin:4px 0 0;color:#6b7280;font-size:13px">
          Each column has a heading and a list of links.
        </p>
      </div>

      <form method="post" id="footerNavForm">
        <?= csrf_field() ?>
        <input type="hidden" name="nav_which" value="footer" />
        <input type="hidden" name="footer_nav_json" id="footerNavJson" />

        <div id="footerGroups">
          <!-- rendered by JS -->
        </div>

        <button type="button" class="add-btn" id="addFooterGroup">
          <i class="fas fa-plus"></i> Add Column
        </button>

        <br />
        <button type="submit" class="save-btn">
          <i class="fas fa-save"></i> Save Footer Menu
        </button>
      </form>
    </div>
  </div>
</main>

<!-- SortableJS for drag-and-drop reordering -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js" crossorigin="anonymous"></script>
<script>
/* ── Initial data from PHP ── */
var headerData = <?= json_encode($headerNav, JSON_UNESCAPED_UNICODE) ?>;
var footerData = <?= json_encode($footerNav, JSON_UNESCAPED_UNICODE) ?>;

/* ══════════════════════════ TABS ══════════════════════════ */
document.querySelectorAll('.nav-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.nav-tab').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.nav-panel').forEach(function(p){ p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
});

/* ══════════════════════════ HEADER NAV ══════════════════════ */
var headerList = document.getElementById('headerList');

function typeLabel(type) {
    if (type === 'solutions_auto') return '<span class="badge-type">solutions_auto</span>';
    if (type === 'services_auto') return '<span class="badge-type">services_auto</span>';
    return '';
}

function renderHeaderItem(item, idx) {
    var li = document.createElement('li');
    li.className = 'nav-item';
    li.dataset.idx = idx;
    li.innerHTML =
        '<span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
        '<input type="text" class="item-label" placeholder="Label" value="' + escHtml(item.label) + '" />' +
        '<input type="text" class="item-url" placeholder="URL (e.g. about.php)" value="' + escHtml(item.url) + '" />' +
        '<select class="item-type">' +
            '<option value="link"' + (item.type==='link'?' selected':'') + '>link</option>' +
            '<option value="solutions_auto"' + (item.type==='solutions_auto'?' selected':'') + '>solutions_auto</option>' +
            '<option value="services_auto"' + (item.type==='services_auto'?' selected':'') + '>services_auto</option>' +
        '</select>' +
        '<select class="item-target">' +
            '<option value="_self"' + (item.target==='_self'?' selected':'') + '>_self</option>' +
            '<option value="_blank"' + (item.target==='_blank'?' selected':'') + '>_blank</option>' +
        '</select>' +
        '<button type="button" class="del-btn" title="Remove"><i class="fas fa-trash"></i></button>';
    li.querySelector('.del-btn').addEventListener('click', function(){ li.remove(); });
    return li;
}

function rebuildHeaderList() {
    headerList.innerHTML = '';
    headerData.forEach(function(item, idx) {
        headerList.appendChild(renderHeaderItem(item, idx));
    });
}
rebuildHeaderList();

Sortable.create(headerList, { handle: '.drag-handle', animation: 150 });

document.getElementById('addHeaderItem').addEventListener('click', function() {
    var newItem = { id: Date.now(), label: 'New Item', url: '#', type: 'link', target: '_self', children: [] };
    headerList.appendChild(renderHeaderItem(newItem, headerList.children.length));
});

document.getElementById('headerNavForm').addEventListener('submit', function(e) {
    var items = [];
    headerList.querySelectorAll('.nav-item').forEach(function(li) {
        items.push({
            id:       Date.now(),
            label:    li.querySelector('.item-label').value,
            url:      li.querySelector('.item-url').value,
            type:     li.querySelector('.item-type').value,
            target:   li.querySelector('.item-target').value,
            children: []
        });
    });
    document.getElementById('headerNavJson').value = JSON.stringify(items);
});

/* ══════════════════════════ FOOTER NAV ══════════════════════ */
var footerGroups = document.getElementById('footerGroups');

function renderFooterGroup(group, gIdx) {
    var div = document.createElement('div');
    div.className = 'footer-group';
    div.dataset.gidx = gIdx;

    var linksHtml = (group.links || []).map(function(link, lIdx) {
        return '<div class="footer-link-item" data-lidx="' + lIdx + '">' +
            '<input type="text" class="link-label" placeholder="Label" value="' + escHtml(link.label) + '" />' +
            '<input type="text" class="link-url" placeholder="URL" value="' + escHtml(link.url) + '" />' +
            '<button type="button" class="del-btn" title="Remove link"><i class="fas fa-times"></i></button>' +
        '</div>';
    }).join('');

    div.innerHTML =
        '<div class="footer-group-header">' +
            '<span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
            '<input type="text" class="group-title" placeholder="Column Title" value="' + escHtml(group.title) + '" />' +
            '<button type="button" class="del-btn" title="Remove column"><i class="fas fa-trash"></i></button>' +
        '</div>' +
        '<div class="footer-group-links">' +
            '<div class="link-list">' + linksHtml + '</div>' +
            '<button type="button" class="add-btn add-link-btn" style="margin-top:6px">' +
                '<i class="fas fa-plus"></i> Add Link' +
            '</button>' +
        '</div>';

    /* Remove group */
    div.querySelector('.footer-group-header .del-btn').addEventListener('click', function() {
        div.remove();
    });

    /* Remove individual link */
    div.querySelectorAll('.footer-link-item .del-btn').forEach(function(btn) {
        btn.addEventListener('click', function(){ btn.closest('.footer-link-item').remove(); });
    });

    /* Add link */
    div.querySelector('.add-link-btn').addEventListener('click', function() {
        var linkList = div.querySelector('.link-list');
        var item = document.createElement('div');
        item.className = 'footer-link-item';
        item.innerHTML =
            '<input type="text" class="link-label" placeholder="Label" />' +
            '<input type="text" class="link-url" placeholder="URL" />' +
            '<button type="button" class="del-btn"><i class="fas fa-times"></i></button>';
        item.querySelector('.del-btn').addEventListener('click', function(){ item.remove(); });
        linkList.appendChild(item);
    });

    return div;
}

function rebuildFooterGroups() {
    footerGroups.innerHTML = '';
    footerData.forEach(function(group, gIdx) {
        footerGroups.appendChild(renderFooterGroup(group, gIdx));
    });
}
rebuildFooterGroups();

Sortable.create(footerGroups, { handle: '.drag-handle', animation: 150 });

document.getElementById('addFooterGroup').addEventListener('click', function() {
    footerGroups.appendChild(renderFooterGroup({ title: 'New Column', links: [] }, footerGroups.children.length));
});

document.getElementById('footerNavForm').addEventListener('submit', function(e) {
    var groups = [];
    footerGroups.querySelectorAll('.footer-group').forEach(function(div) {
        var links = [];
        div.querySelectorAll('.footer-link-item').forEach(function(li) {
            links.push({
                label: li.querySelector('.link-label').value,
                url:   li.querySelector('.link-url').value
            });
        });
        groups.push({
            title: div.querySelector('.group-title').value,
            links: links
        });
    });
    document.getElementById('footerNavJson').value = JSON.stringify(groups);
});

/* ── Utility ── */
function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
</body>
</html>
