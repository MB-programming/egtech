<?php
/**
 * DGTEC Admin — Custom Page Add / Edit
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id     = (int)($_GET['id'] ?? 0);
$page   = $id ? dgtec_page_get($id) : null;
$isEdit = !empty($page);
$errors = [];

$defaults = [
    'id'        => '',
    'title'     => '',
    'slug'      => '',
    'content'   => '',
    'is_active' => 1,
];
$d = array_merge($defaults, $page ?? []);

/* SEO data keyed as 'page:slug' */
$seoKey  = $d['slug'] ? 'page:' . $d['slug'] : '';
$seoData = $seoKey ? dgtec_seo_get($seoKey) : [];
$sd = array_merge(['meta_title'=>'','meta_desc'=>'','og_title'=>'','og_desc'=>'','og_image'=>'',
    'canonical'=>'','robots'=>'index,follow','schema_json'=>'','head_code'=>'','body_code'=>''], $seoData);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $title = sanitize_str($_POST['title'] ?? '', 300);
    $slug  = sanitize_str($_POST['slug']  ?? '', 200);
    if ($slug === '' && $title !== '') {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slug = trim($slug, '-');
    }

    $d = [
        'id'        => $isEdit ? $id : null,
        'title'     => $title,
        'slug'      => $slug,
        'content'   => sanitize_html($_POST['content'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    if (empty($d['title'])) $errors[] = 'Title is required.';
    if (empty($d['slug']))  $errors[] = 'Slug is required.';

    if (empty($errors)) {
        dgtec_page_save($d);
        /* Save SEO */
        dgtec_seo_save('page:' . $d['slug'], [
            'meta_title'  => sanitize_str($_POST['seo_meta_title']  ?? '', 160),
            'meta_desc'   => sanitize_str($_POST['seo_meta_desc']   ?? '', 320),
            'og_title'    => sanitize_str($_POST['seo_og_title']    ?? '', 160),
            'og_desc'     => sanitize_str($_POST['seo_og_desc']     ?? '', 320),
            'og_image'    => sanitize_url($_POST['seo_og_image']    ?? ''),
            'canonical'   => sanitize_url($_POST['seo_canonical']   ?? ''),
            'robots'      => sanitize_str($_POST['seo_robots']      ?? 'index,follow', 50),
            'schema_json' => sanitize_html($_POST['seo_schema_json']?? ''),
            'head_code'   => sanitize_html($_POST['seo_head_code']  ?? ''),
            'body_code'   => sanitize_html($_POST['seo_body_code']  ?? ''),
        ]);
        header('Location: pages.php?saved=1');
        exit;
    }
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'pages';
$pageTitle   = $isEdit ? 'Edit Page' : 'Add New Page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js" crossorigin="anonymous"></script>
  <style>
    .ck-editor__editable { min-height:420px; font-size:15px; line-height:1.7; }
    .slug-preview { margin-top:6px; font-size:12px; color:var(--gray); }
    .slug-preview code { color:var(--p); font-size:12px; }
  </style>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'Add' ?> <span>Page</span></div>
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
        <a href="pages.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <form method="post" id="pageForm">
        <?= csrf_field() ?>

        <!-- Title + Slug + Status -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-file-lines" style="color:var(--acc)"></i> Page Details</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group full">
                <label class="form-label">Page Title *</label>
                <input type="text" name="title" id="titleInput" class="form-input"
                       value="<?= htmlspecialchars($d['title']) ?>"
                       placeholder="e.g. About Us" required
                       oninput="syncSlug()" />
              </div>

              <div class="form-group full">
                <label class="form-label">Slug (URL)</label>
                <input type="text" name="slug" id="slugInput" class="form-input"
                       value="<?= htmlspecialchars($d['slug']) ?>"
                       placeholder="e.g. about-us"
                       oninput="updateSlugPreview()" />
                <div class="slug-preview">URL: <code>page.php?slug=<span id="slugPreview"><?= htmlspecialchars($d['slug']) ?></span></code></div>
              </div>

              <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:6px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px">
                  <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                         style="width:18px;height:18px;accent-color:var(--btn)" />
                  <span><strong>Published</strong> — visible on the website</span>
                </label>
              </div>

            </div>
          </div>
        </div>

        <!-- Page Content -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header">
            <h2><i class="fas fa-align-left" style="color:var(--acc)"></i> Page Content</h2>
            <button type="button" id="sourceToggle" onclick="toggleSource()"
                    style="margin-left:auto;font-size:12px;padding:4px 12px" class="btn btn-secondary">
              <i class="fas fa-code"></i> HTML Source
            </button>
          </div>
          <div class="card-body">
            <textarea name="content" id="contentEditor"><?= htmlspecialchars($d['content']) ?></textarea>
            <textarea id="sourceEditor" style="display:none;width:100%;min-height:420px;font-family:monospace;font-size:13px;border:1px solid var(--border);border-radius:8px;padding:12px;resize:vertical;background:var(--bg);color:var(--dark)"><?= htmlspecialchars($d['content']) ?></textarea>
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="pages.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" onclick="syncBeforeSubmit()">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Page' : 'Save Page' ?>
          </button>
        </div>

        <!-- SEO -->
        <div class="card" style="margin-top:24px;margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-magnifying-glass-chart" style="color:var(--acc)"></i> SEO Settings</h2></div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group full">
                <label class="form-label">Meta Title</label>
                <input type="text" name="seo_meta_title" class="form-input" value="<?= htmlspecialchars($sd['meta_title']) ?>" placeholder="Leave blank to use page title" maxlength="160" />
              </div>
              <div class="form-group full">
                <label class="form-label">Meta Description</label>
                <textarea name="seo_meta_desc" class="form-textarea" rows="2" placeholder="Leave blank to use page content excerpt" maxlength="320"><?= htmlspecialchars($sd['meta_desc']) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Robots</label>
                <select name="seo_robots" class="form-input">
                  <?php foreach (['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $r): ?>
                  <option value="<?= $r ?>" <?= $sd['robots'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Canonical URL</label>
                <input type="text" name="seo_canonical" class="form-input" value="<?= htmlspecialchars($sd['canonical']) ?>" placeholder="https://…" />
              </div>
            </div>
            <hr style="margin:18px 0;border-color:var(--border)" />
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">OG Title</label>
                <input type="text" name="seo_og_title" class="form-input" value="<?= htmlspecialchars($sd['og_title']) ?>" placeholder="Defaults to meta title" maxlength="160" />
              </div>
              <div class="form-group">
                <label class="form-label">OG Image <small style="font-weight:400;text-transform:none">(1200×630 px)</small></label>
                <input type="hidden" name="seo_og_image" id="pageOgImgHidden" value="<?= htmlspecialchars($sd['og_image']) ?>" />
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:4px">
                  <img id="pageOgPreview" src="<?= $sd['og_image'] ? '../'.htmlspecialchars($sd['og_image']) : '' ?>"
                       style="max-width:180px;max-height:100px;border-radius:6px;border:1px solid var(--border);object-fit:cover;<?= $sd['og_image'] ? '' : 'display:none' ?>" />
                  <div>
                    <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--white);border:1.5px solid var(--border);border-radius:7px;cursor:pointer;font-size:13px">
                      <i class="fas fa-upload"></i> Upload OG Image
                      <input type="file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadPageOgImage(this)" />
                    </label>
                    <div id="pageOgStatus" style="font-size:12px;margin-top:4px;color:var(--gray)"></div>
                  </div>
                </div>
              </div>
              <div class="form-group full">
                <label class="form-label">OG Description</label>
                <textarea name="seo_og_desc" class="form-textarea" rows="2" placeholder="Defaults to meta description"><?= htmlspecialchars($sd['og_desc']) ?></textarea>
              </div>
            </div>
          </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="pages.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" onclick="syncBeforeSubmit()">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Page' : 'Save Page' ?>
          </button>
        </div>
      </form>

    </div>
  </main>
</div>

<script>
var ckEditor = null;
var sourceMode = false;

ClassicEditor.create(document.querySelector('#contentEditor'), {
  toolbar: ['heading','|','bold','italic','underline','strikethrough','|',
            'bulletedList','numberedList','|','blockQuote','insertTable','|',
            'link','|','undo','redo'],
}).then(function(editor) {
  ckEditor = editor;
}).catch(function(err) { console.error(err); });

function toggleSource() {
  sourceMode = !sourceMode;
  var btn = document.getElementById('sourceToggle');
  if (sourceMode) {
    var html = ckEditor ? ckEditor.getData() : document.getElementById('contentEditor').value;
    document.getElementById('sourceEditor').value = html;
    document.querySelector('.ck-editor').style.display = 'none';
    document.getElementById('sourceEditor').style.display = 'block';
    btn.innerHTML = '<i class="fas fa-eye"></i> Visual Editor';
  } else {
    var src = document.getElementById('sourceEditor').value;
    document.querySelector('.ck-editor').style.display = '';
    document.getElementById('sourceEditor').style.display = 'none';
    if (ckEditor) ckEditor.setData(src);
    btn.innerHTML = '<i class="fas fa-code"></i> HTML Source';
  }
}

function syncBeforeSubmit() {
  if (sourceMode) {
    var src = document.getElementById('sourceEditor').value;
    if (ckEditor) ckEditor.setData(src);
  }
}

var slugManuallyEdited = <?= ($isEdit && $d['slug'] !== '') ? 'true' : 'false' ?>;

function syncSlug() {
  if (slugManuallyEdited) return;
  var title = document.getElementById('titleInput').value;
  var slug  = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  document.getElementById('slugInput').value = slug;
  document.getElementById('slugPreview').textContent = slug;
}

document.getElementById('slugInput').addEventListener('input', function() {
  slugManuallyEdited = true;
  updateSlugPreview();
});

function updateSlugPreview() {
  document.getElementById('slugPreview').textContent = document.getElementById('slugInput').value;
}

function uploadPageOgImage(input) {
  if (!input.files || !input.files[0]) return;
  var statusEl = document.getElementById('pageOgStatus');
  statusEl.textContent = 'Uploading…';
  var fd = new FormData();
  fd.append('image', input.files[0]);
  fetch('og-upload.php', { method:'POST', body:fd,
    headers:{'X-CSRF-TOKEN':'<?= admin_csrf_token() ?>'}
  }).then(function(r){ return r.json(); }).then(function(data) {
    if (data.success) {
      document.getElementById('pageOgImgHidden').value = data.path;
      var img = document.getElementById('pageOgPreview');
      img.src = data.preview; img.style.display = '';
      statusEl.textContent = 'Uploaded.'; statusEl.style.color = '#16a34a';
    } else {
      statusEl.textContent = data.error || 'Upload failed.'; statusEl.style.color = '#dc2626';
    }
  }).catch(function(){ statusEl.textContent = 'Upload error.'; statusEl.style.color = '#dc2626'; });
}
</script>
</body>
</html>
