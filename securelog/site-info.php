<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$msg     = '';
$msgType = 'success';

$info = dgtec_site_info();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    /* Handle logo uploads */
    $headerLogo = trim($_POST['current_header_logo'] ?? $info['header_logo']);
    $footerLogo = trim($_POST['current_footer_logo'] ?? $info['footer_logo']);

    if (!empty($_POST['header_logo_uploaded'])) $headerLogo = trim($_POST['header_logo_uploaded']);
    if (!empty($_POST['footer_logo_uploaded']))  $footerLogo = trim($_POST['footer_logo_uploaded']);

    $data = [
        'phone'              => trim($_POST['phone'] ?? ''),
        'email'              => trim($_POST['email'] ?? ''),
        'address'            => trim($_POST['address'] ?? ''),
        'footer_description' => trim($_POST['footer_description'] ?? ''),
        'site_description'   => trim($_POST['site_description'] ?? ''),
        'header_logo'        => $headerLogo,
        'footer_logo'        => $footerLogo,
    ];

    dgtec_site_info_save($data);
    $msg  = 'Site info saved successfully.';
    $info = dgtec_site_info(); /* re-read */
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'site-info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Site Info – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    .upload-progress { display:none;margin-top:10px;background:var(--bg);border-radius:8px;overflow:hidden;height:20px;position:relative;border:1px solid var(--border); }
    .upload-progress.active { display:block; }
    .upload-progress-bar { height:100%;background:linear-gradient(90deg,var(--p),var(--btn));width:0%;transition:width .2s ease;border-radius:8px; }
    .upload-progress-text { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--dark);pointer-events:none; }
    .upload-status { margin-top:6px;font-size:12px;color:var(--gray); }
    .upload-status.error   { color:#dc2626; }
    .upload-status.success { color:#16a34a; }
    .logo-preview { display:flex;align-items:center;gap:16px;padding:16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;margin-bottom:12px; }
    .logo-preview img { max-height:48px;max-width:160px;object-fit:contain; }
    .logo-upload-row { display:flex;gap:12px;align-items:center; }
    .logo-upload-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--white);border:1.5px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;transition:border-color .2s; }
    .logo-upload-btn:hover { border-color:var(--p); }
    .logo-upload-btn input[type=file] { display:none; }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Site <span>Info</span></div>
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
          <h1>Site Info</h1>
          <p>Manage contact details, logos and site description used across the website.</p>
        </div>
      </div>

      <form method="post" id="siteInfoForm">
        <?= csrf_field() ?>

        <!-- Hidden uploaded logo paths -->
        <input type="hidden" name="current_header_logo" id="currentHeaderLogo" value="<?= htmlspecialchars($info['header_logo']) ?>" />
        <input type="hidden" name="header_logo_uploaded" id="headerLogoUploaded" value="" />
        <input type="hidden" name="current_footer_logo" id="currentFooterLogo" value="<?= htmlspecialchars($info['footer_logo']) ?>" />
        <input type="hidden" name="footer_logo_uploaded" id="footerLogoUploaded" value="" />

        <!-- ===== LOGOS ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-image" style="color:var(--acc)"></i> Logos</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <!-- Header Logo -->
              <div class="form-group">
                <label class="form-label">Header Logo</label>
                <div class="logo-preview" id="headerLogoPreview">
                  <?php if ($info['header_logo']): ?>
                  <img src="../<?= htmlspecialchars($info['header_logo']) ?>" id="headerLogoImg" alt="Header Logo" />
                  <?php else: ?>
                  <img src="" id="headerLogoImg" alt="" style="display:none" />
                  <?php endif; ?>
                  <span style="font-size:12px;color:var(--gray)" id="headerLogoName">
                    <?= $info['header_logo'] ? basename($info['header_logo']) : 'No logo set' ?>
                  </span>
                </div>
                <div class="logo-upload-row">
                  <label class="logo-upload-btn">
                    <i class="fas fa-upload"></i> Upload New Logo
                    <input type="file" id="headerLogoInput" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                           onchange="uploadLogo(this,'header')" />
                  </label>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="clearLogo('header')">
                    <i class="fas fa-times"></i> Clear
                  </button>
                </div>
                <div class="upload-progress" id="headerLogoProgress">
                  <div class="upload-progress-bar" id="headerLogoBar"></div>
                  <span class="upload-progress-text" id="headerLogoText">0%</span>
                </div>
                <div class="upload-status" id="headerLogoStatus"></div>
              </div>

              <!-- Footer Logo -->
              <div class="form-group">
                <label class="form-label">Footer Logo</label>
                <div class="logo-preview" id="footerLogoPreview">
                  <?php if ($info['footer_logo']): ?>
                  <img src="../<?= htmlspecialchars($info['footer_logo']) ?>" id="footerLogoImg" alt="Footer Logo" />
                  <?php else: ?>
                  <img src="" id="footerLogoImg" alt="" style="display:none" />
                  <?php endif; ?>
                  <span style="font-size:12px;color:var(--gray)" id="footerLogoName">
                    <?= $info['footer_logo'] ? basename($info['footer_logo']) : 'No logo set' ?>
                  </span>
                </div>
                <div class="logo-upload-row">
                  <label class="logo-upload-btn">
                    <i class="fas fa-upload"></i> Upload New Logo
                    <input type="file" id="footerLogoInput" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                           onchange="uploadLogo(this,'footer')" />
                  </label>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="clearLogo('footer')">
                    <i class="fas fa-times"></i> Clear
                  </button>
                </div>
                <div class="upload-progress" id="footerLogoProgress">
                  <div class="upload-progress-bar" id="footerLogoBar"></div>
                  <span class="upload-progress-text" id="footerLogoText">0%</span>
                </div>
                <div class="upload-status" id="footerLogoStatus"></div>
              </div>

            </div>
          </div>
        </div>

        <!-- ===== CONTACT DETAILS ===== -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h2><i class="fas fa-address-card" style="color:var(--acc)"></i> Contact Details</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group">
                <label class="form-label"><i class="fas fa-phone" style="color:var(--p);margin-right:6px"></i> Phone</label>
                <input type="text" name="phone" class="form-input"
                       value="<?= htmlspecialchars($info['phone']) ?>"
                       placeholder="+966 11 000 0000" />
              </div>

              <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope" style="color:var(--p);margin-right:6px"></i> Email</label>
                <input type="email" name="email" class="form-input"
                       value="<?= htmlspecialchars($info['email']) ?>"
                       placeholder="info@dgtec.com.sa" />
              </div>

              <div class="form-group full">
                <label class="form-label"><i class="fas fa-location-dot" style="color:var(--p);margin-right:6px"></i> Address</label>
                <input type="text" name="address" class="form-input"
                       value="<?= htmlspecialchars($info['address']) ?>"
                       placeholder="Riyadh, Saudi Arabia" />
              </div>

            </div>
          </div>
        </div>

        <!-- ===== DESCRIPTIONS ===== -->
        <div class="card" style="margin-bottom:28px">
          <div class="card-header"><h2><i class="fas fa-align-left" style="color:var(--acc)"></i> Descriptions</h2></div>
          <div class="card-body">
            <div class="form-grid">

              <div class="form-group full">
                <label class="form-label">Footer Description</label>
                <textarea name="footer_description" class="form-textarea" rows="3"
                          placeholder="Short company description shown in the footer…"><?= htmlspecialchars($info['footer_description']) ?></textarea>
                <p class="form-hint">Shown under the logo in the footer.</p>
              </div>

              <div class="form-group full">
                <label class="form-label">Site Meta Description</label>
                <textarea name="site_description" class="form-textarea" rows="3"
                          placeholder="SEO meta description for the website…"><?= htmlspecialchars($info['site_description']) ?></textarea>
                <p class="form-hint">Used as the default meta description tag in the HTML head.</p>
              </div>

            </div>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-floppy-disk"></i> Save Site Info
          </button>
        </div>

      </form>
    </div>
  </main>
</div>

<script>
function uploadLogo(input, slot) {
  if (!input.files || !input.files[0]) return;
  var file        = input.files[0];
  var progressEl  = document.getElementById(slot + 'LogoProgress');
  var barEl       = document.getElementById(slot + 'LogoBar');
  var textEl      = document.getElementById(slot + 'LogoText');
  var statusEl    = document.getElementById(slot + 'LogoStatus');
  var imgEl       = document.getElementById(slot + 'LogoImg');
  var nameEl      = document.getElementById(slot + 'LogoName');
  var uploadedEl  = document.getElementById(slot === 'header' ? 'headerLogoUploaded' : 'footerLogoUploaded');

  progressEl.classList.add('active');
  barEl.style.width = '0%';
  textEl.textContent = '0%';
  statusEl.className = 'upload-status';
  statusEl.textContent = 'Uploading…';

  var formData = new FormData();
  formData.append('image', file);
  formData.append('slot', slot);

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'logo-upload.php', true);
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
        uploadedEl.value = resp.path;
        imgEl.src = resp.preview;
        imgEl.style.display = 'block';
        nameEl.textContent = resp.filename;
        barEl.style.width = '100%';
        textEl.textContent = '100%';
        statusEl.className = 'upload-status success';
        statusEl.textContent = 'Logo uploaded. Click "Save Site Info" to apply.';
      } else {
        progressEl.classList.remove('active');
        statusEl.className = 'upload-status error';
        statusEl.textContent = resp.error || 'Upload failed.';
      }
    } catch(err) {
      progressEl.classList.remove('active');
      statusEl.className = 'upload-status error';
      statusEl.textContent = 'Unexpected server response.';
    }
  });

  xhr.addEventListener('error', function() {
    progressEl.classList.remove('active');
    statusEl.className = 'upload-status error';
    statusEl.textContent = 'Network error. Please try again.';
  });

  xhr.send(formData);
}

function clearLogo(slot) {
  document.getElementById(slot === 'header' ? 'headerLogoUploaded' : 'footerLogoUploaded').value = '';
  document.getElementById(slot === 'header' ? 'currentHeaderLogo' : 'currentFooterLogo').value = '';
  var imgEl  = document.getElementById(slot + 'LogoImg');
  var nameEl = document.getElementById(slot + 'LogoName');
  imgEl.src = '';
  imgEl.style.display = 'none';
  nameEl.textContent = 'No logo set';
}
</script>
</body>
</html>
