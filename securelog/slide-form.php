<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id     = (int)($_GET['id'] ?? 0);
$slide  = $id ? dgtec_slide_get($id) : null;
$isEdit = !empty($slide);
$errors = [];
$msg    = '';

/* Defaults for a new slide */
$defaults = [
    'id'                => '',
    'position'          => count(dgtec_slides_all()) + 1,
    'is_active'         => 1,
    'label'             => '',
    'title'             => '',
    'highlight_text'    => '',
    'highlight_color'   => '',
    'description'       => '',
    'bg_image'          => '',
    'gradient_color1'   => '#183f96',
    'gradient_opacity1' => 0.84,
    'gradient_color2'   => '#183f96',
    'gradient_opacity2' => 0.45,
    'btn1_text'         => '',
    'btn1_url'          => '',
    'btn2_text'         => '',
    'btn2_url'          => '',
];
$d = array_merge($defaults, $slide ?? []);

/* ---- Handle save ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Image: prefer AJAX-uploaded path, otherwise keep current */
    $bgImage = trim($_POST['current_bg_image'] ?? '');
    $uploadedPath = trim($_POST['bg_image_uploaded'] ?? '');
    if ($uploadedPath !== '') {
        $bgImage = $uploadedPath;
    }

    $d = [
        'id'                => $isEdit ? $id : null,
        'position'          => max(1, (int)($_POST['position'] ?? 1)),
        'is_active'         => isset($_POST['is_active']) ? 1 : 0,
        'label'             => trim($_POST['label'] ?? ''),
        'title'             => trim($_POST['title'] ?? ''),
        'highlight_text'    => trim($_POST['highlight_text'] ?? ''),
        'highlight_color'   => trim($_POST['highlight_color'] ?? ''),
        'description'       => trim($_POST['description'] ?? ''),
        'bg_image'          => $bgImage,
        'gradient_color1'   => trim($_POST['gradient_color1'] ?? '#183f96'),
        'gradient_opacity1' => max(0, min(1, (float)($_POST['gradient_opacity1'] ?? 0.84))),
        'gradient_color2'   => trim($_POST['gradient_color2'] ?? '#183f96'),
        'gradient_opacity2' => max(0, min(1, (float)($_POST['gradient_opacity2'] ?? 0.45))),
        'btn1_text'         => trim($_POST['btn1_text'] ?? ''),
        'btn1_url'          => trim($_POST['btn1_url'] ?? ''),
        'btn2_text'         => trim($_POST['btn2_text'] ?? ''),
        'btn2_url'          => trim($_POST['btn2_url'] ?? ''),
    ];

    if (empty($d['label'])) $errors[] = 'Label / specialty is required.';
    if (empty($d['title'])) $errors[] = 'Main title is required.';

    if (empty($errors)) {
        dgtec_slide_save($d);
        header('Location: slides.php?saved=1');
        exit;
    }
}

/* Sidebar unread count */
$unreadCount = dgtec_submissions_unread_count();

$pageTitle = $isEdit ? 'Edit Slide' : 'Add New Slide';
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
    .upload-progress {
      display: none;
      margin-top: 12px;
      background: var(--bg);
      border-radius: 8px;
      overflow: hidden;
      height: 22px;
      position: relative;
      border: 1px solid var(--border);
    }
    .upload-progress.active { display: block; }
    .upload-progress-bar {
      height: 100%;
      background: linear-gradient(90deg, var(--p), var(--btn));
      width: 0%;
      transition: width .2s ease;
      border-radius: 8px;
    }
    .upload-progress-text {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
      color: var(--dark);
      pointer-events: none;
    }
    .upload-status { margin-top: 8px; font-size: 12px; color: var(--gray); }
    .upload-status.error   { color: #dc2626; }
    .upload-status.success { color: #16a34a; }
  </style>
</head>
<body>
<div class="admin-shell">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-brand">
      <img src="../assets/images/logo.webp" alt="DGTEC" />
      <span>Admin</span>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-section">Content</p>
      <a href="slides.php" class="active"><i class="fas fa-images"></i> Hero Slides</a>
      <a href="services.php"><i class="fas fa-briefcase"></i> Services</a>
      <a href="solutions.php"><i class="fas fa-lightbulb"></i> Solutions</a>
      <p class="nav-section">Inbox</p>
      <a href="submissions.php">
        <i class="fas fa-envelope"></i> Submissions
        <?php if ($unreadCount > 0): ?>
        <span style="margin-left:auto;background:#dc2626;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700"><?= $unreadCount ?></span>
        <?php endif; ?>
      </a>
      <p class="nav-section">Site</p>
      <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'Add' ?> <span>Slide</span></div>
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
          <p><?= $isEdit ? 'Update the hero slider slide below.' : 'Fill in the details to add a new slide to the hero slider.' ?></p>
        </div>
        <a href="slides.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Slides</a>
      </div>

      <form method="post" id="slideForm">

        <!-- Hidden fields for image handling -->
        <input type="hidden" name="current_bg_image" id="currentBgImage" value="<?= htmlspecialchars($d['bg_image']) ?>" />
        <input type="hidden" name="bg_image_uploaded" id="uploadedImagePath" value="" />

        <!-- ===== SECTION 1: Background Image ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-image" style="color:var(--acc)"></i> Background Image</h2></div>
          <div class="card-body">

            <?php if ($d['bg_image']): ?>
            <div class="img-preview-box" id="imgPreviewBox">
              <img src="../<?= htmlspecialchars($d['bg_image']) ?>" id="imgPreview" alt="Current image" />
              <button type="button" class="remove-img" onclick="clearImage()" title="Remove image"><i class="fas fa-times"></i></button>
            </div>
            <?php else: ?>
            <div id="imgPreviewBox" style="display:none" class="img-preview-box">
              <img src="" id="imgPreview" alt="" />
              <button type="button" class="remove-img" onclick="clearImage()" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>

            <div class="img-upload-wrap" id="uploadArea" <?= $d['bg_image'] ? 'style="display:none"' : '' ?>>
              <input type="file" name="bg_image_file" id="bgImageInput"
                     accept="image/jpeg,image/png,image/webp,image/gif"
                     onchange="handleImageSelect(this)" />
              <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
              <p><strong>Click to upload</strong> or drag and drop</p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WebP, GIF — max 10 MB</p>
            </div>

            <!-- Progress bar (hidden until upload starts) -->
            <div class="upload-progress" id="uploadProgress">
              <div class="upload-progress-bar" id="uploadProgressBar"></div>
              <span class="upload-progress-text" id="uploadProgressText">0%</span>
            </div>
            <div class="upload-status" id="uploadStatus"></div>

          </div>
        </div>

        <!-- ===== SECTION 2: Gradient ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-palette" style="color:var(--acc)"></i> Gradient Overlay</h2></div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Color 1 (Start)</label>
                <div class="color-row">
                  <input type="color" class="color-picker" name="gradient_color1" id="gc1"
                         value="<?= htmlspecialchars($d['gradient_color1']) ?>" onchange="updateGradientPreview()" />
                  <div style="flex:1">
                    <label class="form-label" style="margin-bottom:4px">Opacity</label>
                    <input type="number" class="opacity-input" name="gradient_opacity1" id="go1"
                           value="<?= $d['gradient_opacity1'] ?>" min="0" max="1" step="0.01" onchange="updateGradientPreview()" />
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Color 2 (End)</label>
                <div class="color-row">
                  <input type="color" class="color-picker" name="gradient_color2" id="gc2"
                         value="<?= htmlspecialchars($d['gradient_color2']) ?>" onchange="updateGradientPreview()" />
                  <div style="flex:1">
                    <label class="form-label" style="margin-bottom:4px">Opacity</label>
                    <input type="number" class="opacity-input" name="gradient_opacity2" id="go2"
                           value="<?= $d['gradient_opacity2'] ?>" min="0" max="1" step="0.01" onchange="updateGradientPreview()" />
                  </div>
                </div>
              </div>
            </div>
            <div class="gradient-preview" id="gradientPreview"></div>
          </div>
        </div>

        <!-- ===== SECTION 3: Text Content ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-text-height" style="color:var(--acc)"></i> Slide Text Content</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group">
                <label class="form-label">Label / Specialty *</label>
                <input type="text" name="label" class="form-input"
                       value="<?= htmlspecialchars($d['label']) ?>"
                       placeholder="e.g. Expert Talent Acquisition" required />
                <p class="form-hint">Small caption above the title (shown in accent colour)</p>
              </div>

              <div class="form-group">
                <label class="form-label">Position (order)</label>
                <input type="number" name="position" class="form-input"
                       value="<?= (int)$d['position'] ?>" min="1" />
              </div>

              <div class="form-group full">
                <label class="form-label">Main Title * <small style="text-transform:none;letter-spacing:0;font-weight:400">(one line per row — use Enter for line breaks)</small></label>
                <textarea name="title" class="form-textarea" rows="3"
                          placeholder="The Right Talent,&#10;Right Now, Right" required><?= htmlspecialchars($d['title']) ?></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Highlighted Text</label>
                <input type="text" name="highlight_text" class="form-input"
                       value="<?= htmlspecialchars($d['highlight_text']) ?>"
                       placeholder="e.g. Here" />
                <p class="form-hint">Displayed on a new line after the title, in a special colour</p>
              </div>

              <div class="form-group">
                <label class="form-label">Highlight Colour <small style="text-transform:none;font-weight:400">(leave empty uses site accent)</small></label>
                <div class="color-row">
                  <input type="color" class="color-picker" name="highlight_color" id="hlColor"
                         value="<?= htmlspecialchars($d['highlight_color'] ?: '#6dc6db') ?>" />
                  <input type="text" class="form-input" name="highlight_color_text" id="hlColorText"
                         value="<?= htmlspecialchars($d['highlight_color']) ?>"
                         placeholder="Leave empty for default" style="flex:1" />
                </div>
                <p class="form-hint">Pick a colour OR type a hex value, or leave blank for the default accent</p>
              </div>

              <div class="form-group full">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-textarea" rows="3"
                          placeholder="Hire top-tier technical, managerial and engineering professionals..." required><?= htmlspecialchars($d['description']) ?></textarea>
              </div>

            </div>
          </div>
        </div>

        <!-- ===== SECTION 4: Buttons ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-hand-pointer" style="color:var(--acc)"></i> Call-to-Action Buttons</h2></div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Button 1 Text</label>
                <input type="text" name="btn1_text" class="form-input"
                       value="<?= htmlspecialchars($d['btn1_text']) ?>"
                       placeholder="e.g. Hire Now" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 1 Link / URL</label>
                <input type="text" name="btn1_url" class="form-input"
                       value="<?= htmlspecialchars($d['btn1_url']) ?>"
                       placeholder="e.g. contact.php" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 2 Text</label>
                <input type="text" name="btn2_text" class="form-input"
                       value="<?= htmlspecialchars($d['btn2_text']) ?>"
                       placeholder="e.g. Our Services" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 2 Link / URL</label>
                <input type="text" name="btn2_url" class="form-input"
                       value="<?= htmlspecialchars($d['btn2_url']) ?>"
                       placeholder="e.g. services.php" />
              </div>
            </div>
          </div>
        </div>

        <!-- ===== SECTION 5: Visibility ===== -->
        <div class="card" style="margin-bottom:28px">
          <div class="card-header"><h2><i class="fas fa-toggle-on" style="color:var(--acc)"></i> Visibility</h2></div>
          <div class="card-body">
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:14px">
              <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                     style="width:18px;height:18px;accent-color:var(--btn)" />
              <span><strong>Active</strong> slide will appear in the hero slider on the homepage</span>
            </label>
          </div>
        </div>

        <!-- Save -->
        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="slides.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Slide' : 'Save Slide' ?>
          </button>
        </div>

      </form>
    </div><!-- /.admin-content -->
  </main>
</div>

<script>
/* ======================================================
   Gradient preview
   ====================================================== */
function updateGradientPreview() {
  var c1 = document.getElementById('gc1').value;
  var o1 = parseFloat(document.getElementById('go1').value) || 0.84;
  var c2 = document.getElementById('gc2').value;
  var o2 = parseFloat(document.getElementById('go2').value) || 0.45;

  function hexRgba(hex, a) {
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    var r = parseInt(hex.slice(0,2),16),
        g = parseInt(hex.slice(2,4),16),
        b = parseInt(hex.slice(4,6),16);
    return 'rgba('+r+','+g+','+b+','+a+')';
  }

  document.getElementById('gradientPreview').style.background =
    'linear-gradient(105deg,' + hexRgba(c1,o1) + ' 0%,' + hexRgba(c2,o2) + ' 100%)';
}
updateGradientPreview();

/* ======================================================
   Highlight colour sync
   ====================================================== */
document.getElementById('hlColor').addEventListener('input', function() {
  document.getElementById('hlColorText').value = this.value;
});
document.getElementById('hlColorText').addEventListener('input', function() {
  if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
    document.getElementById('hlColor').value = this.value;
  }
});

/* Fix highlight_color on submit */
document.getElementById('slideForm').addEventListener('submit', function() {
  var textVal = document.getElementById('hlColorText').value.trim();
  document.querySelectorAll('input[name="highlight_color"]').forEach(function(el) { el.name = '_hc_old'; });
  var hc = document.createElement('input');
  hc.type  = 'hidden';
  hc.name  = 'highlight_color';
  hc.value = /^#[0-9a-fA-F]{6}$/.test(textVal) ? textVal : '';
  this.appendChild(hc);
});

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

  /* Reset UI */
  progressWrap.classList.add('active');
  progressBar.style.width  = '0%';
  progressText.textContent = '0%';
  statusEl.className   = 'upload-status';
  statusEl.textContent = 'Uploading...';
  saveBtn.disabled = true;

  var formData = new FormData();
  formData.append('image', file);

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'slide-upload.php', true);

  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      var pct = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width  = pct + '%';
      progressText.textContent = pct + '%';
    }
  });

  xhr.addEventListener('load', function() {
    saveBtn.disabled = false;
    /* Clear file input — path stored in hidden field, not re-submitted */
    input.value = '';

    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        /* Store returned path in hidden field */
        document.getElementById('uploadedImagePath').value = resp.path;

        /* Show preview */
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
  document.getElementById('bgImageInput').value      = '';
  document.getElementById('uploadedImagePath').value = '';
  document.getElementById('currentBgImage').value    = '';
  document.getElementById('imgPreviewBox').style.display = 'none';
  document.getElementById('uploadArea').style.display   = 'block';
  document.getElementById('uploadProgress').classList.remove('active');
  document.getElementById('uploadStatus').textContent   = '';
}
</script>
</body>
</html>
