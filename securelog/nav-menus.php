<?php
/**
 * DGTEC Admin — Navigation Menus Editor
 * Header menu supports top-level items + dropdown children.
 * Footer supports column groups + links.
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();

$activePage  = 'nav-menus';
$unreadCount = dgtec_submissions_unread_count();
$msg         = '';
$msgType     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $which = $_POST['nav_which'] ?? '';

    if ($which === 'header') {
        $arr = json_decode($_POST['header_nav_json'] ?? '[]', true);
        if (!is_array($arr)) $arr = [];

        $clean  = [];
        $nextId = 1;
        foreach ($arr as $item) {
            $type = in_array($item['type'] ?? '', ['link','solutions_auto','services_auto'], true)
                  ? $item['type'] : 'link';

            /* Sanitize children */
            $children = [];
            foreach ((array)($item['children'] ?? []) as $child) {
                $children[] = [
                    'label' => sanitize_str($child['label'] ?? '', 100),
                    'url'   => sanitize_url($child['url'] ?? '#'),
                    'icon'  => sanitize_str($child['icon'] ?? '', 60),
                ];
            }

            $clean[] = [
                'id'       => $nextId++,
                'label'    => sanitize_str($item['label'] ?? '', 100),
                'url'      => sanitize_url($item['url'] ?? '#'),
                'type'     => $type,
                'target'   => ($item['target'] ?? '') === '_blank' ? '_blank' : '_self',
                'children' => $children,
            ];
        }

        dgtec_db()->prepare("UPDATE `site_info` SET `header_nav_json` = ? WHERE `id` = 1")
                  ->execute([json_encode($clean)]);
        $msg     = 'Header navigation saved.';
        $msgType = 'success';

    } elseif ($which === 'footer') {
        $arr = json_decode($_POST['footer_nav_json'] ?? '[]', true);
        if (!is_array($arr)) $arr = [];

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

        dgtec_db()->prepare("UPDATE `site_info` SET `footer_nav_json` = ? WHERE `id` = 1")
                  ->execute([json_encode($clean)]);
        $msg     = 'Footer navigation saved.';
        $msgType = 'success';
    }
}

$headerNav = dgtec_header_nav();
$footerNav = dgtec_footer_nav();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Nav Menus — DGTEC Admin</title>
<link rel="stylesheet" href="assets/admin.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
<style>
/* ── Tab navigation ── */
.nav-tabs { display:flex;gap:4px;border-bottom:2px solid var(--border);margin-bottom:24px }
.nav-tab  { padding:10px 22px;border:none;background:none;font-size:13px;font-weight:600;
            color:var(--gray);cursor:pointer;border-bottom:3px solid transparent;
            margin-bottom:-2px;transition:.15s;display:inline-flex;align-items:center;gap:7px }
.nav-tab.active { color:var(--p);border-bottom-color:var(--p) }
.nav-tab:hover:not(.active) { color:var(--dark) }
.nav-panel { display:none }
.nav-panel.active { display:block }

/* ── Top-level item row ── */
.nav-item-wrap { border:1px solid var(--border);border-radius:10px;margin-bottom:12px;overflow:hidden;background:var(--white) }
.nav-item-row  { display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--white) }
.nav-item-row:hover { background:var(--bg) }
.drag-handle   { color:#9ca3af;cursor:grab;font-size:16px;flex-shrink:0 }
.drag-handle:active { cursor:grabbing }
.nav-item-row input[type=text],
.nav-item-row select { border:1px solid var(--border);border-radius:6px;padding:7px 10px;font-size:13px;color:var(--dark) }
.nav-item-row input[type=text]:focus,
.nav-item-row select:focus { outline:none;border-color:var(--p);box-shadow:0 0 0 2px #dbeafe }
.input-label { flex:1.5 }
.input-url   { flex:2 }
.input-type  { width:140px }
.input-target{ width:90px }
.icon-btn    { background:none;border:none;cursor:pointer;font-size:14px;padding:5px 7px;border-radius:6px;transition:.15s }
.icon-btn.del { color:#ef4444 } .icon-btn.del:hover { background:#fee2e2 }
.icon-btn.expand { color:#6b7280 } .icon-btn.expand:hover { background:#f3f4f6;color:var(--p) }
.children-badge { font-size:10px;font-weight:700;background:#e0e7ff;color:#3730a3;
                  border-radius:20px;padding:2px 7px;flex-shrink:0 }

/* ── Children section ── */
.children-wrap { border-top:1px solid var(--border);background:#fafafa;padding:10px 14px 14px }
.children-list { list-style:none;margin:0;padding:0 }
.child-item    { display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px dashed var(--border) }
.child-item:last-child { border-bottom:none }
.child-item input { flex:1;border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:12px;color:var(--dark) }
.child-item input.icon-field { flex:0 0 160px }
.child-item input:focus { outline:none;border-color:var(--p) }

/* ── Footer groups ── */
.footer-group { border:1px solid var(--border);border-radius:10px;margin-bottom:14px;overflow:hidden;background:var(--white) }
.footer-group-header { display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--bg);border-bottom:1px solid var(--border) }
.footer-group-header input { flex:1;border:1px solid var(--border);border-radius:6px;padding:7px 10px;font-size:13px }
.footer-group-links { padding:8px 14px 12px }
.footer-link-item { display:flex;align-items:center;gap:8px;margin-bottom:8px }
.footer-link-item input { flex:1;border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px }

/* ── Buttons ── */
.add-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 15px;border-radius:7px;
           border:1.5px dashed #93c5fd;background:#eff6ff;color:var(--p);font-size:13px;
           font-weight:500;cursor:pointer;transition:.15s;margin-top:10px }
.add-btn:hover { background:#dbeafe;border-color:#60a5fa }
.save-btn { display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:var(--btn);
            color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;
            cursor:pointer;margin-top:18px;transition:.15s }
.save-btn:hover { background:var(--btn-hover,#1d4ed8) }

.hint-text { font-size:11px;color:var(--gray);margin-top:4px }
</style>
</head>
<body class="admin-body">
<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <div class="topbar-title">Navigation <span>Menus</span></div>
    <div class="topbar-user">
      <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
      <?= htmlspecialchars(admin_current_user()) ?>
    </div>
  </div>

  <div class="admin-content">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?>" style="margin-bottom:20px"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="nav-tabs">
      <button class="nav-tab active" data-tab="header"><i class="fas fa-bars"></i> Header Menu</button>
      <button class="nav-tab" data-tab="footer"><i class="fas fa-table-columns"></i> Footer Columns</button>
    </div>

    <!-- ══════════ HEADER NAV ══════════ -->
    <div class="nav-panel active" id="panel-header">
      <div class="card">
        <div class="card-header">
          <h2>Header Navigation</h2>
        </div>
        <div class="card-body">
          <p class="hint-text" style="margin-bottom:16px">
            Drag <i class="fas fa-grip-vertical"></i> to reorder. Add dropdown items with <i class="fas fa-chevron-down"></i>.
            Use type <code>solutions_auto</code> / <code>services_auto</code> to auto-populate dropdowns from the DB.
          </p>

          <form method="post" id="headerNavForm">
            <?= csrf_field() ?>
            <input type="hidden" name="nav_which" value="header" />
            <input type="hidden" name="header_nav_json" id="headerNavJson" />

            <div id="headerList"><!-- filled by JS --></div>

            <button type="button" class="add-btn" id="addHeaderItem">
              <i class="fas fa-plus"></i> Add Menu Item
            </button>

            <br />
            <button type="submit" class="save-btn">
              <i class="fas fa-save"></i> Save Header Menu
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ══════════ FOOTER NAV ══════════ -->
    <div class="nav-panel" id="panel-footer">
      <div class="card">
        <div class="card-header">
          <h2>Footer Navigation Columns</h2>
        </div>
        <div class="card-body">
          <p class="hint-text" style="margin-bottom:16px">Each column has a heading and a list of links.</p>

          <form method="post" id="footerNavForm">
            <?= csrf_field() ?>
            <input type="hidden" name="nav_which" value="footer" />
            <input type="hidden" name="footer_nav_json" id="footerNavJson" />

            <div id="footerGroups"><!-- filled by JS --></div>

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
    </div>

  </div><!-- /.admin-content -->
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js" crossorigin="anonymous"></script>
<script>
/* ── Initial data ── */
var headerData = <?= json_encode($headerNav, JSON_UNESCAPED_UNICODE) ?>;
var footerData = <?= json_encode($footerNav, JSON_UNESCAPED_UNICODE) ?>;

/* ── Tabs ── */
document.querySelectorAll('.nav-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.nav-tab').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.nav-panel').forEach(function(p){ p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
});

/* ═══════════════════════════════════════════
   HEADER NAV
════════════════════════════════════════════ */
var headerList = document.getElementById('headerList');

function renderHeaderItem(item) {
    var wrap = document.createElement('div');
    wrap.className = 'nav-item-wrap';

    var childCount  = (item.children || []).length;
    var childBadge  = childCount > 0 ? '<span class="children-badge">' + childCount + ' sub-items</span>' : '';
    var typeOptions = ['link','solutions_auto','services_auto'].map(function(t) {
        return '<option value="' + t + '"' + (item.type === t ? ' selected' : '') + '>' + t + '</option>';
    }).join('');
    var targetOpts  = ['_self','_blank'].map(function(t) {
        return '<option value="' + t + '"' + (item.target === t ? ' selected' : '') + '>' + t + '</option>';
    }).join('');

    wrap.innerHTML =
        '<div class="nav-item-row">' +
            '<span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
            '<input type="text" class="item-label input-label" placeholder="Label" value="' + escHtml(item.label) + '" />' +
            '<input type="text" class="item-url input-url" placeholder="URL (e.g. about.php)" value="' + escHtml(item.url) + '" />' +
            '<select class="item-type input-type">' + typeOptions + '</select>' +
            '<select class="item-target input-target">' + targetOpts + '</select>' +
            childBadge +
            '<button type="button" class="icon-btn expand" title="Edit dropdown children"><i class="fas fa-chevron-down"></i></button>' +
            '<button type="button" class="icon-btn del" title="Remove item"><i class="fas fa-trash"></i></button>' +
        '</div>' +
        '<div class="children-wrap" style="display:none">' +
            '<p style="font-size:11px;color:var(--gray);margin:0 0 8px"><i class="fas fa-sitemap"></i> Dropdown children (shown in sub-menu)</p>' +
            '<ul class="children-list"></ul>' +
            '<button type="button" class="add-btn add-child-btn" style="margin-top:6px">' +
                '<i class="fas fa-plus"></i> Add Child Item' +
            '</button>' +
        '</div>';

    /* Toggle children panel */
    var expandBtn    = wrap.querySelector('.icon-btn.expand');
    var childrenWrap = wrap.querySelector('.children-wrap');
    expandBtn.addEventListener('click', function() {
        var open = childrenWrap.style.display !== 'none';
        childrenWrap.style.display = open ? 'none' : 'block';
        expandBtn.querySelector('i').className = open ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
    });

    /* Remove item */
    wrap.querySelector('.icon-btn.del').addEventListener('click', function() { wrap.remove(); });

    /* Render existing children */
    var childList = wrap.querySelector('.children-list');
    (item.children || []).forEach(function(child) {
        childList.appendChild(renderChildItem(child));
    });

    /* Add child */
    wrap.querySelector('.add-child-btn').addEventListener('click', function() {
        childList.appendChild(renderChildItem({ label:'', url:'#', icon:'' }));
    });

    return wrap;
}

function renderChildItem(child) {
    var li = document.createElement('li');
    li.className = 'child-item';
    li.innerHTML =
        '<i class="fas fa-grip-dots drag-handle" style="color:#ccc;cursor:grab;font-size:12px"></i>' +
        '<input type="text" class="child-label" placeholder="Label" value="' + escHtml(child.label) + '" />' +
        '<input type="text" class="child-url" placeholder="URL" value="' + escHtml(child.url) + '" />' +
        '<input type="text" class="child-icon icon-field" placeholder="Icon class (e.g. fas fa-users)" value="' + escHtml(child.icon || '') + '" />' +
        '<button type="button" class="icon-btn del" title="Remove"><i class="fas fa-times"></i></button>';
    li.querySelector('.icon-btn.del').addEventListener('click', function() { li.remove(); });
    return li;
}

function rebuildHeaderList() {
    headerList.innerHTML = '';
    headerData.forEach(function(item) { headerList.appendChild(renderHeaderItem(item)); });
}
rebuildHeaderList();

Sortable.create(headerList, { handle: '.drag-handle', animation: 150 });

document.getElementById('addHeaderItem').addEventListener('click', function() {
    headerList.appendChild(renderHeaderItem({ id: Date.now(), label:'New Item', url:'#', type:'link', target:'_self', children:[] }));
});

document.getElementById('headerNavForm').addEventListener('submit', function() {
    var items = [];
    headerList.querySelectorAll('.nav-item-wrap').forEach(function(wrap) {
        var children = [];
        wrap.querySelectorAll('.children-list .child-item').forEach(function(li) {
            children.push({
                label : li.querySelector('.child-label').value,
                url   : li.querySelector('.child-url').value,
                icon  : li.querySelector('.child-icon').value,
            });
        });
        items.push({
            id       : Date.now(),
            label    : wrap.querySelector('.item-label').value,
            url      : wrap.querySelector('.item-url').value,
            type     : wrap.querySelector('.item-type').value,
            target   : wrap.querySelector('.item-target').value,
            children : children,
        });
    });
    document.getElementById('headerNavJson').value = JSON.stringify(items);
});

/* ═══════════════════════════════════════════
   FOOTER NAV
════════════════════════════════════════════ */
var footerGroups = document.getElementById('footerGroups');

function renderFooterGroup(group) {
    var div = document.createElement('div');
    div.className = 'footer-group';

    var linksHtml = (group.links || []).map(function(link) {
        return '<div class="footer-link-item">' +
            '<i class="fas fa-grip-dots" style="color:#ccc;cursor:grab;font-size:12px"></i>' +
            '<input type="text" class="link-label" placeholder="Label" value="' + escHtml(link.label) + '" />' +
            '<input type="text" class="link-url" placeholder="URL" value="' + escHtml(link.url) + '" />' +
            '<button type="button" class="icon-btn del"><i class="fas fa-times"></i></button>' +
        '</div>';
    }).join('');

    div.innerHTML =
        '<div class="footer-group-header">' +
            '<span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
            '<input type="text" class="group-title" placeholder="Column Title" value="' + escHtml(group.title) + '" />' +
            '<button type="button" class="icon-btn del" title="Remove column"><i class="fas fa-trash"></i></button>' +
        '</div>' +
        '<div class="footer-group-links">' +
            '<div class="link-list">' + linksHtml + '</div>' +
            '<button type="button" class="add-btn add-link-btn"><i class="fas fa-plus"></i> Add Link</button>' +
        '</div>';

    div.querySelector('.footer-group-header .icon-btn.del').addEventListener('click', function() { div.remove(); });

    div.querySelectorAll('.footer-link-item .icon-btn.del').forEach(function(btn) {
        btn.addEventListener('click', function() { btn.closest('.footer-link-item').remove(); });
    });

    div.querySelector('.add-link-btn').addEventListener('click', function() {
        var linkList = div.querySelector('.link-list');
        var item = document.createElement('div');
        item.className = 'footer-link-item';
        item.innerHTML =
            '<i class="fas fa-grip-dots" style="color:#ccc;cursor:grab;font-size:12px"></i>' +
            '<input type="text" class="link-label" placeholder="Label" />' +
            '<input type="text" class="link-url" placeholder="URL" />' +
            '<button type="button" class="icon-btn del"><i class="fas fa-times"></i></button>';
        item.querySelector('.icon-btn.del').addEventListener('click', function() { item.remove(); });
        linkList.appendChild(item);
    });

    return div;
}

function rebuildFooterGroups() {
    footerGroups.innerHTML = '';
    footerData.forEach(function(g) { footerGroups.appendChild(renderFooterGroup(g)); });
}
rebuildFooterGroups();

Sortable.create(footerGroups, { handle: '.drag-handle', animation: 150 });

document.getElementById('addFooterGroup').addEventListener('click', function() {
    footerGroups.appendChild(renderFooterGroup({ title:'New Column', links:[] }));
});

document.getElementById('footerNavForm').addEventListener('submit', function() {
    var groups = [];
    footerGroups.querySelectorAll('.footer-group').forEach(function(div) {
        var links = [];
        div.querySelectorAll('.footer-link-item').forEach(function(li) {
            links.push({ label: li.querySelector('.link-label').value, url: li.querySelector('.link-url').value });
        });
        groups.push({ title: div.querySelector('.group-title').value, links: links });
    });
    document.getElementById('footerNavJson').value = JSON.stringify(groups);
});

/* ── Utility ── */
function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
</body>
</html>
