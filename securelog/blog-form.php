<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image    = trim($_POST['current_image'] ?? '');
    $uploaded = trim($_POST['image_uploaded'] ?? '');
    if ($uploaded !== '') $image = $uploaded;

    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    if ($slug === '' && $title !== '') {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slug = trim($slug, '-');
    }

    $pubDate = trim($_POST['published_at'] ?? '');
    if ($pubDate === '') $pubDate = null;

    $d = [
        'id'           => $isEdit ? $id : null,
        'position'     => max(1, (int)($_POST['position'] ?? 1)),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'title'        => $title,
        'slug'         => $slug,
        'category'     => trim($_POST['category'] ?? ''),
        'excerpt'      => trim($_POST['excerpt'] ?? ''),
        'content'      => trim($_POST['content'] ?? ''),
        'image'        => $image,
        'published_at' => $pubDate,
    ];

    if (empty($d['title']))   $errors[] = 'Title is required.';
    if (empty($d['excerpt'])) $errors[] = 'Excerpt is required.';

    if (empty($errors)) {
        dgtec_blog_save($d);
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
  <style>
    .upload-progress { display:none;margin-top:12px;background:var(--bg);border-radius:8px;overflow:hidden;height:22px;position:relative;border:1px solid var(--border); }
    .upload-progress.active { display:block; }
    .upload-progress-bar { height:100%;background:linear-gradient(90deg,var(--p),var(--btn));width:0%;transition:width .2s ease;border-radius:8px; }
    .upload-progress-text { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--dark);pointer-events:none; }
    .upload-status { margin-top:8px;font-size:12px;color:var(--gray); }
    .upload-status.error   { color:#dc2626; }
    .upload-status.success { color:#16a34a; }
    .content-textarea { min-height:400px;font-family:'Courier New',monospace;font-size:13px;line-height:1.6; }
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

      <form method="post" id="blogForm">
        <input type="hidden" name="current_image" id="currentImage" value="<?= htmlspecialchars($d['image']) ?>" />
        <input type="hidden" name="image_uploaded" id="uploadedImagePath" value="" />

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

        <!-- Post Details -->
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
                <label class="form-label">Excerpt * <small style="text-transform:none;font-weight:400">(shown on blog listing page)</small></label>
                <textarea name="excerpt" class="form-textarea" rows="3"
                          placeholder="Short summary of the post…" required><?= htmlspecialchars($d['excerpt']) ?></textarea>
              </div>

            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header">
            <h2><i class="fas fa-align-left" style="color:var(--acc)"></i> Post Content</h2>
            <span style="font-size:12px;color:var(--gray)">HTML supported</span>
          </div>
          <div class="card-body" style="padding:0">
            <textarea name="content" class="form-textarea content-textarea"
                      style="border:none;border-radius:0;resize:vertical"
                      placeholder="Write your post content here. HTML tags are supported (e.g. &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;blockquote&gt;)…"><?= htmlspecialchars($d['content']) ?></textarea>
          </div>
        </div>

        <!-- Visibility -->
        <div class="card" style="margin-bottom:28px">
          <div class="card-header"><h2><i class="fas fa-toggle-on" style="color:var(--acc)"></i> Visibility</h2></div>
          <div class="card-body">
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:14px">
              <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                     style="width:18px;height:18px;accent-color:var(--btn)" />
              <span><strong>Published</strong> — visible on the public blog page</span>
            </label>
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
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
var slugTouched = <?= ($d['slug'] !== '') ? 'true' : 'false' ?>;

document.getElementById('slugInput').addEventListener('input', function() { slugTouched = true; });

function autoSlug() {
  if (slugTouched) return;
  var slug = document.getElementById('titleInput').value.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-');
  document.getElementById('slugInput').value = slug;
}

function handleImageSelect(input) {
  if (!input.files || !input.files[0]) return;
  var file         = input.files[0];
  var progressWrap = document.getElementById('uploadProgress');
  var progressBar  = document.getElementById('uploadProgressBar');
  var progressText = document.getElementById('uploadProgressText');
  var statusEl     = document.getElementById('uploadStatus');
  var saveBtn      = document.getElementById('saveBtn');
  progressWrap.classList.add('active');
  progressBar.style.width = '0%';
  progressText.textContent = '0%';
  statusEl.className = 'upload-status';
  statusEl.textContent = 'Uploading…';
  saveBtn.disabled = true;
  var formData = new FormData();
  formData.append('image', file);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'blog-upload.php', true);
  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      var pct = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = pct + '%';
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
        document.getElementById('imgPreview').src = resp.preview;
        document.getElementById('imgPreviewBox').style.display = 'block';
        document.getElementById('uploadArea').style.display = 'none';
        progressBar.style.width = '100%';
        progressText.textContent = '100%';
        statusEl.className = 'upload-status success';
        statusEl.textContent = 'Image uploaded successfully.';
      } else {
        progressWrap.classList.remove('active');
        statusEl.className = 'upload-status error';
        statusEl.textContent = resp.error || 'Upload failed.';
      }
    } catch(err) {
      progressWrap.classList.remove('active');
      statusEl.className = 'upload-status error';
      statusEl.textContent = 'Unexpected server response.';
    }
  });
  xhr.addEventListener('error', function() {
    saveBtn.disabled = false;
    progressWrap.classList.remove('active');
    statusEl.className = 'upload-status error';
    statusEl.textContent = 'Network error. Please try again.';
  });
  xhr.send(formData);
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
</script>
</body>
</html>
