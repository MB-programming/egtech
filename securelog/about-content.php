<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$defaultAbout = [
    'hero_title'   => 'About',
    'intro_label'  => 'Who we are?',
    'intro_title'  => 'We are DGTEC',
    'intro_desc1'  => 'A leading integrated solutions company delivering advanced Technical recruitment and outsourcing services, Squad-as-a-Service, AI and digital transformation, in addition to Data Solutions.',
    'intro_desc2'  => 'With a strong presence in both government and private sectors, we empower our clients to achieve measurable results — not just promises. Our +6 years of experience have enabled us to build a reputation built on trust, innovation, and excellence.',
    'badge_number' => '6+',
    'badge_text'   => 'Years of Experience',
    'intro_image'  => 'assets/images/our-soul.webp',
    'why_label'    => 'Why Choose Us',
    'why_title'    => 'Why Choose DGTEC?',
    'why_cards'    => [
        ['icon' => 'fas fa-award',       'text' => 'Our +6 years of experience enabled us to deliver results not just promises.'],
        ['icon' => 'fas fa-flag',         'text' => 'Full compliance with Saudi Kingdom Vision 2030.'],
        ['icon' => 'fas fa-puzzle-piece', 'text' => 'Provide our clients with tailored solutions, not just ready ones.'],
        ['icon' => 'fas fa-globe',        'text' => 'Deep Saudi / GCC market knowledge and expertise.'],
        ['icon' => 'fas fa-cubes',        'text' => 'Provide Integrated Services: Recruitment, Squad-as-a-Service, Automation & Digital Transformation.'],
        ['icon' => 'fas fa-handshake',    'text' => 'Partnership with global platforms like Zenoo, Newgen and others.'],
    ],
    'cta_title'    => 'Ready to Transform Your Business?',
    'cta_desc'     => 'Let us show you how DGTEC can drive real results for your organization.',
    'cta_btn1_text'=> 'Free Consultation',
    'cta_btn1_url' => 'contact.php',
    'cta_btn2_text'=> 'Our Services',
    'cta_btn2_url' => 'index.php#services',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $data = [
        'hero_title'    => trim($_POST['hero_title'] ?? $defaultAbout['hero_title']),
        'intro_label'   => trim($_POST['intro_label'] ?? $defaultAbout['intro_label']),
        'intro_title'   => trim($_POST['intro_title'] ?? $defaultAbout['intro_title']),
        'intro_desc1'   => trim($_POST['intro_desc1'] ?? $defaultAbout['intro_desc1']),
        'intro_desc2'   => trim($_POST['intro_desc2'] ?? $defaultAbout['intro_desc2']),
        'badge_number'  => trim($_POST['badge_number'] ?? $defaultAbout['badge_number']),
        'badge_text'    => trim($_POST['badge_text'] ?? $defaultAbout['badge_text']),
        'intro_image'   => trim($_POST['intro_image'] ?? $defaultAbout['intro_image']),
        'why_label'     => trim($_POST['why_label'] ?? $defaultAbout['why_label']),
        'why_title'     => trim($_POST['why_title'] ?? $defaultAbout['why_title']),
        'why_cards'     => json_decode(trim($_POST['why_cards_json'] ?? '[]'), true) ?: $defaultAbout['why_cards'],
        'cta_title'     => trim($_POST['cta_title'] ?? $defaultAbout['cta_title']),
        'cta_desc'      => trim($_POST['cta_desc'] ?? $defaultAbout['cta_desc']),
        'cta_btn1_text' => trim($_POST['cta_btn1_text'] ?? $defaultAbout['cta_btn1_text']),
        'cta_btn1_url'  => trim($_POST['cta_btn1_url'] ?? $defaultAbout['cta_btn1_url']),
        'cta_btn2_text' => trim($_POST['cta_btn2_text'] ?? $defaultAbout['cta_btn2_text']),
        'cta_btn2_url'  => trim($_POST['cta_btn2_url'] ?? $defaultAbout['cta_btn2_url']),
    ];

    dgtec_site_info_save(['about_content_json' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

    header('Location: about-content.php?saved=1');
    exit;
}

$info = dgtec_site_info();
$d    = $defaultAbout;
if (!empty($info['about_content_json'])) {
    $parsed = json_decode($info['about_content_json'], true);
    if (is_array($parsed)) $d = array_merge($defaultAbout, $parsed);
}

$whyCardsJson = json_encode($d['why_cards'], JSON_UNESCAPED_UNICODE);

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'about-content';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Content – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    .form-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:24px; flex-wrap:wrap; }
    .form-tab  { padding:10px 18px; font-size:13px; font-weight:600; color:var(--gray); cursor:pointer;
                 border:none; background:none; border-bottom:3px solid transparent; margin-bottom:-2px;
                 transition:.15s; display:flex; align-items:center; gap:6px; }
    .form-tab.active { color:var(--btn); border-bottom-color:var(--btn); }
    .form-tab:hover  { color:var(--dark); }
    .form-panel      { display:none; }
    .form-panel.active { display:block; }

    .dynamic-item { background:var(--bg); border:1px solid var(--border); border-radius:10px; padding:16px; margin-bottom:12px; position:relative; }
    .dynamic-item-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .dynamic-item-title  { font-size:13px; font-weight:700; color:var(--dark); }
    .btn-remove { background:none; border:1.5px solid #dc2626; color:#dc2626; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600; transition:.15s; }
    .btn-remove:hover { background:#dc2626; color:#fff; }
    .btn-add { display:inline-flex; align-items:center; gap:6px; margin-top:4px; }

    .icon-preview-wrap { display:flex; align-items:center; gap:10px; }
    .icon-preview { font-size:22px; color:var(--btn); min-width:28px; text-align:center; }
  </style>
</head>
<body>
<div class="admin-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">About <span>Content</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>

    <div class="admin-content">

      <?php if (isset($_GET['saved'])): ?>
      <div class="alert alert-success">About content saved successfully.</div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1>About Content</h1>
          <p>Manage all text content displayed on the About page.</p>
        </div>
      </div>

      <form method="post" id="aboutContentForm">
        <?= csrf_field() ?>
        <input type="hidden" name="why_cards_json" id="why_cards_json" value="<?= htmlspecialchars($whyCardsJson) ?>" />

        <div class="card">
          <div class="card-body" style="padding-bottom:0">
            <div class="form-tabs">
              <button type="button" class="form-tab active" data-tab="hero"><i class="fas fa-image"></i> Hero</button>
              <button type="button" class="form-tab" data-tab="intro"><i class="fas fa-building"></i> Intro</button>
              <button type="button" class="form-tab" data-tab="why"><i class="fas fa-check-circle"></i> Why Choose</button>
              <button type="button" class="form-tab" data-tab="cta"><i class="fas fa-bullhorn"></i> CTA</button>
            </div>
          </div>

          <!-- Hero -->
          <div class="form-panel active card-body" id="tab-hero">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Hero Title</label>
                <input type="text" name="hero_title" class="form-input" value="<?= htmlspecialchars($d['hero_title']) ?>" />
              </div>
            </div>
          </div>

          <!-- Intro -->
          <div class="form-panel card-body" id="tab-intro">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="intro_label" class="form-input" value="<?= htmlspecialchars($d['intro_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="intro_title" class="form-input" value="<?= htmlspecialchars($d['intro_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description Paragraph 1</label>
                <textarea name="intro_desc1" class="form-textarea" rows="3"><?= htmlspecialchars($d['intro_desc1']) ?></textarea>
              </div>
              <div class="form-group full">
                <label class="form-label">Description Paragraph 2</label>
                <textarea name="intro_desc2" class="form-textarea" rows="3"><?= htmlspecialchars($d['intro_desc2']) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Badge Number</label>
                <input type="text" name="badge_number" class="form-input" value="<?= htmlspecialchars($d['badge_number']) ?>" placeholder="6+" />
              </div>
              <div class="form-group">
                <label class="form-label">Badge Text</label>
                <input type="text" name="badge_text" class="form-input" value="<?= htmlspecialchars($d['badge_text']) ?>" placeholder="Years of Experience" />
              </div>
              <div class="form-group full">
                <label class="form-label">Intro Image</label>
                <input type="hidden" name="intro_image" id="intro_image_val" value="<?= htmlspecialchars($d['intro_image']) ?>" />
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                  <button type="button" class="btn btn-secondary" onclick="uploadAboutIntroImage()">
                    <i class="fas fa-upload"></i> Upload Image
                  </button>
                  <span id="intro_image_name" style="font-size:13px;color:var(--gray)"><?= htmlspecialchars($d['intro_image'] ?: 'No image selected') ?></span>
                </div>
                <div id="intro_image_preview" style="margin-top:10px;<?= $d['intro_image'] ? '' : 'display:none' ?>">
                  <img src="../<?= htmlspecialchars($d['intro_image']) ?>" alt="Intro Image" style="max-height:140px;border-radius:8px;border:1px solid var(--border)" />
                </div>
                <input type="file" id="intro_image_file" accept="image/*" style="display:none" onchange="handleAboutIntroFile(this)" />
              </div>
            </div>
          </div>

          <!-- Why Choose -->
          <div class="form-panel card-body" id="tab-why">
            <div class="form-grid" style="margin-bottom:24px">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="why_label" class="form-input" value="<?= htmlspecialchars($d['why_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="why_title" class="form-input" value="<?= htmlspecialchars($d['why_title']) ?>" />
              </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <strong style="font-size:14px">Why Cards</strong>
              <button type="button" class="btn btn-secondary btn-sm btn-add" onclick="addWhyItem()">
                <i class="fas fa-plus"></i> Add Card
              </button>
            </div>
            <div id="whyItems"></div>
          </div>

          <!-- CTA -->
          <div class="form-panel card-body" id="tab-cta">
            <div class="form-grid">
              <div class="form-group full">
                <label class="form-label">Title</label>
                <input type="text" name="cta_title" class="form-input" value="<?= htmlspecialchars($d['cta_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="cta_desc" class="form-textarea" rows="3"><?= htmlspecialchars($d['cta_desc']) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Button 1 Text</label>
                <input type="text" name="cta_btn1_text" class="form-input" value="<?= htmlspecialchars($d['cta_btn1_text']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 1 URL</label>
                <input type="text" name="cta_btn1_url" class="form-input" value="<?= htmlspecialchars($d['cta_btn1_url']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 2 Text</label>
                <input type="text" name="cta_btn2_text" class="form-input" value="<?= htmlspecialchars($d['cta_btn2_text']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Button 2 URL</label>
                <input type="text" name="cta_btn2_url" class="form-input" value="<?= htmlspecialchars($d['cta_btn2_url']) ?>" />
              </div>
            </div>
          </div>

          <div class="card-body" style="border-top:1px solid var(--border);padding-top:20px;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-floppy-disk"></i> Save About Content
            </button>
          </div>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
(function() {
  var tabs = document.querySelectorAll('.form-tab');
  tabs.forEach(function(btn) {
    btn.addEventListener('click', function() {
      tabs.forEach(function(t) { t.classList.remove('active'); });
      document.querySelectorAll('.form-panel').forEach(function(p) { p.classList.remove('active'); });
      btn.classList.add('active');
      document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
  });
})();

var whyData = <?= $whyCardsJson ?>;

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function renderIconPreview(input) {
  var wrap = input.closest('.icon-preview-wrap');
  var preview = wrap ? wrap.querySelector('.icon-preview') : null;
  if (preview) preview.className = 'icon-preview ' + input.value.trim();
}

function buildWhyItem(data, idx) {
  data = data || {icon:'fas fa-check', text:''};
  var div = document.createElement('div');
  div.className = 'dynamic-item';
  div.innerHTML =
    '<div class="dynamic-item-header">' +
      '<span class="dynamic-item-title">Card ' + (idx + 1) + '</span>' +
      '<button type="button" class="btn-remove" onclick="removeWhyItem(this)"><i class="fas fa-trash"></i> Remove</button>' +
    '</div>' +
    '<div class="form-grid">' +
      '<div class="form-group">' +
        '<label class="form-label">FA Icon Class</label>' +
        '<div class="icon-preview-wrap">' +
          '<i class="icon-preview ' + escHtml(data.icon) + '"></i>' +
          '<input type="text" class="form-input why-icon" value="' + escHtml(data.icon) + '" oninput="renderIconPreview(this)" placeholder="fas fa-check" />' +
        '</div>' +
      '</div>' +
      '<div class="form-group full">' +
        '<label class="form-label">Text</label>' +
        '<textarea class="form-textarea why-text" rows="2">' + escHtml(data.text) + '</textarea>' +
      '</div>' +
    '</div>';
  return div;
}

function addWhyItem() {
  var container = document.getElementById('whyItems');
  container.appendChild(buildWhyItem(null, container.children.length));
}

function removeWhyItem(btn) {
  btn.closest('.dynamic-item').remove();
  var items = document.querySelectorAll('#whyItems .dynamic-item-title');
  items.forEach(function(el, i) { el.textContent = 'Card ' + (i + 1); });
}

function serializeWhyCards() {
  var result = [];
  document.querySelectorAll('#whyItems .dynamic-item').forEach(function(item) {
    result.push({
      icon: item.querySelector('.why-icon').value.trim(),
      text: item.querySelector('.why-text').value.trim()
    });
  });
  document.getElementById('why_cards_json').value = JSON.stringify(result);
}

document.getElementById('aboutContentForm').addEventListener('submit', function() {
  serializeWhyCards();
});

function uploadAboutIntroImage() {
  document.getElementById('intro_image_file').click();
}

function handleAboutIntroFile(input) {
  if (!input.files || !input.files[0]) return;
  var fd = new FormData();
  fd.append('file', input.files[0]);
  fd.append('csrf_token', document.querySelector('[name=csrf_token]').value);
  fetch('item-upload.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        document.getElementById('intro_image_val').value = res.path;
        document.getElementById('intro_image_name').textContent = res.path;
        var preview = document.getElementById('intro_image_preview');
        preview.style.display = '';
        preview.innerHTML = '<img src="../' + res.path + '" alt="Intro Image" style="max-height:140px;border-radius:8px;border:1px solid var(--border)" />';
      } else {
        alert(res.error || 'Upload failed');
      }
    })
    .catch(function() { alert('Upload error'); });
}

(function init() {
  var wc = document.getElementById('whyItems');
  whyData.forEach(function(item, i) { wc.appendChild(buildWhyItem(item, i)); });
})();
</script>
</body>
</html>
