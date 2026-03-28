<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_csrf_verify();

$msg     = '';
$msgType = 'success';

/* Known static pages */
$knownPages = [
    'index'                         => 'Homepage',
    'about'                         => 'About',
    'services'                      => 'Services',
    'solutions'                     => 'Solutions',
    'blog'                          => 'Blog Archive',
    'contact'                       => 'Contact',
    'service-recruitment'           => 'Service: Recruitment',
    'service-outsourcing'           => 'Service: Outsourcing',
    'service-digital-transformation'=> 'Service: Digital Transformation',
    'service-tech-squad'            => 'Service: Tech Squad',
    'service-data-handling'         => 'Service: Data Handling',
    'solution-digital-onboarding'   => 'Solution: Digital Onboarding',
    'solution-enterprise-automation'=> 'Solution: Enterprise Automation',
    'solution-tea-boy'              => 'Solution: Tea Boy',
];
/* Add blog post slugs */
foreach (dgtec_blogs_all() as $bp) {
    if ($bp['slug']) $knownPages['blog:' . $bp['slug']] = 'Blog: ' . mb_substr($bp['title'], 0, 50);
}
/* Add all services/solutions from DB */
foreach (dgtec_items_all('service') as $svc) {
    if ($svc['slug']) $knownPages[$svc['slug']] = 'Service: ' . mb_substr($svc['title'], 0, 50);
}
foreach (dgtec_items_all('solution') as $sol) {
    if ($sol['slug']) $knownPages[$sol['slug']] = 'Solution: ' . mb_substr($sol['title'], 0, 50);
}

$activeTab  = $_GET['tab'] ?? 'global';
$activePage = 'seo';
$info       = dgtec_site_info();
$unreadCount = dgtec_submissions_unread_count();

/* ---- SAVE GLOBAL ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'global') {
    $favicon = sanitize_str($_POST['current_favicon'] ?? $info['favicon'], 500);
    if (!empty($_POST['favicon_uploaded'])) $favicon = sanitize_str($_POST['favicon_uploaded'], 500);

    $upd = [
        'google_analytics' => sanitize_str($_POST['google_analytics'] ?? '', 100),
        'favicon'          => $favicon,
        'global_head_code' => sanitize_html($_POST['global_head_code'] ?? ''),
        'global_body_code' => sanitize_html($_POST['global_body_code'] ?? ''),
        /* keep existing nav/contact info */
        'phone'              => $info['phone'],
        'email'              => $info['email'],
        'address'            => $info['address'],
        'footer_description' => $info['footer_description'],
        'site_description'   => sanitize_str($_POST['site_description'] ?? '', 500),
        'header_logo'        => $info['header_logo'],
        'footer_logo'        => $info['footer_logo'],
    ];
    dgtec_site_info_save($upd);
    $msg = 'Global SEO settings saved.';
    $info = dgtec_site_info();
    $activeTab = 'global';
}

/* ---- SAVE PER-PAGE ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'page') {
    $pk = sanitize_str($_POST['page_key'] ?? '', 150);
    if ($pk && isset($knownPages[$pk])) {
        dgtec_seo_save($pk, [
            'meta_title'  => sanitize_str($_POST['meta_title'] ?? '', 255),
            'meta_desc'   => sanitize_str($_POST['meta_desc'] ?? '', 500),
            'og_title'    => sanitize_str($_POST['og_title'] ?? '', 255),
            'og_desc'     => sanitize_str($_POST['og_desc'] ?? '', 500),
            'og_image'    => sanitize_url($_POST['og_image'] ?? ''),
            'canonical'   => sanitize_url($_POST['canonical'] ?? ''),
            'robots'      => sanitize_str($_POST['robots'] ?? 'index, follow', 100),
            'schema_json' => sanitize_html($_POST['schema_json'] ?? ''),
            'head_code'   => sanitize_html($_POST['head_code'] ?? ''),
            'body_code'   => sanitize_html($_POST['body_code'] ?? ''),
        ]);
        $msg = 'SEO settings saved for: ' . htmlspecialchars($knownPages[$pk]);
        $activeTab = 'pages';
    }
}

$selectedPage = sanitize_str($_GET['page_key'] ?? array_key_first($knownPages), 150);
if (!isset($knownPages[$selectedPage])) $selectedPage = 'index';
$pageData = dgtec_seo_get($selectedPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SEO Settings – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    .tab-bar { display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid var(--border);padding-bottom:0; }
    .tab-btn { padding:10px 22px;border:none;background:none;font-size:13px;font-weight:600;color:var(--gray);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:color .2s,border-color .2s;border-radius:0; }
    .tab-btn.active { color:var(--p);border-bottom-color:var(--p); }
    .tab-btn:hover:not(.active) { color:var(--dark); }
    .tab-pane { display:none; }
    .tab-pane.active { display:block; }
    .code-textarea { font-family:'Courier New',monospace;font-size:12px;line-height:1.6;min-height:140px;resize:vertical; }
    .schema-textarea { font-family:'Courier New',monospace;font-size:12px;line-height:1.6;min-height:180px;resize:vertical; }
    .upload-progress { display:none;margin-top:8px;background:var(--bg);border-radius:6px;overflow:hidden;height:18px;position:relative;border:1px solid var(--border); }
    .upload-progress.active { display:block; }
    .upload-progress-bar { height:100%;background:linear-gradient(90deg,var(--p),var(--btn));width:0%;transition:width .2s ease; }
    .upload-progress-text { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--dark); }
    .upload-status { font-size:12px;color:var(--gray);margin-top:4px; }
    .upload-status.error   { color:#dc2626; }
    .upload-status.success { color:#16a34a; }
    .favicon-preview { display:inline-flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:10px; }
    .favicon-preview img { width:32px;height:32px;object-fit:contain; }
    /* OG image upload */
    .og-upload-wrap { display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:6px; }
    .og-preview { max-width:220px;max-height:120px;border-radius:6px;border:1px solid var(--border);display:none;object-fit:cover; }
    .og-preview.show { display:block; }
    .og-upload-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--white);
                     border:1.5px solid var(--border);border-radius:7px;cursor:pointer;font-size:13px;
                     color:var(--dark);transition:.15s; }
    .og-upload-btn:hover { border-color:var(--p);color:var(--p); }
    .page-select-bar { display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap; }
    .page-select-bar select { flex:1;min-width:260px; }
    .seo-score { display:inline-flex;align-items:center;gap:6px;font-size:12px;padding:4px 10px;border-radius:20px; }
    .robots-select { width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit; }
    .robots-select:focus { outline:none;border-color:var(--p); }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">SEO <span>Settings</span></div>
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
          <h1>SEO Settings</h1>
          <p>Manage meta tags, Open Graph, Google Analytics, schema markup, code injection and favicon.</p>
        </div>
      </div>

      <!-- Tab bar -->
      <div class="tab-bar">
        <button class="tab-btn <?= $activeTab === 'global' ? 'active' : '' ?>" onclick="switchTab('global')">
          <i class="fas fa-globe"></i> Global Settings
        </button>
        <button class="tab-btn <?= $activeTab === 'pages' ? 'active' : '' ?>" onclick="switchTab('pages')">
          <i class="fas fa-file-code"></i> Per-Page SEO
        </button>
      </div>

      <!-- ===== GLOBAL TAB ===== -->
      <div class="tab-pane <?= $activeTab === 'global' ? 'active' : '' ?>" id="tab-global">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="global" />
          <input type="hidden" name="current_favicon" id="currentFavicon" value="<?= htmlspecialchars($info['favicon'] ?? '') ?>" />
          <input type="hidden" name="favicon_uploaded" id="faviconUploaded" value="" />

          <!-- Favicon -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-star" style="color:var(--acc)"></i> Favicon</h2></div>
            <div class="card-body">
              <?php if (!empty($info['favicon'])): ?>
              <div class="favicon-preview" id="faviconPreview">
                <img src="../<?= htmlspecialchars($info['favicon']) ?>" id="faviconImg" alt="Favicon" />
                <span id="faviconName"><?= htmlspecialchars(basename($info['favicon'])) ?></span>
              </div>
              <?php else: ?>
              <div class="favicon-preview" id="faviconPreview" style="display:none">
                <img src="" id="faviconImg" alt="Favicon" />
                <span id="faviconName"></span>
              </div>
              <?php endif; ?>
              <label style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:var(--white);border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:13px;margin-bottom:8px">
                <i class="fas fa-upload"></i> Upload Favicon
                <input type="file" id="faviconInput" accept="image/*,.ico" onchange="uploadFavicon(this)" style="display:none" />
              </label>
              <div class="upload-progress" id="faviconProgress">
                <div class="upload-progress-bar" id="faviconBar"></div>
                <span class="upload-progress-text" id="faviconText">0%</span>
              </div>
              <div class="upload-status" id="faviconStatus"></div>
              <p class="form-hint" style="margin-top:8px">Recommended: ICO, PNG or WebP, 32×32 or 64×64 px</p>
            </div>
          </div>

          <!-- Site Meta Description -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-search" style="color:var(--acc)"></i> Default Site Meta</h2></div>
            <div class="card-body">
              <div class="form-group full">
                <label class="form-label">Default Meta Description</label>
                <textarea name="site_description" class="form-textarea" rows="3"
                          placeholder="Used on pages without a specific meta description…"><?= htmlspecialchars($info['site_description'] ?? '') ?></textarea>
                <p class="form-hint">Overridden by per-page descriptions when set.</p>
              </div>
            </div>
          </div>

          <!-- Google Analytics -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fab fa-google" style="color:var(--acc)"></i> Google Analytics</h2></div>
            <div class="card-body">
              <div class="form-group full">
                <label class="form-label">Measurement ID (GA4)</label>
                <input type="text" name="google_analytics" class="form-input"
                       value="<?= htmlspecialchars($info['google_analytics'] ?? '') ?>"
                       placeholder="G-XXXXXXXXXX" style="max-width:320px" />
                <p class="form-hint">Leave empty to disable. Paste your GA4 Measurement ID (starts with G-).</p>
              </div>
            </div>
          </div>

          <!-- Global Code Injection -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-code" style="color:var(--acc)"></i> Global Code Injection</h2></div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group full">
                  <label class="form-label"><code>&lt;head&gt;</code> Code — injected on ALL pages</label>
                  <textarea name="global_head_code" class="form-textarea code-textarea"
                            placeholder="<!-- Any HTML/script/link tags to inject in <head> -->"><?= htmlspecialchars($info['global_head_code'] ?? '') ?></textarea>
                  <p class="form-hint">e.g. meta pixel, tag manager, custom CSS link. Injected on every page.</p>
                </div>
                <div class="form-group full">
                  <label class="form-label"><code>&lt;body&gt;</code> Code — injected before <code>&lt;/body&gt;</code> on ALL pages</label>
                  <textarea name="global_body_code" class="form-textarea code-textarea"
                            placeholder="<!-- Any scripts to inject before </body> -->"><?= htmlspecialchars($info['global_body_code'] ?? '') ?></textarea>
                  <p class="form-hint">e.g. chat widgets, analytics scripts.</p>
                </div>
              </div>
            </div>
          </div>

          <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-floppy-disk"></i> Save Global Settings
            </button>
          </div>
        </form>
      </div><!-- /tab-global -->

      <!-- ===== PER-PAGE TAB ===== -->
      <div class="tab-pane <?= $activeTab === 'pages' ? 'active' : '' ?>" id="tab-pages">

        <div class="page-select-bar">
          <select class="form-input" id="pageSelector" onchange="loadPage(this.value)">
            <?php foreach ($knownPages as $key => $label): ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= $selectedPage === $key ? 'selected' : '' ?>>
              <?= htmlspecialchars($label) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <span style="font-size:12px;color:var(--gray)">Select a page to edit its SEO</span>
        </div>

        <form method="post" id="pageForm">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="page" />
          <input type="hidden" name="page_key" id="pageKey" value="<?= htmlspecialchars($selectedPage) ?>" />

          <!-- Meta Tags -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header">
              <h2><i class="fas fa-tags" style="color:var(--acc)"></i> Meta Tags</h2>
              <span style="font-size:12px;color:var(--gray)">Page: <strong id="pageLabel"><?= htmlspecialchars($knownPages[$selectedPage] ?? $selectedPage) ?></strong></span>
            </div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group full">
                  <label class="form-label">Meta Title <small style="font-weight:400;text-transform:none">(leave blank to use default)</small></label>
                  <input type="text" name="meta_title" id="f_meta_title" class="form-input"
                         value="<?= htmlspecialchars($pageData['meta_title']) ?>"
                         placeholder="Page title for Google…" maxlength="255" />
                  <p class="form-hint">Recommended: 50–60 characters</p>
                </div>
                <div class="form-group full">
                  <label class="form-label">Meta Description</label>
                  <textarea name="meta_desc" id="f_meta_desc" class="form-textarea" rows="3"
                            placeholder="Short description for search engines…" maxlength="500"><?= htmlspecialchars($pageData['meta_desc']) ?></textarea>
                  <p class="form-hint">Recommended: 150–160 characters</p>
                </div>
                <div class="form-group">
                  <label class="form-label">Robots</label>
                  <select name="robots" id="f_robots" class="robots-select">
                    <?php foreach (['index, follow','noindex, follow','noindex, nofollow','index, nofollow'] as $r): ?>
                    <option value="<?= $r ?>" <?= $pageData['robots'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Canonical URL <small style="font-weight:400;text-transform:none">(optional)</small></label>
                  <input type="text" name="canonical" id="f_canonical" class="form-input"
                         value="<?= htmlspecialchars($pageData['canonical']) ?>"
                         placeholder="https://dgtec.com.sa/page" />
                </div>
              </div>
            </div>
          </div>

          <!-- Open Graph -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-share-nodes" style="color:var(--acc)"></i> Open Graph (Social Sharing)</h2></div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group full">
                  <label class="form-label">OG Title</label>
                  <input type="text" name="og_title" id="f_og_title" class="form-input"
                         value="<?= htmlspecialchars($pageData['og_title']) ?>"
                         placeholder="Title shown when shared on social media…" maxlength="255" />
                </div>
                <div class="form-group full">
                  <label class="form-label">OG Description</label>
                  <textarea name="og_desc" id="f_og_desc" class="form-textarea" rows="2"
                            maxlength="500"><?= htmlspecialchars($pageData['og_desc']) ?></textarea>
                </div>
                <div class="form-group full">
                  <label class="form-label">OG Image <small style="font-weight:400;text-transform:none">(shown when shared on social media)</small></label>
                  <div class="og-upload-wrap">
                    <?php $ogImgVal = $pageData['og_image'] ?? ''; ?>
                    <img id="ogPreview" src="<?= $ogImgVal ? '../' . htmlspecialchars($ogImgVal) : '' ?>"
                         class="og-preview <?= $ogImgVal ? 'show' : '' ?>" alt="OG preview" />
                    <div>
                      <input type="hidden" name="og_image" id="f_og_image" value="<?= htmlspecialchars($ogImgVal) ?>" />
                      <label class="og-upload-btn">
                        <i class="fas fa-upload"></i> Upload Image
                        <input type="file" accept="image/jpeg,image/png,image/webp" style="display:none"
                               onchange="uploadOgImage(this, 'f_og_image', 'ogPreview', 'ogStatus')" />
                      </label>
                      <div id="ogStatus" class="upload-status" style="margin-top:4px"></div>
                      <p class="form-hint" style="margin-top:4px">Recommended: 1200×630 px (JPG/PNG/WebP, max 5 MB)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Schema -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-sitemap" style="color:var(--acc)"></i> Schema Markup (JSON-LD)</h2></div>
            <div class="card-body">
              <textarea name="schema_json" id="f_schema_json" class="form-textarea schema-textarea"
                        placeholder='{"@context":"https://schema.org","@type":"Organization","name":"DGTEC",...}'><?= htmlspecialchars($pageData['schema_json']) ?></textarea>
              <p class="form-hint">Paste valid JSON-LD schema. Will be wrapped in a &lt;script type="application/ld+json"&gt; tag.</p>
            </div>
          </div>

          <!-- Per-Page Code Injection -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-code" style="color:var(--acc)"></i> Page-Specific Code Injection</h2></div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group full">
                  <label class="form-label">Head Code <small style="font-weight:400;text-transform:none">(this page only)</small></label>
                  <textarea name="head_code" id="f_head_code" class="form-textarea code-textarea"
                            placeholder="<!-- Extra tags for <head> on this page only -->"><?= htmlspecialchars($pageData['head_code']) ?></textarea>
                </div>
                <div class="form-group full">
                  <label class="form-label">Body Code <small style="font-weight:400;text-transform:none">(this page only)</small></label>
                  <textarea name="body_code" id="f_body_code" class="form-textarea code-textarea"
                            placeholder="<!-- Scripts before </body> on this page only -->"><?= htmlspecialchars($pageData['body_code']) ?></textarea>
                </div>
              </div>
            </div>
          </div>

          <div style="display:flex;gap:12px;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-floppy-disk"></i> Save Page SEO
            </button>
          </div>
        </form>
      </div><!-- /tab-pages -->

    </div><!-- /.admin-content -->
  </main>
</div>

<script>
/* ===== Tab switching ===== */
function switchTab(name) {
  document.querySelectorAll('.tab-pane').forEach(function(el) { el.classList.remove('active'); });
  document.querySelectorAll('.tab-btn').forEach(function(el) { el.classList.remove('active'); });
  document.getElementById('tab-' + name).classList.add('active');
  document.querySelectorAll('.tab-btn').forEach(function(btn) {
    if (btn.textContent.toLowerCase().indexOf(name === 'global' ? 'global' : 'per') !== -1) {
      btn.classList.add('active');
    }
  });
}

/* ===== Page selector — load via AJAX ===== */
var pageData = <?= json_encode(dgtec_seo_all() ? array_column(dgtec_seo_all(), null, 'page_key') : new stdClass()) ?>;

function loadPage(key) {
  document.getElementById('pageKey').value = key;
  var labels = <?= json_encode($knownPages) ?>;
  document.getElementById('pageLabel').textContent = labels[key] || key;
  var d = pageData[key] || {};
  document.getElementById('f_meta_title').value  = d.meta_title  || '';
  document.getElementById('f_meta_desc').value   = d.meta_desc   || '';
  document.getElementById('f_og_title').value    = d.og_title    || '';
  document.getElementById('f_og_desc').value     = d.og_desc     || '';
  document.getElementById('f_og_image').value    = d.og_image    || '';
  document.getElementById('f_canonical').value   = d.canonical   || '';
  document.getElementById('f_robots').value      = d.robots      || 'index, follow';
  document.getElementById('f_schema_json').value = d.schema_json || '';
  document.getElementById('f_head_code').value   = d.head_code   || '';
  document.getElementById('f_body_code').value   = d.body_code   || '';

  /* OG image */
  var ogVal     = d.og_image || '';
  var ogHidden  = document.getElementById('f_og_image');
  var ogPreview = document.getElementById('ogPreview');
  ogHidden.value = ogVal;
  if (ogVal) {
    ogPreview.src = '../' + ogVal;
    ogPreview.classList.add('show');
  } else {
    ogPreview.src = '';
    ogPreview.classList.remove('show');
  }
}

/* ===== OG image upload ===== */
function uploadOgImage(input, hiddenId, previewId, statusId) {
  if (!input.files || !input.files[0]) return;
  var statusEl = document.getElementById(statusId);
  statusEl.className = 'upload-status';
  statusEl.textContent = 'Uploading…';
  var fd = new FormData();
  fd.append('image', input.files[0]);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'og-upload.php', true);
  xhr.setRequestHeader('X-CSRF-Token', '<?= admin_csrf_token() ?>');
  xhr.addEventListener('load', function() {
    input.value = '';
    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        document.getElementById(hiddenId).value = resp.path;
        var prev = document.getElementById(previewId);
        prev.src = resp.preview;
        prev.classList.add('show');
        statusEl.className = 'upload-status success';
        statusEl.textContent = 'Uploaded.';
      } else {
        statusEl.className = 'upload-status error';
        statusEl.textContent = resp.error || 'Upload failed.';
      }
    } catch(e) {
      statusEl.className = 'upload-status error';
      statusEl.textContent = 'Server error.';
    }
  });
  xhr.send(fd);
}

/* ===== Favicon upload ===== */
function uploadFavicon(input) {
  if (!input.files || !input.files[0]) return;
  var file        = input.files[0];
  var progressEl  = document.getElementById('faviconProgress');
  var barEl       = document.getElementById('faviconBar');
  var textEl      = document.getElementById('faviconText');
  var statusEl    = document.getElementById('faviconStatus');
  progressEl.classList.add('active');
  barEl.style.width = '0%';
  textEl.textContent = '0%';
  statusEl.className = 'upload-status';
  statusEl.textContent = 'Uploading…';
  var fd = new FormData();
  fd.append('image', file);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'seo-upload.php', true);
  xhr.setRequestHeader('X-CSRF-Token', '<?= admin_csrf_token() ?>');
  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      var pct = Math.round((e.loaded / e.total) * 100);
      barEl.style.width = pct + '%';
      textEl.textContent = pct + '%';
    }
  });
  xhr.addEventListener('load', function() {
    input.value = '';
    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        document.getElementById('faviconUploaded').value = resp.path;
        document.getElementById('faviconImg').src = resp.preview;
        document.getElementById('faviconName').textContent = resp.filename;
        document.getElementById('faviconPreview').style.display = 'inline-flex';
        barEl.style.width = '100%'; textEl.textContent = '100%';
        statusEl.className = 'upload-status success';
        statusEl.textContent = 'Uploaded. Click "Save Global Settings" to apply.';
      } else {
        progressEl.classList.remove('active');
        statusEl.className = 'upload-status error';
        statusEl.textContent = resp.error || 'Upload failed.';
      }
    } catch(e) {
      progressEl.classList.remove('active');
      statusEl.className = 'upload-status error';
      statusEl.textContent = 'Unexpected response.';
    }
  });
  xhr.send(fd);
}
</script>
</body>
</html>
