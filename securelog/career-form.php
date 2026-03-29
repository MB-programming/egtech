<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$job = $id ? dgtec_career_get($id) : null;

$defaults = [
    'id' => 0, 'title' => '', 'slug' => '', 'department' => '',
    'location' => '', 'job_type' => 'Full-time', 'description' => '',
    'requirements' => '', 'is_active' => 1, 'position' => 0,
];
$d = $job ? array_merge($defaults, $job) : $defaults;

/* existing form fields */
$existingFields = $id ? dgtec_career_fields($id) : [];

/* default form fields for new jobs */
$defaultFields = [
    ['field_key'=>'full_name',   'field_type'=>'text',     'label'=>'Full Name',     'placeholder'=>'Your full name',         'required'=>1, 'options_json'=>'[]'],
    ['field_key'=>'email',       'field_type'=>'email',    'label'=>'Email Address', 'placeholder'=>'your@email.com',          'required'=>1, 'options_json'=>'[]'],
    ['field_key'=>'phone',       'field_type'=>'tel',      'label'=>'Phone Number',  'placeholder'=>'+966 5x xxx xxxx',        'required'=>1, 'options_json'=>'[]'],
    ['field_key'=>'cv_file',     'field_type'=>'file',     'label'=>'Upload CV',     'placeholder'=>'PDF, DOC, DOCX (max 5MB)','required'=>1, 'options_json'=>'[]'],
    ['field_key'=>'cover_letter','field_type'=>'textarea', 'label'=>'Cover Letter',  'placeholder'=>'Tell us about yourself…', 'required'=>0, 'options_json'=>'[]'],
];
$formFields = count($existingFields) ? $existingFields : $defaultFields;

/* ---- Save ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    if (!$slug && $title) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    }
    $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $slug)));

    $data = [
        'id'           => $id,
        'title'        => $title,
        'slug'         => $slug,
        'department'   => trim($_POST['department'] ?? ''),
        'location'     => trim($_POST['location'] ?? ''),
        'job_type'     => trim($_POST['job_type'] ?? 'Full-time'),
        'description'  => trim($_POST['description'] ?? ''),
        'requirements' => trim($_POST['requirements'] ?? ''),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'position'     => (int)($_POST['position'] ?? 0),
    ];

    $savedId = dgtec_career_save($data);

    /* save form fields */
    $fields = json_decode(trim($_POST['form_fields_json'] ?? '[]'), true);
    if (is_array($fields)) {
        dgtec_career_fields_save($savedId, $fields);
    }

    header('Location: careers.php?saved=1'); exit;
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'careers';
$pageTitle   = $id ? 'Edit Job' : 'New Job';
$fieldsJson  = json_encode($formFields, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?> – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="assets/icon-picker.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <style>
    .form-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:24px; flex-wrap:wrap; }
    .form-tab  { padding:10px 18px; font-size:13px; font-weight:600; color:var(--gray); cursor:pointer;
                 border:none; background:none; border-bottom:3px solid transparent; margin-bottom:-2px;
                 transition:.15s; display:flex; align-items:center; gap:6px; }
    .form-tab.active { color:var(--btn); border-bottom-color:var(--btn); }
    .form-panel { display:none; } .form-panel.active { display:block; }

    .fb-item { background:var(--bg); border:1px solid var(--border); border-radius:10px; padding:16px; margin-bottom:12px; position:relative; cursor:grab; }
    .fb-item.dragging { opacity:.4; }
    .fb-item-header { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .fb-drag-handle { color:var(--gray); cursor:grab; font-size:14px; }
    .fb-item-title  { font-size:13px; font-weight:700; color:var(--dark); flex:1; }
    .fb-item-type   { font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; background:rgba(3,134,158,.1); color:var(--btn); }
    .btn-remove { background:none; border:1.5px solid #dc2626; color:#dc2626; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600; transition:.15s; cursor:pointer; }
    .btn-remove:hover { background:#dc2626; color:#fff; }

    .fb-add-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .btn-add-field { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:12px; font-weight:600; color:var(--dark); cursor:pointer; background:#fff; transition:.15s; }
    .btn-add-field:hover { border-color:var(--btn); color:var(--btn); background:rgba(3,134,158,.05); }

    .options-list { margin-top:8px; }
    .option-row { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
    .option-row input { flex:1; }
  </style>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title"><?= $pageTitle ?> <span>– Careers</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(), 0, 1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <div class="page-header">
        <div><h1><?= $pageTitle ?></h1></div>
        <a href="careers.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <form method="post" id="careerForm">
        <?= csrf_field() ?>
        <input type="hidden" name="form_fields_json" id="form_fields_json" value="<?= htmlspecialchars($fieldsJson) ?>" />

        <div class="card">
          <div class="card-body" style="padding-bottom:0">
            <div class="form-tabs">
              <button type="button" class="form-tab active" data-tab="details"><i class="fas fa-briefcase"></i> Job Details</button>
              <button type="button" class="form-tab" data-tab="description"><i class="fas fa-file-lines"></i> Description</button>
              <button type="button" class="form-tab" data-tab="form-builder"><i class="fas fa-wpforms"></i> Application Form</button>
            </div>
          </div>

          <!-- Tab: Details -->
          <div class="form-panel active card-body" id="tab-details">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Job Title <span style="color:#dc2626">*</span></label>
                <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($d['title']) ?>"
                       required oninput="autoSlug(this)" placeholder="e.g. Senior PHP Developer" />
              </div>
              <div class="form-group">
                <label class="form-label">Slug (URL)</label>
                <input type="text" name="slug" id="slugInput" class="form-input" value="<?= htmlspecialchars($d['slug']) ?>"
                       placeholder="auto-generated" />
                <p class="form-hint">careers.php?slug=<span id="slugPreview"><?= htmlspecialchars($d['slug'] ?: 'auto') ?></span></p>
              </div>
              <div class="form-group">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-input" value="<?= htmlspecialchars($d['department']) ?>" placeholder="e.g. Engineering" />
              </div>
              <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input" value="<?= htmlspecialchars($d['location']) ?>" placeholder="e.g. Riyadh, Saudi Arabia" />
              </div>
              <div class="form-group">
                <label class="form-label">Job Type</label>
                <select name="job_type" class="form-input">
                  <?php foreach (['Full-time','Part-time','Contract','Internship','Remote','Hybrid'] as $t): ?>
                  <option value="<?= $t ?>" <?= $d['job_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="position" class="form-input" value="<?= (int)$d['position'] ?>" min="0" />
              </div>
              <div class="form-group full">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                  <input type="checkbox" name="is_active" value="1" <?= $d['is_active'] ? 'checked' : '' ?> style="width:16px;height:16px" />
                  <strong>Active</strong> — show this job on the careers page
                </label>
              </div>
            </div>
          </div>

          <!-- Tab: Description -->
          <div class="form-panel card-body" id="tab-description">
            <div class="form-group" style="margin-bottom:24px">
              <label class="form-label">Job Description</label>
              <textarea name="description" class="form-textarea" rows="8"
                        placeholder="Describe the role, responsibilities, and what the candidate will be doing…"><?= htmlspecialchars($d['description']) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Requirements & Qualifications</label>
              <textarea name="requirements" class="form-textarea" rows="8"
                        placeholder="List the required skills, experience, and qualifications…"><?= htmlspecialchars($d['requirements']) ?></textarea>
              <p class="form-hint">Tip: Start each point with a dash (-) for a clean bullet list.</p>
            </div>
          </div>

          <!-- Tab: Form Builder -->
          <div class="form-panel card-body" id="tab-form-builder">
            <p style="font-size:13px;color:var(--gray);margin-bottom:16px">
              Build the application form. Drag to reorder fields.
            </p>

            <div class="fb-add-bar">
              <button type="button" class="btn-add-field" onclick="fbAdd('text')"><i class="fas fa-font"></i> Text</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('email')"><i class="fas fa-at"></i> Email</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('tel')"><i class="fas fa-phone"></i> Phone</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('number')"><i class="fas fa-hashtag"></i> Number</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('textarea')"><i class="fas fa-align-left"></i> Textarea</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('select')"><i class="fas fa-list"></i> Dropdown</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('radio')"><i class="fas fa-circle-dot"></i> Radio</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('checkbox')"><i class="fas fa-check-square"></i> Checkbox</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('file')"><i class="fas fa-upload"></i> File Upload</button>
              <button type="button" class="btn-add-field" onclick="fbAdd('date')"><i class="fas fa-calendar"></i> Date</button>
            </div>

            <div id="fbItems"></div>
          </div>

          <div class="card-body" style="border-top:1px solid var(--border);padding-top:20px;display:flex;justify-content:flex-end;gap:12px">
            <a href="careers.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Job</button>
          </div>
        </div>
      </form>

    </div>
  </main>
</div>

<script>
/* ---- Tabs ---- */
(function(){
  var tabs = document.querySelectorAll('.form-tab');
  tabs.forEach(function(btn){
    btn.addEventListener('click', function(){
      tabs.forEach(function(t){ t.classList.remove('active'); });
      document.querySelectorAll('.form-panel').forEach(function(p){ p.classList.remove('active'); });
      btn.classList.add('active');
      document.getElementById('tab-'+btn.dataset.tab).classList.add('active');
    });
  });
})();

/* ---- Slug ---- */
function autoSlug(input) {
  var slugInput = document.getElementById('slugInput');
  if (!slugInput.dataset.manual) {
    var s = input.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    slugInput.value = s;
    document.getElementById('slugPreview').textContent = s || 'auto';
  }
}
document.getElementById('slugInput').addEventListener('input', function(){
  this.dataset.manual = '1';
  document.getElementById('slugPreview').textContent = this.value || 'auto';
});

/* ============================================================
   FORM BUILDER
   ============================================================ */
var fbData = <?= $fieldsJson ?>;

function escH(s){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

var typeLabels = {
  text:'Text Input', email:'Email', tel:'Phone', number:'Number',
  textarea:'Textarea', select:'Dropdown', radio:'Radio', checkbox:'Checkbox',
  file:'File Upload', date:'Date'
};

function fbBuildItem(f, idx) {
  var opts = [];
  try { opts = JSON.parse(f.options_json || '[]'); } catch(e){}
  var hasOptions = (f.field_type === 'select' || f.field_type === 'radio');

  var optHtml = '';
  if (hasOptions) {
    optHtml = '<div class="form-group full" style="margin-top:8px">' +
      '<label class="form-label" style="font-size:12px">Options (one per line)</label>' +
      '<div class="options-list" id="opts_'+idx+'">' +
      opts.map(function(o){ return '<div class="option-row"><input type="text" class="form-input opt-val" value="'+escH(o)+'" placeholder="Option text" /><button type="button" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px" onclick="this.closest(\'.option-row\').remove()"><i class="fas fa-xmark"></i></button></div>'; }).join('') +
      '</div>' +
      '<button type="button" class="btn btn-secondary btn-sm" style="margin-top:6px" onclick="fbAddOption(this)"><i class="fas fa-plus"></i> Add Option</button>' +
    '</div>';
  }

  var div = document.createElement('div');
  div.className = 'fb-item';
  div.draggable = true;
  div.innerHTML =
    '<div class="fb-item-header">' +
      '<span class="fb-drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
      '<span class="fb-item-title">' + escH(f.label || 'Untitled') + '</span>' +
      '<span class="fb-item-type">' + escH(typeLabels[f.field_type] || f.field_type) + '</span>' +
      '<button type="button" class="btn-remove" onclick="this.closest(\'.fb-item\').remove();fbSerialize()"><i class="fas fa-trash"></i></button>' +
    '</div>' +
    '<div class="form-grid">' +
      '<div class="form-group">' +
        '<label class="form-label" style="font-size:12px">Label</label>' +
        '<input type="text" class="form-input fb-label" value="' + escH(f.label) + '" placeholder="Field label"' +
        ' oninput="this.closest(\'.fb-item\').querySelector(\'.fb-item-title\').textContent=this.value||\'Untitled\'" />' +
      '</div>' +
      '<div class="form-group">' +
        '<label class="form-label" style="font-size:12px">Placeholder</label>' +
        '<input type="text" class="form-input fb-placeholder" value="' + escH(f.placeholder) + '" placeholder="Hint text" />' +
      '</div>' +
      '<div class="form-group">' +
        '<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;margin-top:20px">' +
          '<input type="checkbox" class="fb-required" ' + (f.required ? 'checked' : '') + ' style="width:15px;height:15px" />' +
          'Required' +
        '</label>' +
      '</div>' +
      '<input type="hidden" class="fb-type" value="' + escH(f.field_type) + '" />' +
      '<input type="hidden" class="fb-key"  value="' + escH(f.field_key)  + '" />' +
      optHtml +
    '</div>';

  /* drag events */
  div.addEventListener('dragstart', function(e){ e.dataTransfer.effectAllowed='move'; window._fbDrag=div; div.classList.add('dragging'); });
  div.addEventListener('dragend',   function(){ div.classList.remove('dragging'); fbSerialize(); });
  div.addEventListener('dragover',  function(e){ e.preventDefault(); var list=document.getElementById('fbItems'); var after=fbDragAfter(list,e.clientY); if(after===null) list.appendChild(window._fbDrag); else list.insertBefore(window._fbDrag,after); });

  return div;
}

function fbDragAfter(container, y) {
  var els = [...container.querySelectorAll('.fb-item:not(.dragging)')];
  return els.reduce(function(closest, el) {
    var box = el.getBoundingClientRect();
    var offset = y - box.top - box.height/2;
    if (offset < 0 && offset > closest.offset) return { offset: offset, el: el };
    return closest;
  }, { offset: Number.NEGATIVE_INFINITY }).el || null;
}

function fbAdd(type) {
  var key = type + '_' + Date.now();
  var f = { field_key: key, field_type: type, label: typeLabels[type] || type, placeholder: '', required: 0, options_json: '[]' };
  var list = document.getElementById('fbItems');
  list.appendChild(fbBuildItem(f, list.children.length));
  fbSerialize();
}

function fbAddOption(btn) {
  var list = btn.previousElementSibling;
  var row = document.createElement('div');
  row.className = 'option-row';
  row.innerHTML = '<input type="text" class="form-input opt-val" placeholder="Option text" /><button type="button" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px" onclick="this.closest(\'.option-row\').remove()"><i class="fas fa-xmark"></i></button>';
  list.appendChild(row);
}

function fbSerialize() {
  var result = [];
  document.querySelectorAll('#fbItems .fb-item').forEach(function(item, i) {
    var opts = [];
    item.querySelectorAll('.opt-val').forEach(function(o){ if(o.value.trim()) opts.push(o.value.trim()); });
    result.push({
      field_key:    item.querySelector('.fb-key').value || ('field_'+i),
      field_type:   item.querySelector('.fb-type').value,
      label:        item.querySelector('.fb-label').value.trim(),
      placeholder:  item.querySelector('.fb-placeholder').value.trim(),
      required:     item.querySelector('.fb-required').checked ? 1 : 0,
      options_json: JSON.stringify(opts),
    });
  });
  document.getElementById('form_fields_json').value = JSON.stringify(result);
}

document.getElementById('careerForm').addEventListener('submit', function(){ fbSerialize(); });

/* init */
(function(){
  var list = document.getElementById('fbItems');
  fbData.forEach(function(f, i){ list.appendChild(fbBuildItem(f, i)); });
})();
</script>
</body>
</html>
