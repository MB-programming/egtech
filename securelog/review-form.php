<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id     = (int)($_GET['id'] ?? 0);
$review = $id ? dgtec_review_get($id) : null;
$isEdit = !empty($review);
$errors = [];

$defaults = [
    'id'        => '',
    'position'  => count(dgtec_reviews_all()) + 1,
    'is_active' => 1,
    'name'      => '',
    'job_title' => '',
    'review'    => '',
    'stars'     => 5,
    'image'     => '',
];
$d = array_merge($defaults, $review ?? []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $image    = trim($_POST['current_image'] ?? '');
    $uploaded = trim($_POST['image_uploaded'] ?? '');
    if ($uploaded !== '') $image = $uploaded;

    $d = [
        'id'        => $isEdit ? $id : null,
        'position'  => max(1, (int)($_POST['position'] ?? 1)),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'name'      => trim($_POST['name'] ?? ''),
        'job_title' => trim($_POST['job_title'] ?? ''),
        'review'    => trim($_POST['review'] ?? ''),
        'stars'     => min(5, max(1, (int)($_POST['stars'] ?? 5))),
        'image'     => $image,
    ];

    if (empty($d['name']))   $errors[] = 'Name is required.';
    if (empty($d['review'])) $errors[] = 'Review text is required.';

    if (empty($errors)) {
        dgtec_review_save($d);
        header('Location: reviews.php?saved=1');
        exit;
    }
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'reviews';
$pageTitle   = $isEdit ? 'Edit Review' : 'Add Review';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    .upload-progress { display:none;margin-top:12px;background:var(--bg);border-radius:8px;overflow:hidden;height:22px;position:relative;border:1px solid var(--border); }
    .upload-progress.active { display:block; }
    .upload-progress-bar { height:100%;background:linear-gradient(90deg,var(--p),var(--btn));width:0%;transition:width .2s ease;border-radius:8px; }
    .upload-progress-text { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--dark);pointer-events:none; }
    .upload-status { margin-top:8px;font-size:12px;color:var(--gray); }
    .upload-status.error { color:#dc2626; }
    .upload-status.success { color:#16a34a; }
    .star-rating { display:flex;gap:6px;align-items:center;margin-top:8px; }
    .star-btn { font-size:28px;cursor:pointer;color:#d1d5db;border:none;background:none;padding:0;line-height:1;transition:color .15s; }
    .star-btn.on { color:#f59e0b; }
    .star-btn:hover { color:#f59e0b; }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'Add' ?> <span>Review</span></div>
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
          <p><?= $isEdit ? 'Update review details below.' : 'Fill in the details to add a new client review.' ?></p>
        </div>
        <a href="reviews.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Reviews</a>
      </div>

      <form method="post" id="reviewForm">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" id="currentImage" value="<?= htmlspecialchars($d['image']) ?>" />
        <input type="hidden" name="image_uploaded" id="uploadedImagePath" value="" />

        <!-- Photo -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-user-circle" style="color:var(--acc)"></i> Client Photo <small style="font-weight:400;text-transform:none;font-size:12px;color:var(--gray)">(optional — initials shown if empty)</small></h2></div>
          <div class="card-body">
            <?php if ($d['image']): ?>
            <div class="img-preview-box" id="imgPreviewBox">
              <img src="../<?= htmlspecialchars($d['image']) ?>" id="imgPreview" alt="Photo"
                   style="border-radius:50%;width:100px;height:100px;object-fit:cover" />
              <button type="button" class="remove-img" onclick="clearImage()" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <?php else: ?>
            <div id="imgPreviewBox" style="display:none" class="img-preview-box">
              <img src="" id="imgPreview" alt="" style="border-radius:50%;width:100px;height:100px;object-fit:cover" />
              <button type="button" class="remove-img" onclick="clearImage()" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>

            <div class="img-upload-wrap" id="uploadArea" <?= $d['image'] ? 'style="display:none"' : '' ?>>
              <input type="file" name="image_file" id="imageInput"
                     accept="image/jpeg,image/png,image/webp"
                     onchange="handleImageSelect(this)" />
              <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
              <p><strong>Click to upload</strong> or drag and drop</p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WebP — max 5 MB</p>
            </div>

            <div class="upload-progress" id="uploadProgress">
              <div class="upload-progress-bar" id="uploadProgressBar"></div>
              <span class="upload-progress-text" id="uploadProgressText">0%</span>
            </div>
            <div class="upload-status" id="uploadStatus"></div>
          </div>
        </div>

        <!-- Details -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-text-height" style="color:var(--acc)"></i> Review Details</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group">
                <label class="form-label">Client Name *</label>
                <input type="text" name="name" class="form-input"
                       value="<?= htmlspecialchars($d['name']) ?>"
                       placeholder="e.g. Ahmed Al-Rashidi" required />
              </div>

              <div class="form-group">
                <label class="form-label">Job Title &amp; Company</label>
                <input type="text" name="job_title" class="form-input"
                       value="<?= htmlspecialchars($d['job_title']) ?>"
                       placeholder="e.g. HR Director, National Tech Co." />
              </div>

              <div class="form-group full">
                <label class="form-label">Star Rating</label>
                <div class="star-rating" id="starRating">
                  <?php for ($s = 1; $s <= 5; $s++): ?>
                  <button type="button" class="star-btn <?= $s <= (int)$d['stars'] ? 'on' : '' ?>"
                          data-val="<?= $s ?>" onclick="setStars(<?= $s ?>)">★</button>
                  <?php endfor; ?>
                  <span id="starLabel" style="font-size:13px;color:var(--gray);margin-left:8px"><?= (int)$d['stars'] ?> / 5</span>
                </div>
                <input type="hidden" name="stars" id="starsInput" value="<?= (int)$d['stars'] ?>" />
              </div>

              <div class="form-group full">
                <label class="form-label">Review Text *</label>
                <textarea name="review" class="form-textarea" rows="5"
                          placeholder="Write the client's review here…" required><?= htmlspecialchars($d['review']) ?></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Position (order)</label>
                <input type="number" name="position" class="form-input"
                       value="<?= (int)$d['position'] ?>" min="1" />
              </div>

            </div>
          </div>
        </div>

        <!-- Visibility -->
        <div class="card" style="margin-bottom:28px">
          <div class="card-header"><h2><i class="fas fa-toggle-on" style="color:var(--acc)"></i> Visibility</h2></div>
          <div class="card-body">
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:14px">
              <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                     style="width:18px;height:18px;accent-color:var(--btn)" />
              <span><strong>Active</strong> — will appear in the testimonials slider on the homepage</span>
            </label>
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="reviews.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Review' : 'Save Review' ?>
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
function setStars(val) {
  document.getElementById('starsInput').value = val;
  document.getElementById('starLabel').textContent = val + ' / 5';
  document.querySelectorAll('.star-btn').forEach(function(btn) {
    btn.classList.toggle('on', parseInt(btn.dataset.val) <= val);
  });
}

function handleImageSelect(input) {
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  var progressWrap = document.getElementById('uploadProgress');
  var progressBar  = document.getElementById('uploadProgressBar');
  var progressText = document.getElementById('uploadProgressText');
  var statusEl     = document.getElementById('uploadStatus');
  var saveBtn      = document.getElementById('saveBtn');
  progressWrap.classList.add('active');
  progressBar.style.width = '0%';
  progressText.textContent = '0%';
  statusEl.className = 'upload-status';
  statusEl.textContent = 'Uploading...';
  saveBtn.disabled = true;
  var formData = new FormData();
  formData.append('image', file);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'review-upload.php', true);
  xhr.setRequestHeader('X-CSRF-Token', '<?= admin_csrf_token() ?>');
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
        statusEl.textContent = 'Photo uploaded successfully.';
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
