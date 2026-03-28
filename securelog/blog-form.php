<?php
/**
 * DGTEC Admin — Blog Post Add / Edit
 * Features: CKEditor 5 rich editor + SEO meta tab
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id     = (int)($_GET['id'] ?? 0);
$post   = $id ? dgtec_blog_get($id) : null;
$isEdit = !empty($post);
$errors = [];

$defaults = [
    'id'           => '',
    'position'     => count(dgtec_blogs_all()) + 1,
    'is_active'    => 1,
    'title'        => '',
    'slug'         => '',
    'category'     => '',
    'excerpt'      => '',
    'content'      => '',
    'image'        => '',
    'published_at' => date('Y-m-d'),
];
$d = array_merge($defaults, $post ?? []);

/* Load existing SEO data for this slug */
$seoKey  = $d['slug'] ? 'blog:' . $d['slug'] : '';
$seoData = $seoKey ? dgtec_seo_get($seoKey) : [];
$sd      = array_merge([
    'meta_title'  => '',
    'meta_desc'   => '',
    'og_title'    => '',
    'og_desc'     => '',
    'og_image'    => '',
    'canonical'   => '',
    'robots'      => 'index,follow',
    'schema_json' => '',
    'head_code'   => '',
    'body_code'   => '',
], $seoData);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $image    = trim($_POST['current_image'] ?? '');
    $uploaded = trim($_POST['image_uploaded'] ?? '');
    if ($uploaded !== '') $image = $uploaded;

    $title = sanitize_str($_POST['title'] ?? '', 300);
    $slug  = sanitize_str($_POST['slug'] ?? '', 200);
    if ($slug === '' && $title !== '') {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slug = trim($slug, '-');
    }

    $pubDate = trim($_POST['published_at'] ?? '');
    if ($pubDate === '') $pubDate = null;

    $d = [
        'id'           => $isEdit ? $id : null,
        'position'     => sanitize_int($_POST['position'] ?? 1, 1),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'title'        => $title,
        'slug'         => $slug,
        'category'     => sanitize_str($_POST['category'] ?? '', 100),
        'excerpt'      => sanitize_str($_POST['excerpt'] ?? '', 1000),
        'content'      => sanitize_html($_POST['content'] ?? ''),
        'image'        => sanitize_url($image),
        'published_at' => $pubDate,
    ];

    if (empty($d['title']))   $errors[] = 'Title is required.';
    if (empty($d['excerpt'])) $errors[] = 'Excerpt is required.';

    if (empty($errors)) {
        dgtec_blog_save($d);

        /* Save SEO data for this slug */
        $newKey = 'blog:' . $d['slug'];
        dgtec_seo_save($newKey, [
            'meta_title'  => sanitize_str($_POST['seo_meta_title'] ?? '', 160),
            'meta_desc'   => sanitize_str($_POST['seo_meta_desc'] ?? '', 320),
            'og_title'    => sanitize_str($_POST['seo_og_title'] ?? '', 160),
            'og_desc'     => sanitize_str($_POST['seo_og_desc'] ?? '', 320),
            'og_image'    => sanitize_url($_POST['seo_og_image'] ?? ''),
            'canonical'   => sanitize_url($_POST['seo_canonical'] ?? ''),
            'robots'      => sanitize_str($_POST['seo_robots'] ?? 'index,follow', 50),
            'schema_json' => sanitize_html($_POST['seo_schema_json'] ?? ''),
            'head_code'   => sanitize_html($_POST['seo_head_code'] ?? ''),
            'body_code'   => sanitize_html($_POST['seo_body_code'] ?? ''),
        ]);

        header('Location: blog.php?saved=1');
        exit;
    }
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'blog';
$pageTitle   = $isEdit ? 'Edit Post' : 'Add New Post';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <!-- CKEditor 5 Classic Build -->
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js" crossorigin="anonymous"></script>
  <style>
    /* ── Tabs ── */
    .form-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:24px; }
    .form-tab  { padding:10px 22px; font-size:14px; font-weight:600; color:var(--gray); cursor:pointer;
                 border:none; background:none; border-bottom:3px solid transparent; margin-bottom:-2px;
                 transition:.15s; display:flex; align-items:center; gap:7px; }
    .form-tab.active { color:var(--btn); border-bottom-color:var(--btn); }
    .form-tab:hover  { color:var(--dark); }
    .form-panel      { display:none; }
    .form-panel.active { display:block; }

    /* ── Upload progress ── */
    .upload-progress { display:none;margin-top:12px;background:var(--bg);border-radius:8px;overflow:hidden;height:22px;position:relative;border:1px solid var(--border); }
    .upload-progress.active { display:block; }
    .upload-progress-bar  { height:100%;background:linear-gradient(90deg,var(--p),var(--btn));width:0%;transition:width .2s ease;border-radius:8px; }
    .upload-progress-text { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--dark);pointer-events:none; }
    .upload-status        { margin-top:8px;font-size:12px;color:var(--gray); }
    .upload-status.error   { color:#dc2626; }
    .upload-status.success { color:#16a34a; }

    /* ── CKEditor container ── */
    .ck-editor__editable { min-height:380px; font-size:15px; line-height:1.7; }

    /* ── SEO character counters ── */
    .char-counter { font-size:11px; color:var(--gray); text-align:right; margin-top:2px; }
    .char-counter.warn { color:#f59e0b; }
    .char-counter.over { color:#ef4444; }

    /* ── Code textarea ── */
    .code-textarea { font-family:'Courier New',monospace; font-size:12px; line-height:1.5; min-height:100px; background:#1e1e2e; color:#cdd6f4; border-radius:8px; padding:12px; }
    /* ── Toggle HTML button active state ── */
    #toggleHtmlBtn.active { background:var(--dark,#0f172a); color:#7dd3fc; border-color:var(--dark,#0f172a); }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'New' ?> <span>Blog Post</span></div>
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
          <h1><?= htmlspecialchars($pageTitle) ?></h1>
          <p><?= $isEdit ? 'Update post details below.' : 'Fill in the details to publish a new article.' ?></p>
        </div>
        <a href="blog.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Posts</a>
      </div>

      <!-- Tab navigation -->
      <div class="form-tabs">
        <button type="button" class="form-tab active" data-tab="details">
          <i class="fas fa-file-lines"></i> Post Details
        </button>
        <button type="button" class="form-tab" data-tab="content">
          <i class="fas fa-align-left"></i> Content
        </button>
        <button type="button" class="form-tab" data-tab="seo">
          <i class="fas fa-chart-line"></i> SEO
        </button>
      </div>

      <form method="post" id="blogForm">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" id="currentImage" value="<?= htmlspecialchars($d['image']) ?>" />
        <input type="hidden" name="image_uploaded" id="uploadedImagePath" value="" />
        <!-- CKEditor stores content here on submit -->
        <textarea name="content" id="contentHidden" style="display:none"><?= htmlspecialchars($d['content']) ?></textarea>

        <!-- ════ TAB: DETAILS ════ -->
        <div class="form-panel active" id="panel-details">

          <!-- Featured Image -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-image" style="color:var(--acc)"></i> Featured Image</h2></div>
            <div class="card-body">
              <?php if ($d['image']): ?>
              <div class="img-preview-box" id="imgPreviewBox">
                <img src="../<?= htmlspecialchars($d['image']) ?>" id="imgPreview" alt="Featured Image" />
                <button type="button" class="remove-img" onclick="clearImage()" title="Remove"><i class="fas fa-times"></i></button>
              </div>
              <?php else: ?>
              <div id="imgPreviewBox" style="display:none" class="img-preview-box">
                <img src="" id="imgPreview" alt="" />
                <button type="button" class="remove-img" onclick="clearImage()" title="Remove"><i class="fas fa-times"></i></button>
              </div>
              <?php endif; ?>

              <div class="img-upload-wrap" id="uploadArea" <?= $d['image'] ? 'style="display:none"' : '' ?>>
                <input type="file" name="image_file" id="imageInput"
                       accept="image/jpeg,image/png,image/webp"
                       onchange="handleImageSelect(this)" />
                <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                <p><strong>Click to upload</strong> or drag and drop</p>
                <p style="font-size:11px;margin-top:4px">JPG, PNG, WebP — max 10 MB</p>
              </div>

              <div class="upload-progress" id="uploadProgress">
                <div class="upload-progress-bar" id="uploadProgressBar"></div>
                <span class="upload-progress-text" id="uploadProgressText">0%</span>
              </div>
              <div class="upload-status" id="uploadStatus"></div>
            </div>
          </div>

          <!-- Post Fields -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-text-height" style="color:var(--acc)"></i> Post Details</h2></div>
            <div class="card-body">
              <div class="form-grid">

                <div class="form-group full">
                  <label class="form-label">Title *</label>
                  <input type="text" name="title" id="titleInput" class="form-input"
                         value="<?= htmlspecialchars($d['title']) ?>"
                         placeholder="Post title…" oninput="autoSlug()" required />
                </div>

                <div class="form-group">
                  <label class="form-label">Slug <small style="text-transform:none;font-weight:400">(URL)</small></label>
                  <input type="text" name="slug" id="slugInput" class="form-input"
                         value="<?= htmlspecialchars($d['slug']) ?>"
                         placeholder="auto-generated-from-title" />
                  <p class="form-hint">Auto-generated from title if empty.</p>
                </div>

                <div class="form-group">
                  <label class="form-label">Category / Tag</label>
                  <input type="text" name="category" class="form-input"
                         value="<?= htmlspecialchars($d['category']) ?>"
                         placeholder="e.g. AI &amp; Recruitment" />
                </div>

                <div class="form-group">
                  <label class="form-label">Published Date</label>
                  <input type="date" name="published_at" class="form-input"
                         value="<?= htmlspecialchars($d['published_at'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label class="form-label">Position (order)</label>
                  <input type="number" name="position" class="form-input"
                         value="<?= (int)$d['position'] ?>" min="1" />
                </div>

                <div class="form-group full">
                  <label class="form-label">Excerpt * <small style="text-transform:none;font-weight:400">(shown on blog listing)</small></label>
                  <textarea name="excerpt" class="form-textarea" rows="3"
                            placeholder="Short summary of the post…" required><?= htmlspecialchars($d['excerpt']) ?></textarea>
                </div>

              </div>
            </div>
          </div>

          <!-- Visibility -->
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-toggle-on" style="color:var(--acc)"></i> Visibility</h2></div>
            <div class="card-body">
              <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:14px">
                <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                       style="width:18px;height:18px;accent-color:var(--btn)" />
                <span><strong>Published</strong> — visible on the public blog page</span>
              </label>
            </div>
          </div>

        </div><!-- /panel-details -->

        <!-- ════ TAB: CONTENT ════ -->
        <div class="form-panel" id="panel-content">
          <div class="card" style="margin-bottom:24px">
            <div class="card-header">
              <h2><i class="fas fa-pen-nib" style="color:var(--acc)"></i> Post Content</h2>
              <button type="button" id="toggleHtmlBtn"
                      style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
                             border:1.5px solid var(--border);border-radius:7px;background:var(--white);
                             font-size:12px;font-weight:600;cursor:pointer;color:var(--gray);transition:.15s">
                <i class="fas fa-code"></i> HTML Source
              </button>
            </div>
            <div class="card-body" style="padding:12px">
              <!-- CKEditor attaches here -->
              <div id="ckEditorWrap">
                <div id="editorContainer"><?= $d['content'] ?></div>
              </div>
              <!-- Raw HTML textarea (shown in source mode) -->
              <textarea id="htmlSourceArea"
                        style="display:none;width:100%;min-height:400px;font-family:'Courier New',monospace;
                               font-size:13px;line-height:1.6;padding:14px;border:1px solid var(--border);
                               border-radius:8px;resize:vertical;background:#1e1e2e;color:#cdd6f4;
                               box-sizing:border-box"
                        placeholder="<!-- Write raw HTML here -->"
                        spellcheck="false"></textarea>
            </div>
          </div>
        </div><!-- /panel-content -->

        <!-- ════ TAB: SEO ════ -->
        <div class="form-panel" id="panel-seo">
          <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2><i class="fas fa-magnifying-glass-chart" style="color:var(--acc)"></i> SEO Settings</h2></div>
            <div class="card-body">
              <div class="form-grid">

                <div class="form-group full">
                  <label class="form-label">Meta Title <small>(≤ 60 chars recommended)</small></label>
                  <input type="text" name="seo_meta_title" id="seoTitle" class="form-input"
                         value="<?= htmlspecialchars($sd['meta_title']) ?>"
                         placeholder="Leave blank to use post title" maxlength="160"
                         oninput="updateCounter(this,'cntTitle',60)" />
                  <div class="char-counter" id="cntTitle"><?= mb_strlen($sd['meta_title']) ?> / 60</div>
                </div>

                <div class="form-group full">
                  <label class="form-label">Meta Description <small>(≤ 160 chars recommended)</small></label>
                  <textarea name="seo_meta_desc" id="seoDesc" class="form-textarea" rows="2"
                            placeholder="Leave blank to use post excerpt" maxlength="320"
                            oninput="updateCounter(this,'cntDesc',160)"><?= htmlspecialchars($sd['meta_desc']) ?></textarea>
                  <div class="char-counter" id="cntDesc"><?= mb_strlen($sd['meta_desc']) ?> / 160</div>
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
                  <input type="text" name="seo_canonical" class="form-input"
                         value="<?= htmlspecialchars($sd['canonical']) ?>"
                         placeholder="https://…" />
                </div>

              </div>

              <hr style="margin:18px 0;border-color:var(--border)" />
              <p class="form-label" style="margin-bottom:14px">Open Graph (Social Sharing)</p>

              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">OG Title</label>
                  <input type="text" name="seo_og_title" class="form-input"
                         value="<?= htmlspecialchars($sd['og_title']) ?>"
                         placeholder="Defaults to meta title" maxlength="160" />
                </div>

                <div class="form-group">
                  <label class="form-label">OG Image <small style="font-weight:400;text-transform:none">(1200×630 px recommended)</small></label>
                  <input type="hidden" name="seo_og_image" id="ogImgHidden" value="<?= htmlspecialchars($sd['og_image']) ?>" />
                  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:4px">
                    <?php if (!empty($sd['og_image'])): ?>
                    <img id="blogOgPreview" src="../<?= htmlspecialchars($sd['og_image']) ?>"
                         style="max-width:180px;max-height:100px;border-radius:6px;border:1px solid var(--border);object-fit:cover" />
                    <?php else: ?>
                    <img id="blogOgPreview" src="" style="max-width:180px;max-height:100px;border-radius:6px;border:1px solid var(--border);object-fit:cover;display:none" />
                    <?php endif; ?>
                    <div>
                      <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--white);border:1.5px solid var(--border);border-radius:7px;cursor:pointer;font-size:13px">
                        <i class="fas fa-upload"></i> Upload OG Image
                        <input type="file" accept="image/jpeg,image/png,image/webp" style="display:none"
                               onchange="uploadBlogOgImage(this)" />
                      </label>
                      <div id="blogOgStatus" style="font-size:12px;margin-top:4px;color:var(--gray)"></div>
                    </div>
                  </div>
                </div>

                <div class="form-group full">
                  <label class="form-label">OG Description</label>
                  <textarea name="seo_og_desc" class="form-textarea" rows="2"
                            placeholder="Defaults to meta description"><?= htmlspecialchars($sd['og_desc']) ?></textarea>
                </div>
              </div>

              <hr style="margin:18px 0;border-color:var(--border)" />

              <div class="form-group" style="margin-bottom:18px">
                <label class="form-label">JSON-LD Schema Markup</label>
                <textarea name="seo_schema_json" class="form-textarea code-textarea" rows="6"
                          placeholder='{"@context":"https://schema.org","@type":"Article",…}'><?= htmlspecialchars($sd['schema_json']) ?></textarea>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Extra &lt;head&gt; Code <small>(for this post only)</small></label>
                  <textarea name="seo_head_code" class="form-textarea code-textarea" rows="4"
                            placeholder="<!-- tracking pixels, custom meta… -->"><?= htmlspecialchars($sd['head_code']) ?></textarea>
                </div>

                <div class="form-group">
                  <label class="form-label">Extra &lt;/body&gt; Code <small>(for this post only)</small></label>
                  <textarea name="seo_body_code" class="form-textarea code-textarea" rows="4"
                            placeholder="<!-- custom scripts… -->"><?= htmlspecialchars($sd['body_code']) ?></textarea>
                </div>
              </div>

            </div>
          </div>
        </div><!-- /panel-seo -->

        <!-- Submit -->
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px">
          <a href="blog.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Post' : 'Publish Post' ?>
          </button>
        </div>
      </form>
    </div>
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

/* ── CKEditor 5 ── */
var ckEditor;
var htmlMode = false;

ClassicEditor.create(document.getElementById('editorContainer'), {
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
    ckEditor = editor;
}).catch(function(err) {
    console.error('CKEditor error:', err);
});

/* ── Custom HTML source toggle ── */
document.getElementById('toggleHtmlBtn').addEventListener('click', function() {
    if (!ckEditor) return;
    var srcArea  = document.getElementById('htmlSourceArea');
    var ckWrap   = document.getElementById('ckEditorWrap');
    var btn      = this;

    if (!htmlMode) {
        /* Switch to HTML source view */
        srcArea.value  = ckEditor.getData();
        ckWrap.style.display  = 'none';
        srcArea.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-eye"></i> Visual Editor';
        btn.classList.add('active');
        htmlMode = true;
    } else {
        /* Switch back to visual */
        ckEditor.setData(srcArea.value);
        srcArea.style.display = 'none';
        ckWrap.style.display  = 'block';
        btn.innerHTML = '<i class="fas fa-code"></i> HTML Source';
        btn.classList.remove('active');
        htmlMode = false;
    }
});

/* Sync CKEditor / HTML source to hidden textarea before submit */
document.getElementById('blogForm').addEventListener('submit', function() {
    if (htmlMode) {
        document.getElementById('contentHidden').value = document.getElementById('htmlSourceArea').value;
    } else if (ckEditor) {
        document.getElementById('contentHidden').value = ckEditor.getData();
    }
});

/* ── Slug auto-generation ── */
var slugTouched = <?= ($d['slug'] !== '') ? 'true' : 'false' ?>;
document.getElementById('slugInput').addEventListener('input', function() { slugTouched = true; });

function autoSlug() {
    if (slugTouched) return;
    var slug = document.getElementById('titleInput').value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-').replace(/-+/g,'-');
    document.getElementById('slugInput').value = slug;
}

/* ── Image upload ── */
function handleImageSelect(input) {
    if (!input.files || !input.files[0]) return;
    var file        = input.files[0];
    var progWrap    = document.getElementById('uploadProgress');
    var progBar     = document.getElementById('uploadProgressBar');
    var progText    = document.getElementById('uploadProgressText');
    var statusEl    = document.getElementById('uploadStatus');
    var saveBtn     = document.getElementById('saveBtn');
    progWrap.classList.add('active');
    progBar.style.width = '0%';
    progText.textContent = '0%';
    statusEl.className = 'upload-status';
    statusEl.textContent = 'Uploading…';
    saveBtn.disabled = true;
    var fd = new FormData();
    fd.append('image', file);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'blog-upload.php', true);
    xhr.setRequestHeader('X-CSRF-Token', <?= json_encode(admin_csrf_token()) ?>);
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            var pct = Math.round((e.loaded / e.total) * 100);
            progBar.style.width = pct + '%';
            progText.textContent = pct + '%';
        }
    });
    xhr.addEventListener('load', function() {
        saveBtn.disabled = false;
        input.value = '';
        try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
                document.getElementById('uploadedImagePath').value = resp.path;
                document.getElementById('imgPreview').src = resp.preview;
                document.getElementById('imgPreviewBox').style.display = 'block';
                document.getElementById('uploadArea').style.display = 'none';
                progBar.style.width = '100%';
                progText.textContent = '100%';
                statusEl.className = 'upload-status success';
                statusEl.textContent = 'Image uploaded successfully.';
            } else {
                progWrap.classList.remove('active');
                statusEl.className = 'upload-status error';
                statusEl.textContent = resp.error || 'Upload failed.';
            }
        } catch(e) {
            progWrap.classList.remove('active');
            statusEl.className = 'upload-status error';
            statusEl.textContent = 'Unexpected server response.';
        }
    });
    xhr.addEventListener('error', function() {
        saveBtn.disabled = false;
        progWrap.classList.remove('active');
        statusEl.className = 'upload-status error';
        statusEl.textContent = 'Network error. Please try again.';
    });
    xhr.send(fd);
}

function clearImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('uploadedImagePath').value = '';
    document.getElementById('currentImage').value = '';
    document.getElementById('imgPreviewBox').style.display = 'none';
    document.getElementById('uploadArea').style.display = 'block';
    document.getElementById('uploadProgress').classList.remove('active');
    document.getElementById('uploadStatus').textContent = '';
}

/* ── OG image upload ── */
function uploadBlogOgImage(input) {
  if (!input.files || !input.files[0]) return;
  var statusEl = document.getElementById('blogOgStatus');
  statusEl.textContent = 'Uploading…';
  statusEl.style.color = 'var(--gray)';
  var fd = new FormData();
  fd.append('image', input.files[0]);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'og-upload.php', true);
  xhr.setRequestHeader('X-CSRF-Token', <?= json_encode(admin_csrf_token()) ?>);
  xhr.addEventListener('load', function() {
    input.value = '';
    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        document.getElementById('ogImgHidden').value = resp.path;
        var prev = document.getElementById('blogOgPreview');
        prev.src = resp.preview;
        prev.style.display = 'block';
        statusEl.textContent = 'Uploaded.';
        statusEl.style.color = '#16a34a';
      } else {
        statusEl.textContent = resp.error || 'Upload failed.';
        statusEl.style.color = '#dc2626';
      }
    } catch(e) {
      statusEl.textContent = 'Server error.';
      statusEl.style.color = '#dc2626';
    }
  });
  xhr.send(fd);
}

/* ── SEO char counters ── */
function updateCounter(el, counterId, limit) {
    var len    = el.value.length;
    var cntEl  = document.getElementById(counterId);
    cntEl.textContent = len + ' / ' + limit;
    cntEl.className   = 'char-counter' + (len > limit ? ' over' : (len > limit * 0.9 ? ' warn' : ''));
}
/* Init counters */
(function() {
    var t = document.getElementById('seoTitle');
    var d = document.getElementById('seoDesc');
    if (t) updateCounter(t, 'cntTitle', 60);
    if (d) updateCounter(d, 'cntDesc', 160);
})();
</script>
</body>
</html>
