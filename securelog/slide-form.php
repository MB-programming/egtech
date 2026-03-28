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
    /* Collect fields */
    $d = [
        'id'                => $isEdit ? $id : null,
        'position'          => max(1, (int)($_POST['position'] ?? 1)),
        'is_active'         => isset($_POST['is_active']) ? 1 : 0,
        'label'             => trim($_POST['label'] ?? ''),
        'title'             => trim($_POST['title'] ?? ''),
        'highlight_text'    => trim($_POST['highlight_text'] ?? ''),
        'highlight_color'   => trim($_POST['highlight_color'] ?? ''),
        'description'       => trim($_POST['description'] ?? ''),
        'bg_image'          => trim($_POST['current_bg_image'] ?? ''),
        'gradient_color1'   => trim($_POST['gradient_color1'] ?? '#183f96'),
        'gradient_opacity1' => max(0, min(1, (float)($_POST['gradient_opacity1'] ?? 0.84))),
        'gradient_color2'   => trim($_POST['gradient_color2'] ?? '#183f96'),
        'gradient_opacity2' => max(0, min(1, (float)($_POST['gradient_opacity2'] ?? 0.45))),
        'btn1_text'         => trim($_POST['btn1_text'] ?? ''),
        'btn1_url'          => trim($_POST['btn1_url'] ?? ''),
        'btn2_text'         => trim($_POST['btn2_text'] ?? ''),
        'btn2_url'          => trim($_POST['btn2_url'] ?? ''),
    ];

    /* Handle image upload */
    if (!empty($_FILES['bg_image']['name'])) {
        $file     = $_FILES['bg_image'];
        $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxBytes = 10 * 1024 * 1024; /* 10 MB */

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error. Please try again.';
        } elseif (!in_array(mime_content_type($file['tmp_name']), $allowed)) {
            $errors[] = 'Only JPG, PNG, WebP and GIF images are accepted.';
        } elseif ($file['size'] > $maxBytes) {
            $errors[] = 'File too large (max 10 MB).';
        } else {
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fname   = 'slide_' . uniqid() . '.' . $ext;
            $dest    = dirname(__DIR__) . '/assets/images/slides/' . $fname;
            $destDir = dirname($dest);
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                /* Delete old image if it was an uploaded slide image */
                if ($d['bg_image'] && strpos($d['bg_image'], 'assets/images/slides/') !== false) {
                    $old = dirname(__DIR__) . '/' . $d['bg_image'];
                    if (file_exists($old)) unlink($old);
                }
                $d['bg_image'] = 'assets/images/slides/' . $fname;
            } else {
                $errors[] = 'Failed to save uploaded file.';
            }
        }
    }

    if (empty($d['label'])) $errors[] = 'Label / specialty is required.';
    if (empty($d['title'])) $errors[] = 'Main title is required.';

    if (empty($errors)) {
        $newId = dgtec_slide_save($d);
        header('Location: slides.php?saved=1');
        exit;
    }
}

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
        <div class="topbar-avatar">M</div>
        minaboules
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

      <form method="post" enctype="multipart/form-data">
        <?php if ($isEdit): ?>
        <input type="hidden" name="current_bg_image" value="<?= htmlspecialchars($d['bg_image']) ?>" />
        <?php endif; ?>

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

            <div class="img-upload-wrap" id="uploadArea">
              <input type="file" name="bg_image" id="bgImageInput" accept="image/*" onchange="previewImage(this)" />
              <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
              <p><strong>Click to upload</strong> or drag and drop</p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WebP — max 10 MB</p>
            </div>

            <!-- Hidden field to track if image was removed -->
            <input type="hidden" name="current_bg_image" id="currentBgImage" value="<?= htmlspecialchars($d['bg_image']) ?>" />
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
                <label class="form-label">Highlight Colour <small style="text-transform:none;font-weight:400">(leave empty → uses site accent)</small></label>
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
                          placeholder="Hire top-tier technical, managerial and engineering professionals…" required><?= htmlspecialchars($d['description']) ?></textarea>
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
                <label class="form-label">Button 1 — Text</label>
                <input type="text" name="btn1_text" class="form-input"
                       value="<?= htmlspecialchars($d['btn1_text']) ?>"
                       placeholder="e.g. Hire Now" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 1 — Link / URL</label>
                <input type="text" name="btn1_url" class="form-input"
                       value="<?= htmlspecialchars($d['btn1_url']) ?>"
                       placeholder="e.g. contact.php" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 2 — Text</label>
                <input type="text" name="btn2_text" class="form-input"
                       value="<?= htmlspecialchars($d['btn2_text']) ?>"
                       placeholder="e.g. Our Services" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 2 — Link / URL</label>
                <input type="text" name="btn2_url" class="form-input"
                       value="<?= htmlspecialchars($d['btn2_url']) ?>"
                       placeholder="e.g. services.php" />
              </div>
            </div>
          </div>
        </div>

        <!-- ===== SECTION 5: Settings ===== -->
        <div class="card" style="margin-bottom:28px">
          <div class="card-header"><h2><i class="fas fa-toggle-on" style="color:var(--acc)"></i> Visibility</h2></div>
          <div class="card-body">
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:14px">
              <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>
                     style="width:18px;height:18px;accent-color:var(--btn)" />
              <span><strong>Active</strong> — slide will appear in the hero slider on the homepage</span>
            </label>
          </div>
        </div>

        <!-- Save -->
        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="slides.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Slide' : 'Save Slide' ?>
          </button>
        </div>

      </form>
    </div><!-- /.admin-content -->
  </main>
</div>

<script>
/* ---- Gradient preview ---- */
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

/* ---- Highlight colour sync ---- */
document.getElementById('hlColor').addEventListener('input', function() {
  document.getElementById('hlColorText').value = this.value;
});
document.getElementById('hlColorText').addEventListener('input', function() {
  if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
    document.getElementById('hlColor').value = this.value;
  }
  /* Override the hidden field: if text is empty, clear highlight_color */
  document.getElementById('hlColor').name = this.value.trim() ? 'highlight_color_sync' : '';
});

/* ---- Image preview ---- */
function previewImage(input) {
  if (!input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('imgPreview').src = e.target.result;
    document.getElementById('imgPreviewBox').style.display = 'block';
    document.getElementById('uploadArea').style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}

function clearImage() {
  document.getElementById('bgImageInput').value = '';
  document.getElementById('imgPreviewBox').style.display = 'none';
  document.getElementById('uploadArea').style.display = 'block';
  document.getElementById('currentBgImage').value = '';
}

/* Show upload area if no current image */
<?php if (!$d['bg_image']): ?>
document.getElementById('uploadArea').style.display = 'block';
<?php endif; ?>

/* Fix highlight_color field: use the text value on submit */
document.querySelector('form').addEventListener('submit', function() {
  var textVal = document.getElementById('hlColorText').value.trim();
  /* The field "highlight_color" sent to server should be the text value */
  document.querySelectorAll('input[name="highlight_color"]').forEach(function(el) { el.name = '_hc_old'; });
  var hc = document.createElement('input');
  hc.type = 'hidden';
  hc.name = 'highlight_color';
  hc.value = /^#[0-9a-fA-F]{6}$/.test(textVal) ? textVal : '';
  this.appendChild(hc);
});
</script>
</body>
</html>
