<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

/* Determine type: 'service' or 'solution' */
$type = $_GET['type'] ?? $_POST['type'] ?? 'service';
if (!in_array($type, ['service', 'solution'], true)) {
    $type = 'service';
}

$typeLabel  = $type === 'service' ? 'Service' : 'Solution';
$listPage   = $type === 'service' ? 'services.php' : 'solutions.php';

$id     = (int)($_GET['id'] ?? 0);
$item   = $id ? dgtec_item_get($type, $id) : null;
$isEdit = !empty($item);
$errors = [];
$msg    = '';

/* Default values for a new item */
$defaults = [
    'id'           => '',
    'position'     => count(dgtec_items_all($type)) + 1,
    'is_active'    => 1,
    'title'        => '',
    'slug'         => '',
    'icon'         => '',
    'image'        => '',
    'description'  => '',
    'features'     => '',
    'page_url'     => '',
    'is_reversed'  => 0,
    'page_content' => '',
];
$d = array_merge($defaults, $item ?? []);

/* Convert pipe-separated features to newline for textarea display */
$featuresForDisplay = implode("\n", array_filter(explode('|', $d['features'])));

/* ---- Handle save ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    /* Image: prefer AJAX-uploaded path, otherwise keep current */
    $image = trim($_POST['current_image'] ?? '');
    $uploadedPath = trim($_POST['image_uploaded'] ?? '');
    if ($uploadedPath !== '') {
        $image = $uploadedPath;
    }

    /* Convert newline-separated features to pipe-separated for storage */
    $rawFeatures = trim($_POST['features'] ?? '');
    $featuresStored = implode('|', array_filter(array_map('trim', explode("\n", $rawFeatures))));

    /* Auto-generate slug from title if empty */
    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    if ($slug === '' && $title !== '') {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slug = trim($slug, '-');
    }

    $d = [
        'id'           => $isEdit ? $id : null,
        'position'     => max(1, (int)($_POST['position'] ?? 1)),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'title'        => $title,
        'slug'         => $slug,
        'icon'         => trim($_POST['icon'] ?? ''),
        'image'        => $image,
        'description'  => trim($_POST['description'] ?? ''),
        'features'     => $featuresStored,
        'page_url'     => trim($_POST['page_url'] ?? ''),
        'is_reversed'  => isset($_POST['is_reversed']) ? 1 : 0,
        'page_content' => sanitize_html($_POST['page_content'] ?? ''),
    ];

    if (empty($d['title'])) $errors[] = 'Title is required.';
    if (empty($d['description'])) $errors[] = 'Description is required.';

    if (empty($errors)) {
        dgtec_item_save($type, $d);
        header('Location: ' . $listPage . '?saved=1');
        exit;
    }

    /* Re-populate features for display on validation error */
    $featuresForDisplay = $rawFeatures;
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = $type === 'service' ? 'services' : 'solutions';
$pageTitle   = $isEdit ? "Edit $typeLabel" : "Add New $typeLabel";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js" crossorigin="anonymous"></script>
  <style>
    .upload-progress {
      display: none; margin-top: 12px; background: var(--bg); border-radius: 8px;
      overflow: hidden; height: 22px; position: relative; border: 1px solid var(--border);
    }
    .upload-progress.active { display: block; }
    .upload-progress-bar {
      height: 100%; background: linear-gradient(90deg, var(--p), var(--btn));
      width: 0%; transition: width .2s ease; border-radius: 8px;
    }
    .upload-progress-text {
      position: absolute; inset: 0; display: flex; align-items: center;
      justify-content: center; font-size: 11px; font-weight: 700;
      color: var(--dark); pointer-events: none;
    }
    .upload-status { margin-top: 8px; font-size: 12px; color: var(--gray); }
    .upload-status.error   { color: #dc2626; }
    .upload-status.success { color: #16a34a; }
    .icon-preview { display: inline-flex; align-items: center; gap: 10px; margin-top: 8px; font-size: 13px; color: var(--gray); }
    .icon-preview i { font-size: 24px; color: var(--p); }
    /* ── Tabs ── */
    .form-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:24px; }
    .form-tab  { padding:10px 22px; font-size:14px; font-weight:600; color:var(--gray); cursor:pointer;
                 border:none; background:none; border-bottom:3px solid transparent; margin-bottom:-2px;
                 transition:.15s; display:flex; align-items:center; gap:7px; }
    .form-tab.active { color:var(--btn); border-bottom-color:var(--btn); }
    .form-tab:hover  { color:var(--dark); }
    .form-panel      { display:none; }
    .form-panel.active { display:block; }
    /* ── CKEditor ── */
    .ck-editor__editable { min-height:380px; font-size:15px; line-height:1.7; }
    #togglePageHtmlBtn.active { background:var(--dark,#0f172a); color:#7dd3fc; border-color:var(--dark,#0f172a); }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'Add' ?> <span><?= $typeLabel ?></span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>

    <div class="admin-content">

      <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <strong>Please fix the following:</strong>
        <ul style="margin-top:6px;padding-left:18px">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1><?= $pageTitle ?></h1>
          <p><?= $isEdit ? "Update this $typeLabel's details below." : "Fill in the details to add a new $typeLabel." ?></p>
        </div>
        <a href="<?= $listPage ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to <?= $typeLabel ?>s</a>
      </div>

      <!-- Tab navigation -->
      <div class="form-tabs">
        <button type="button" class="form-tab active" data-tab="details">
          <i class="fas fa-list-check"></i> Card Details
        </button>
        <button type="button" class="form-tab" data-tab="pagecontent">
          <i class="fas fa-file-lines"></i> Page Content
        </button>
      </div>

      <form method="post" id="itemForm">
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />

        <!-- Hidden fields for image handling -->
        <input type="hidden" name="current_image" id="currentImage" value="<?= htmlspecialchars($d['image']) ?>" />
        <input type="hidden" name="image_uploaded" id="uploadedImagePath" value="" />
        <!-- CKEditor page_content stored here on submit -->
        <textarea name="page_content" id="pageContentHidden" style="display:none"><?= htmlspecialchars($d['page_content']) ?></textarea>

        <div class="form-panel active" id="panel-details">

        <!-- ===== SECTION 1: Image ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-image" style="color:var(--acc)"></i> <?= $typeLabel ?> Image</h2></div>
          <div class="card-body">

            <?php if ($d['image']): ?>
            <div class="img-preview-box" id="imgPreviewBox">
              <img src="../<?= htmlspecialchars($d['image']) ?>" id="imgPreview" alt="Current image" />
              <button type="button" class="remove-img" onclick="clearImage()" title="Remove image"><i class="fas fa-times"></i></button>
            </div>
            <?php else: ?>
            <div id="imgPreviewBox" style="display:none" class="img-preview-box">
              <img src="" id="imgPreview" alt="" />
              <button type="button" class="remove-img" onclick="clearImage()" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>

            <div class="img-upload-wrap" id="uploadArea" <?= $d['image'] ? 'style="display:none"' : '' ?>>
              <input type="file" name="image_file" id="imageInput"
                     accept="image/jpeg,image/png,image/webp,image/gif"
                     onchange="handleImageSelect(this)" />
              <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
              <p><strong>Click to upload</strong> or drag and drop</p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WebP, GIF — max 10 MB</p>
            </div>

            <div class="upload-progress" id="uploadProgress">
              <div class="upload-progress-bar" id="uploadProgressBar"></div>
              <span class="upload-progress-text" id="uploadProgressText">0%</span>
            </div>
            <div class="upload-status" id="uploadStatus"></div>

          </div>
        </div>

        <!-- ===== SECTION 2: Core Details ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-text-height" style="color:var(--acc)"></i> <?= $typeLabel ?> Details</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group full">
                <label class="form-label">Title *</label>
                <input type="text" name="title" id="titleInput" class="form-input"
                       value="<?= htmlspecialchars($d['title']) ?>"
                       placeholder="e.g. Expert Technical Recruitment"
                       oninput="autoSlug()" required />
              </div>

              <div class="form-group">
                <label class="form-label">Slug <small style="text-transform:none;font-weight:400">(URL-friendly ID)</small></label>
                <input type="text" name="slug" id="slugInput" class="form-input"
                       value="<?= htmlspecialchars($d['slug']) ?>"
                       placeholder="e.g. service-recruitment" />
                <p class="form-hint">Auto-generated from title if left empty</p>
              </div>

              <div class="form-group">
                <label class="form-label">Font Awesome Icon</label>
                <input type="text" name="icon" id="iconInput" class="form-input"
                       value="<?= htmlspecialchars($d['icon']) ?>"
                       placeholder="e.g. fas fa-users"
                       oninput="updateIconPreview()" />
                <div class="icon-preview" id="iconPreview">
                  <?php if ($d['icon']): ?>
                  <i class="<?= htmlspecialchars($d['icon']) ?>"></i>
                  <span>Icon Preview</span>
                  <?php else: ?>
                  <span>Enter a Font Awesome class to preview</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Page URL</label>
                <input type="text" name="page_url" class="form-input"
                       value="<?= htmlspecialchars($d['page_url']) ?>"
                       placeholder="e.g. service-recruitment.php" />
                <p class="form-hint">Relative URL for the "Explore" link</p>
              </div>

              <div class="form-group full">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-textarea" rows="4"
                          placeholder="Short description of this <?= strtolower($typeLabel) ?>..." required><?= htmlspecialchars($d['description']) ?></textarea>
              </div>

              <div class="form-group full">
                <label class="form-label">Features <small style="text-transform:none;font-weight:400">(one per line)</small></label>
                <textarea name="features" class="form-textarea" rows="6"
                          placeholder="72h Shortlist SLA&#10;AI-Powered Screening&#10;Permanent &amp; Contract"><?= htmlspecialchars($featuresForDisplay) ?></textarea>
                <p class="form-hint">Each line becomes a feature pill on the listing page. Stored internally as pipe-separated values.</p>
              </div>

            </div>
          </div>
        </div>

        <!-- ===== SECTION 3: Display Options ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-sliders" style="color:var(--acc)"></i> Display Options</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group">
                <label class="form-label">Position (order)</label>
                <input type="number" name="position" class="form-input"
                       value="<?= (int)$d['position'] ?>" min="1" />
              </div>

              <div class="form-group" style="display:flex;align-items:flex-end;gap:24px;padding-bottom:4px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px">
                  <input type="checkbox" name="is_reversed" <?= $d['is_reversed'] ? 'checked' : '' ?>
                         style="width:18px;height:18px;accent-color:var(--btn)" />
                  <span><strong>Reversed Layout</strong> — image on right, text on left</span>
                </label>
              </div>

            </div>
          </div>
        </div>

        <!-- ===== SECTION 4: Visibility ===== -->
        <div class="card" style="margin-bottom:28px">
          <div class="card-header"><h2><i class="fas fa-toggle-on" style="color:var(--acc)"></i> Visibility</h2></div>
          <div class="card-body">
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:14px">
              <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                     style="width:18px;height:18px;accent-color:var(--btn)" />
              <span><strong>Active</strong> — will appear on the public <?= strtolower($typeLabel) ?>s page</span>
            </label>
          </div>
        </div>

        <!-- Save (inside details panel) -->
        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="<?= $listPage ?>" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? "Update $typeLabel" : "Save $typeLabel" ?>
          </button>
        </div>

        </div><!-- /panel-details -->

        <!-- ════ TAB: PAGE CONTENT ════ -->
        <div class="form-panel" id="panel-pagecontent">
          <div class="card" style="margin-bottom:24px">
            <div class="card-header">
              <h2><i class="fas fa-pen-nib" style="color:var(--acc)"></i> Full Page Content</h2>
              <button type="button" id="togglePageHtmlBtn"
                      style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
                             border:1.5px solid var(--border);border-radius:7px;background:var(--white);
                             font-size:12px;font-weight:600;cursor:pointer;color:var(--gray);transition:.15s">
                <i class="fas fa-code"></i> HTML Source
              </button>
            </div>
            <div class="card-body" style="padding:12px">
              <p style="font-size:13px;color:var(--gray);margin-bottom:12px">
                If filled in, this content will be displayed on the <strong><?= strtolower($typeLabel) ?> detail page</strong>
                instead of the static HTML. Leave empty to keep the static page layout.
              </p>
              <div id="pageEditorWrap">
                <div id="pageEditorContainer"><?= $d['page_content'] ?></div>
              </div>
              <textarea id="pageHtmlSourceArea"
                        style="display:none;width:100%;min-height:400px;font-family:'Courier New',monospace;
                               font-size:13px;line-height:1.6;padding:14px;border:1px solid var(--border);
                               border-radius:8px;resize:vertical;background:#1e1e2e;color:#cdd6f4;
                               box-sizing:border-box"
                        placeholder="<!-- Write raw HTML here -->"
                        spellcheck="false"></textarea>
            </div>
          </div>

          <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="<?= $listPage ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" id="saveBtn2">
              <i class="fas fa-floppy-disk"></i> <?= $isEdit ? "Update $typeLabel" : "Save $typeLabel" ?>
            </button>
          </div>
        </div><!-- /panel-pagecontent -->

      </form>
    </div><!-- /.admin-content -->
  </main>
</div>

<script>
/* ── Tabs ── */
document.querySelectorAll('.form-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.form-tab').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.form-panel').forEach(function(p){ p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
});

/* ── CKEditor 5 for Page Content ── */
var pageEditor;
var pageHtmlMode = false;

ClassicEditor.create(document.getElementById('pageEditorContainer'), {
    toolbar: {
        items: [
            'heading', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'link', 'blockQuote', 'code', '|',
            'bulletedList', 'numberedList', 'outdent', 'indent', '|',
            'insertTable', 'horizontalLine', '|',
            'undo', 'redo'
        ]
    },
    heading: {
        options: [
            { model:'paragraph', title:'Paragraph', class:'ck-heading_paragraph' },
            { model:'heading1', view:'h1', title:'Heading 1', class:'ck-heading_heading1' },
            { model:'heading2', view:'h2', title:'Heading 2', class:'ck-heading_heading2' },
            { model:'heading3', view:'h3', title:'Heading 3', class:'ck-heading_heading3' },
            { model:'heading4', view:'h4', title:'Heading 4', class:'ck-heading_heading4' },
        ]
    },
    table: { contentToolbar: ['tableColumn','tableRow','mergeTableCells'] }
}).then(function(editor) {
    pageEditor = editor;
}).catch(function(err) { console.error('CKEditor error:', err); });

/* HTML source toggle for page content */
document.getElementById('togglePageHtmlBtn').addEventListener('click', function() {
    if (!pageEditor) return;
    var srcArea = document.getElementById('pageHtmlSourceArea');
    var edWrap  = document.getElementById('pageEditorWrap');
    var btn     = this;
    if (!pageHtmlMode) {
        srcArea.value = pageEditor.getData();
        edWrap.style.display  = 'none';
        srcArea.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-eye"></i> Visual Editor';
        btn.classList.add('active');
        pageHtmlMode = true;
    } else {
        pageEditor.setData(srcArea.value);
        srcArea.style.display = 'none';
        edWrap.style.display  = 'block';
        btn.innerHTML = '<i class="fas fa-code"></i> HTML Source';
        btn.classList.remove('active');
        pageHtmlMode = false;
    }
});

/* Sync page content on submit */
document.getElementById('itemForm').addEventListener('submit', function() {
    if (pageHtmlMode) {
        document.getElementById('pageContentHidden').value = document.getElementById('pageHtmlSourceArea').value;
    } else if (pageEditor) {
        document.getElementById('pageContentHidden').value = pageEditor.getData();
    }
});

/* ======================================================
   Auto-generate slug from title
   ====================================================== */
var slugTouched = <?= ($d['slug'] !== '') ? 'true' : 'false' ?>;

document.getElementById('slugInput').addEventListener('input', function() {
  slugTouched = true;
});

function autoSlug() {
  if (slugTouched) return;
  var title = document.getElementById('titleInput').value;
  var slug  = title.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-');
  document.getElementById('slugInput').value = slug;
}

/* ======================================================
   Live icon preview
   ====================================================== */
function updateIconPreview() {
  var cls     = document.getElementById('iconInput').value.trim();
  var preview = document.getElementById('iconPreview');
  if (cls) {
    preview.innerHTML = '<i class="' + cls + '"></i><span>Icon Preview</span>';
  } else {
    preview.innerHTML = '<span>Enter a Font Awesome class to preview</span>';
  }
}

/* ======================================================
   AJAX image upload with XHR progress tracking
   ====================================================== */
function handleImageSelect(input) {
  if (!input.files || !input.files[0]) return;

  var file         = input.files[0];
  var progressWrap = document.getElementById('uploadProgress');
  var progressBar  = document.getElementById('uploadProgressBar');
  var progressText = document.getElementById('uploadProgressText');
  var statusEl     = document.getElementById('uploadStatus');
  var saveBtn      = document.getElementById('saveBtn');

  progressWrap.classList.add('active');
  progressBar.style.width  = '0%';
  progressText.textContent = '0%';
  statusEl.className   = 'upload-status';
  statusEl.textContent = 'Uploading...';
  saveBtn.disabled = true;

  var formData = new FormData();
  formData.append('image', file);

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'item-upload.php', true);
  xhr.setRequestHeader('X-CSRF-Token', '<?= admin_csrf_token() ?>');

  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      var pct = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width  = pct + '%';
      progressText.textContent = pct + '%';
    }
  });

  xhr.addEventListener('load', function() {
    saveBtn.disabled = false;
    input.value = '';

    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        document.getElementById('uploadedImagePath').value = resp.path;
        document.getElementById('imgPreview').src              = resp.preview;
        document.getElementById('imgPreviewBox').style.display = 'block';
        document.getElementById('uploadArea').style.display    = 'none';
        progressBar.style.width  = '100%';
        progressText.textContent = '100%';
        statusEl.className   = 'upload-status success';
        statusEl.textContent = 'Image uploaded successfully.';
      } else {
        progressWrap.classList.remove('active');
        statusEl.className   = 'upload-status error';
        statusEl.textContent = resp.error || 'Upload failed.';
      }
    } catch (err) {
      progressWrap.classList.remove('active');
      statusEl.className   = 'upload-status error';
      statusEl.textContent = 'Unexpected server response.';
    }
  });

  xhr.addEventListener('error', function() {
    saveBtn.disabled = false;
    progressWrap.classList.remove('active');
    statusEl.className   = 'upload-status error';
    statusEl.textContent = 'Network error. Please try again.';
  });

  xhr.send(formData);
}

/* ======================================================
   Clear image
   ====================================================== */
function clearImage() {
  document.getElementById('imageInput').value         = '';
  document.getElementById('uploadedImagePath').value  = '';
  document.getElementById('currentImage').value       = '';
  document.getElementById('imgPreviewBox').style.display = 'none';
  document.getElementById('uploadArea').style.display   = 'block';
  document.getElementById('uploadProgress').classList.remove('active');
  document.getElementById('uploadStatus').textContent   = '';
}
</script>
</body>
</html>
