<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id     = (int)($_GET['id'] ?? 0);
$partner = $id ? dgtec_partner_get($id) : null;
$isEdit  = !empty($partner);
$errors  = [];

$defaults = [
    'id'          => '',
    'position'    => count(dgtec_partners_all()) + 1,
    'is_active'   => 1,
    'name'        => '',
    'logo'        => '',
    'website_url' => '',
];
$d = array_merge($defaults, $partner ?? []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $logo = trim($_POST['current_logo'] ?? '');
    $uploaded = trim($_POST['logo_uploaded'] ?? '');
    if ($uploaded !== '') $logo = $uploaded;

    $d = [
        'id'          => $isEdit ? $id : null,
        'position'    => max(1, (int)($_POST['position'] ?? 1)),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        'name'        => trim($_POST['name'] ?? ''),
        'logo'        => $logo,
        'website_url' => trim($_POST['website_url'] ?? ''),
    ];

    if (empty($d['name'])) $errors[] = 'Partner name is required.';

    if (empty($errors)) {
        dgtec_partner_save($d);
        header('Location: partners.php?saved=1');
        exit;
    }
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'partners';
$pageTitle   = $isEdit ? 'Edit Partner' : 'Add Partner';
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
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $isEdit ? 'Edit' : 'Add' ?> <span>Partner</span></div>
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
          <p><?= $isEdit ? 'Update partner details below.' : 'Fill in the details to add a new partner.' ?></p>
        </div>
        <a href="partners.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Partners</a>
      </div>

      <form method="post" id="partnerForm">
        <?= csrf_field() ?>
        <input type="hidden" name="current_logo" id="currentLogo" value="<?= htmlspecialchars($d['logo']) ?>" />
        <input type="hidden" name="logo_uploaded" id="uploadedLogoPath" value="" />

        <!-- Logo -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-image" style="color:var(--acc)"></i> Partner Logo</h2></div>
          <div class="card-body">

            <?php if ($d['logo']): ?>
            <div class="img-preview-box" id="imgPreviewBox">
              <img src="../<?= htmlspecialchars($d['logo']) ?>" id="imgPreview" alt="Logo" style="object-fit:contain;background:#f8fafc" />
              <button type="button" class="remove-img" onclick="clearLogo()" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <?php else: ?>
            <div id="imgPreviewBox" style="display:none" class="img-preview-box">
              <img src="" id="imgPreview" alt="" style="object-fit:contain;background:#f8fafc" />
              <button type="button" class="remove-img" onclick="clearLogo()" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>

            <div class="img-upload-wrap" id="uploadArea" <?= $d['logo'] ? 'style="display:none"' : '' ?>>
              <input type="file" name="logo_file" id="logoInput"
                     accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                     onchange="handleLogoSelect(this)" />
              <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
              <p><strong>Click to upload</strong> or drag and drop</p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WebP, SVG — max 5 MB</p>
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
          <div class="card-header"><h2><i class="fas fa-text-height" style="color:var(--acc)"></i> Partner Details</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group full">
                <label class="form-label">Partner Name *</label>
                <input type="text" name="name" class="form-input"
                       value="<?= htmlspecialchars($d['name']) ?>"
                       placeholder="e.g. Saudi Aramco" required />
              </div>

              <div class="form-group full">
                <label class="form-label">Website URL</label>
                <input type="url" name="website_url" class="form-input"
                       value="<?= htmlspecialchars($d['website_url']) ?>"
                       placeholder="https://example.com" />
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
              <span><strong>Active</strong> — will appear in the partners marquee strip</span>
            </label>
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="partners.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Update Partner' : 'Save Partner' ?>
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
function handleLogoSelect(input) {
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
  xhr.open('POST', 'partner-upload.php', true);
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
        document.getElementById('uploadedLogoPath').value = resp.path;
        document.getElementById('imgPreview').src = resp.preview;
        document.getElementById('imgPreviewBox').style.display = 'block';
        document.getElementById('uploadArea').style.display = 'none';
        progressBar.style.width = '100%';
        progressText.textContent = '100%';
        statusEl.className = 'upload-status success';
        statusEl.textContent = 'Logo uploaded successfully.';
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

function clearLogo() {
  document.getElementById('logoInput').value = '';
  document.getElementById('uploadedLogoPath').value = '';
  document.getElementById('currentLogo').value = '';
  document.getElementById('imgPreviewBox').style.display = 'none';
  document.getElementById('uploadArea').style.display = 'block';
  document.getElementById('uploadProgress').classList.remove('active');
  document.getElementById('uploadStatus').textContent = '';
}
</script>
</body>
</html>
