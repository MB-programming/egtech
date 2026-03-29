<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$defaults = [
    'services_label' => 'What We Do',
    'services_title' => 'Explore Our Services',
    'services_desc'  => 'A full spectrum of technology and talent services to power your business in a rapidly evolving market.',
    'services_count' => 5,
    'solutions_label' => 'Our Solutions',
    'solutions_title' => 'Our Solutions',
    'solutions_desc'  => 'A comprehensive suite of intelligent AI and automation services...',
    'solutions_count' => 3,
    'process_label' => 'How We Work',
    'process_title' => 'Our Business Process Road',
    'process_desc'  => 'From the first conversation to long-term digital success...',
    'achievements_label' => 'Our Track Record',
    'achievements_title' => 'Our Achievements',
    'achievements_desc'  => 'Numbers that reflect the trust...',
    'partners_label' => 'Our Partners',
    'partners_title' => 'Trusted By Leading Brands',
    'testimonials_label' => 'Client Feedback',
    'testimonials_title' => 'Our Clients Says',
    'testimonials_desc'  => 'Real results, real relationships...',
    'contact_label'        => 'Get In Touch',
    'contact_title'        => "Let's Start Your\nDigital Journey",
    'contact_desc'         => 'Ready to transform your business?...',
    'contact_form_title'   => 'Send Us a Message',
    'contact_form_subtitle'=> "We'll respond within 24 hours.",
];

$defaultProcess = '[{"icon":"fas fa-magnifying-glass-chart","title":"Discovery & Assessment","desc":"We start by deeply understanding your business goals, current challenges, and technology landscape."},{"icon":"fas fa-pencil-ruler","title":"Strategy & Design","desc":"Our experts craft a tailored digital roadmap aligned with your Vision 2030 objectives."},{"icon":"fas fa-robot","title":"Build & Automate","desc":"We implement smart automation, AI-driven workflows and enterprise systems with minimal disruption."},{"icon":"fas fa-chart-line","title":"Scale & Grow","desc":"We stay with you post-launch \u2014 monitoring, optimising and scaling your digital capabilities."}]';

$defaultAchievements = '[{"icon":"fas fa-check-circle","number":"250","suffix":"+","label":"Completed Tasks"},{"icon":"fas fa-folder-open","number":"120","suffix":"+","label":"Successful Projects"},{"icon":"fas fa-rocket","number":"85","suffix":"+","label":"Delivered Projects"},{"icon":"fas fa-handshake","number":"60","suffix":"+","label":"Happy Clients"}]';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $textData = [];
    foreach (array_keys($defaults) as $key) {
        $textData[$key] = trim($_POST[$key] ?? $defaults[$key]);
    }

    dgtec_site_info_save([
        'home_content_json'     => json_encode($textData, JSON_UNESCAPED_UNICODE),
        'home_process_json'     => trim($_POST['home_process_json'] ?? $defaultProcess),
        'home_achievements_json'=> trim($_POST['home_achievements_json'] ?? $defaultAchievements),
    ]);

    header('Location: home-content.php?saved=1');
    exit;
}

$info = dgtec_site_info();

$saved_text = $info['home_content_json'] ?? '';
$d = $defaults;
if ($saved_text) {
    $parsed = json_decode($saved_text, true);
    if (is_array($parsed)) $d = array_merge($defaults, $parsed);
}

$_pd = json_decode(!empty($info['home_process_json']) ? $info['home_process_json'] : $defaultProcess, true);
$processJson = json_encode(is_array($_pd) && count($_pd) ? $_pd : json_decode($defaultProcess, true), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

$_ad = json_decode(!empty($info['home_achievements_json']) ? $info['home_achievements_json'] : $defaultAchievements, true);
$achievementsJson = json_encode(is_array($_ad) && count($_ad) ? $_ad : json_decode($defaultAchievements, true), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'home-content';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home Content – DGTEC Admin</title>
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
      <div class="topbar-title">Home <span>Content</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>

    <div class="admin-content">

      <?php if (isset($_GET['saved'])): ?>
      <div class="alert alert-success">Home content saved successfully.</div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1>Home Content</h1>
          <p>Manage all text content displayed on the homepage.</p>
        </div>
      </div>

      <form method="post" id="homeContentForm">
        <?= csrf_field() ?>
        <input type="hidden" name="home_process_json" id="home_process_json" value="<?= htmlspecialchars($processJson) ?>" />
        <input type="hidden" name="home_achievements_json" id="home_achievements_json" value="<?= htmlspecialchars($achievementsJson) ?>" />

        <div class="card">
          <div class="card-body" style="padding-bottom:0">
            <div class="form-tabs">
              <button type="button" class="form-tab active" data-tab="services"><i class="fas fa-briefcase"></i> Services</button>
              <button type="button" class="form-tab" data-tab="solutions"><i class="fas fa-lightbulb"></i> Solutions</button>
              <button type="button" class="form-tab" data-tab="process"><i class="fas fa-route"></i> Process Steps</button>
              <button type="button" class="form-tab" data-tab="achievements"><i class="fas fa-trophy"></i> Achievements</button>
              <button type="button" class="form-tab" data-tab="partners"><i class="fas fa-handshake"></i> Partners</button>
              <button type="button" class="form-tab" data-tab="testimonials"><i class="fas fa-star"></i> Testimonials</button>
              <button type="button" class="form-tab" data-tab="contact"><i class="fas fa-envelope"></i> Contact</button>
            </div>
          </div>

          <!-- Services -->
          <div class="form-panel active card-body" id="tab-services">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="services_label" class="form-input" value="<?= htmlspecialchars($d['services_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="services_title" class="form-input" value="<?= htmlspecialchars($d['services_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="services_desc" class="form-textarea" rows="3"><?= htmlspecialchars($d['services_desc']) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Count to Display (1–10)</label>
                <input type="number" name="services_count" class="form-input" min="1" max="10" value="<?= (int)$d['services_count'] ?>" />
              </div>
            </div>
          </div>

          <!-- Solutions -->
          <div class="form-panel card-body" id="tab-solutions">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="solutions_label" class="form-input" value="<?= htmlspecialchars($d['solutions_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="solutions_title" class="form-input" value="<?= htmlspecialchars($d['solutions_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="solutions_desc" class="form-textarea" rows="3"><?= htmlspecialchars($d['solutions_desc']) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Count to Display (1–10)</label>
                <input type="number" name="solutions_count" class="form-input" min="1" max="10" value="<?= (int)$d['solutions_count'] ?>" />
              </div>
            </div>
          </div>

          <!-- Process Steps -->
          <div class="form-panel card-body" id="tab-process">
            <div class="form-grid" style="margin-bottom:24px">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="process_label" class="form-input" value="<?= htmlspecialchars($d['process_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="process_title" class="form-input" value="<?= htmlspecialchars($d['process_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="process_desc" class="form-textarea" rows="2"><?= htmlspecialchars($d['process_desc']) ?></textarea>
              </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <strong style="font-size:14px">Process Steps</strong>
              <button type="button" class="btn btn-secondary btn-sm btn-add" onclick="addProcessItem()">
                <i class="fas fa-plus"></i> Add Step
              </button>
            </div>
            <div id="processItems"></div>
          </div>

          <!-- Achievements -->
          <div class="form-panel card-body" id="tab-achievements">
            <div class="form-grid" style="margin-bottom:24px">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="achievements_label" class="form-input" value="<?= htmlspecialchars($d['achievements_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="achievements_title" class="form-input" value="<?= htmlspecialchars($d['achievements_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="achievements_desc" class="form-textarea" rows="2"><?= htmlspecialchars($d['achievements_desc']) ?></textarea>
              </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <strong style="font-size:14px">Achievement Items</strong>
              <button type="button" class="btn btn-secondary btn-sm btn-add" onclick="addAchievementItem()">
                <i class="fas fa-plus"></i> Add Item
              </button>
            </div>
            <div id="achievementItems"></div>
          </div>

          <!-- Partners -->
          <div class="form-panel card-body" id="tab-partners">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="partners_label" class="form-input" value="<?= htmlspecialchars($d['partners_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="partners_title" class="form-input" value="<?= htmlspecialchars($d['partners_title']) ?>" />
              </div>
            </div>
          </div>

          <!-- Testimonials -->
          <div class="form-panel card-body" id="tab-testimonials">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="testimonials_label" class="form-input" value="<?= htmlspecialchars($d['testimonials_label']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="testimonials_title" class="form-input" value="<?= htmlspecialchars($d['testimonials_title']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="testimonials_desc" class="form-textarea" rows="3"><?= htmlspecialchars($d['testimonials_desc']) ?></textarea>
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div class="form-panel card-body" id="tab-contact">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Section Label</label>
                <input type="text" name="contact_label" class="form-input" value="<?= htmlspecialchars($d['contact_label']) ?>" />
              </div>
              <div class="form-group full">
                <label class="form-label">Section Title (multiline)</label>
                <textarea name="contact_title" class="form-textarea" rows="2"><?= htmlspecialchars($d['contact_title']) ?></textarea>
              </div>
              <div class="form-group full">
                <label class="form-label">Description</label>
                <textarea name="contact_desc" class="form-textarea" rows="3"><?= htmlspecialchars($d['contact_desc']) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Form Title</label>
                <input type="text" name="contact_form_title" class="form-input" value="<?= htmlspecialchars($d['contact_form_title']) ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Form Subtitle</label>
                <input type="text" name="contact_form_subtitle" class="form-input" value="<?= htmlspecialchars($d['contact_form_subtitle']) ?>" />
              </div>
            </div>
          </div>

          <div class="card-body" style="border-top:1px solid var(--border);padding-top:20px;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-floppy-disk"></i> Save Home Content
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

var processData     = <?= $processJson ?>;
var achievementsData= <?= $achievementsJson ?>;

function renderIconPreview(input) {
  var wrap = input.closest('.icon-preview-wrap');
  var preview = wrap ? wrap.querySelector('.icon-preview') : null;
  if (preview) {
    preview.className = 'icon-preview ' + input.value.trim();
  }
}

function buildProcessItem(data, idx) {
  data = data || {icon:'fas fa-star', title:'', desc:''};
  var div = document.createElement('div');
  div.className = 'dynamic-item';
  div.innerHTML =
    '<div class="dynamic-item-header">' +
      '<span class="dynamic-item-title">Step ' + (idx + 1) + '</span>' +
      '<button type="button" class="btn-remove" onclick="removeItem(this, \'process\')"><i class="fas fa-trash"></i> Remove</button>' +
    '</div>' +
    '<div class="form-grid">' +
      '<div class="form-group">' +
        '<label class="form-label">FA Icon Class</label>' +
        '<div class="icon-preview-wrap">' +
          '<i class="icon-preview ' + escHtml(data.icon) + '"></i>' +
          '<input type="text" class="form-input proc-icon" value="' + escHtml(data.icon) + '" oninput="renderIconPreview(this)" placeholder="fas fa-star" />' +
        '</div>' +
      '</div>' +
      '<div class="form-group">' +
        '<label class="form-label">Title</label>' +
        '<input type="text" class="form-input proc-title" value="' + escHtml(data.title) + '" />' +
      '</div>' +
      '<div class="form-group full">' +
        '<label class="form-label">Description</label>' +
        '<textarea class="form-textarea proc-desc" rows="2">' + escHtml(data.desc) + '</textarea>' +
      '</div>' +
    '</div>';
  return div;
}

function buildAchievementItem(data, idx) {
  data = data || {icon:'fas fa-star', number:'', suffix:'+', label:''};
  var div = document.createElement('div');
  div.className = 'dynamic-item';
  div.innerHTML =
    '<div class="dynamic-item-header">' +
      '<span class="dynamic-item-title">Item ' + (idx + 1) + '</span>' +
      '<button type="button" class="btn-remove" onclick="removeItem(this, \'achievement\')"><i class="fas fa-trash"></i> Remove</button>' +
    '</div>' +
    '<div class="form-grid">' +
      '<div class="form-group">' +
        '<label class="form-label">FA Icon Class</label>' +
        '<div class="icon-preview-wrap">' +
          '<i class="icon-preview ' + escHtml(data.icon) + '"></i>' +
          '<input type="text" class="form-input ach-icon" value="' + escHtml(data.icon) + '" oninput="renderIconPreview(this)" placeholder="fas fa-star" />' +
        '</div>' +
      '</div>' +
      '<div class="form-group">' +
        '<label class="form-label">Number</label>' +
        '<input type="text" class="form-input ach-number" value="' + escHtml(data.number) + '" placeholder="250" />' +
      '</div>' +
      '<div class="form-group">' +
        '<label class="form-label">Suffix</label>' +
        '<input type="text" class="form-input ach-suffix" value="' + escHtml(data.suffix) + '" placeholder="+" />' +
      '</div>' +
      '<div class="form-group">' +
        '<label class="form-label">Label</label>' +
        '<input type="text" class="form-input ach-label" value="' + escHtml(data.label) + '" />' +
      '</div>' +
    '</div>';
  return div;
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function refreshLabels(containerId, prefix) {
  var items = document.querySelectorAll('#' + containerId + ' .dynamic-item-title');
  items.forEach(function(el, i) { el.textContent = prefix + ' ' + (i + 1); });
}

function addProcessItem() {
  var container = document.getElementById('processItems');
  var idx = container.children.length;
  container.appendChild(buildProcessItem(null, idx));
}

function addAchievementItem() {
  var container = document.getElementById('achievementItems');
  var idx = container.children.length;
  container.appendChild(buildAchievementItem(null, idx));
}

function removeItem(btn, type) {
  btn.closest('.dynamic-item').remove();
  if (type === 'process') refreshLabels('processItems', 'Step');
  else refreshLabels('achievementItems', 'Item');
}

function serializeProcess() {
  var result = [];
  document.querySelectorAll('#processItems .dynamic-item').forEach(function(item) {
    result.push({
      icon : item.querySelector('.proc-icon').value.trim(),
      title: item.querySelector('.proc-title').value.trim(),
      desc : item.querySelector('.proc-desc').value.trim()
    });
  });
  document.getElementById('home_process_json').value = JSON.stringify(result);
}

function serializeAchievements() {
  var result = [];
  document.querySelectorAll('#achievementItems .dynamic-item').forEach(function(item) {
    result.push({
      icon  : item.querySelector('.ach-icon').value.trim(),
      number: item.querySelector('.ach-number').value.trim(),
      suffix: item.querySelector('.ach-suffix').value.trim(),
      label : item.querySelector('.ach-label').value.trim()
    });
  });
  document.getElementById('home_achievements_json').value = JSON.stringify(result);
}

document.getElementById('homeContentForm').addEventListener('submit', function() {
  serializeProcess();
  serializeAchievements();
});

(function init() {
  var pc = document.getElementById('processItems');
  processData.forEach(function(item, i) { pc.appendChild(buildProcessItem(item, i)); });
  var ac = document.getElementById('achievementItems');
  achievementsData.forEach(function(item, i) { ac.appendChild(buildAchievementItem(item, i)); });
})();
</script>
</body>
</html>
