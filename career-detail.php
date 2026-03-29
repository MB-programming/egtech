<?php
require_once 'includes/admin-db.php';

$slug = trim($_GET['slug'] ?? '');
$job  = $slug ? dgtec_career_get_by_slug($slug) : null;
if (!$job) { http_response_code(404); include '404.php'; exit; }

$seo_page_key = 'career:' . $slug;
$page_title   = htmlspecialchars($job['title']) . ' – DGTEC Careers';
$page_desc    = mb_substr(strip_tags($job['description']), 0, 160);

$fields = dgtec_career_fields((int)$job['id']);

/* Format description / requirements: lines starting with - become <li> */
function careerFormat(string $text): string {
    $lines = explode("\n", nl2br(htmlspecialchars($text)));
    $html = ''; $inUl = false;
    foreach ($lines as $line) {
        $raw = strip_tags($line);
        if (preg_match('/^-\s+(.+)/', trim($raw), $m)) {
            if (!$inUl) { $html .= '<ul class="career-list">'; $inUl = true; }
            $html .= '<li>' . htmlspecialchars($m[1]) . '</li>';
        } else {
            if ($inUl) { $html .= '</ul>'; $inUl = false; }
            if (trim($raw)) $html .= '<p>' . $line . '</p>';
        }
    }
    if ($inUl) $html .= '</ul>';
    return $html;
}

include 'includes/header.php';
?>

<style>
.career-list { padding-left:20px; margin:12px 0; }
.career-list li { margin-bottom:6px; color:var(--dark); line-height:1.7; }
.career-body p  { margin-bottom:12px; line-height:1.8; color:var(--dark); }

.apply-form-wrap { background:#fff; border:1.5px solid #e5e9f0; border-radius:14px; padding:32px; }
.apply-form-wrap h3 { font-size:20px; font-weight:800; color:var(--primary); margin-bottom:4px; }
.apply-form-wrap p.sub { font-size:14px; color:var(--gray); margin-bottom:24px; }

.cf-group { margin-bottom:20px; }
.cf-label { font-size:14px; font-weight:600; color:var(--dark); display:block; margin-bottom:6px; }
.cf-label .req { color:#dc2626; margin-left:2px; }
.cf-input { width:100%; padding:10px 14px; border:1.5px solid #d0d7e3; border-radius:8px; font-size:14px; transition:.15s; font-family:inherit; }
.cf-input:focus { border-color:var(--btn); outline:none; box-shadow:0 0 0 3px rgba(3,134,158,.1); }
textarea.cf-input { resize:vertical; min-height:100px; }
.cf-file-label { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border:1.5px dashed #d0d7e3; border-radius:8px; cursor:pointer; font-size:13px; color:var(--gray); transition:.15s; }
.cf-file-label:hover { border-color:var(--btn); color:var(--btn); }
.cf-file-name { font-size:12px; color:var(--btn); margin-top:4px; display:block; }
.apply-submit { width:100%; padding:13px; font-size:16px; font-weight:700; }
.apply-msg { display:none; margin-top:16px; padding:14px 18px; border-radius:8px; font-size:14px; font-weight:600; }
.apply-msg.success { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.apply-msg.error   { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.progress-bar-wrap { display:none; margin-top:8px; background:#e5e9f0; border-radius:20px; height:6px; overflow:hidden; }
.progress-bar { height:100%; width:0%; background:var(--btn); transition:width .3s; }
</style>

<!-- Page Hero -->
<section style="background:linear-gradient(135deg,var(--primary) 0%,#0d6eaa 100%);padding:60px 0;color:#fff">
  <div class="container">
    <a href="careers.php" style="color:rgba(255,255,255,.75);font-size:14px;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;text-decoration:none">
      <i class="fas fa-arrow-left"></i> All Openings
    </a>
    <h1 style="font-size:clamp(26px,4vw,42px);font-weight:800;margin-bottom:12px"><?= htmlspecialchars($job['title']) ?></h1>
    <div style="display:flex;flex-wrap:wrap;gap:16px">
      <?php if ($job['department']): ?>
      <span style="font-size:14px;opacity:.85"><i class="fas fa-building" style="margin-right:5px"></i><?= htmlspecialchars($job['department']) ?></span>
      <?php endif; ?>
      <?php if ($job['location']): ?>
      <span style="font-size:14px;opacity:.85"><i class="fas fa-location-dot" style="margin-right:5px"></i><?= htmlspecialchars($job['location']) ?></span>
      <?php endif; ?>
      <span style="font-size:14px;opacity:.85"><i class="fas fa-clock" style="margin-right:5px"></i><?= htmlspecialchars($job['job_type']) ?></span>
    </div>
  </div>
</section>

<!-- Content -->
<section style="padding:60px 0;background:var(--light-gray)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 420px;gap:40px;align-items:start">

      <!-- Left: Job details -->
      <div>
        <?php if ($job['description']): ?>
        <div class="card" style="margin-bottom:24px">
          <div class="card-body">
            <h2 style="font-size:20px;font-weight:700;color:var(--primary);margin-bottom:16px"><i class="fas fa-file-lines" style="color:var(--btn);margin-right:8px"></i>Job Description</h2>
            <div class="career-body"><?= careerFormat($job['description']) ?></div>
          </div>
        </div>
        <?php endif; ?>

        <?php if ($job['requirements']): ?>
        <div class="card">
          <div class="card-body">
            <h2 style="font-size:20px;font-weight:700;color:var(--primary);margin-bottom:16px"><i class="fas fa-list-check" style="color:var(--btn);margin-right:8px"></i>Requirements</h2>
            <div class="career-body"><?= careerFormat($job['requirements']) ?></div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Right: Application Form -->
      <div>
        <div class="apply-form-wrap">
          <h3>Apply for this Position</h3>
          <p class="sub">Fill in the form below and we'll get back to you soon.</p>

          <?php if (empty($fields)): ?>
          <p style="color:var(--gray);font-size:14px">Application form not configured. Please contact us directly.</p>
          <?php else: ?>

          <form id="applyForm" novalidate>
            <input type="hidden" name="career_id" value="<?= (int)$job['id'] ?>" />
            <?php foreach ($fields as $fi => $field):
              $req = $field['required'] ? ' required' : '';
              $reqMark = $field['required'] ? '<span class="req">*</span>' : '';
              $opts = json_decode($field['options_json'] ?: '[]', true) ?: [];
            ?>
            <div class="cf-group" id="group_<?= $fi ?>">
              <label class="cf-label" for="cf_<?= $fi ?>"><?= htmlspecialchars($field['label']) ?><?= $reqMark ?></label>

              <?php if ($field['field_type'] === 'textarea'): ?>
              <textarea class="cf-input" id="cf_<?= $fi ?>" name="<?= htmlspecialchars($field['field_key']) ?>"
                        placeholder="<?= htmlspecialchars($field['placeholder']) ?>"<?= $req ?>></textarea>

              <?php elseif ($field['field_type'] === 'select'): ?>
              <select class="cf-input" id="cf_<?= $fi ?>" name="<?= htmlspecialchars($field['field_key']) ?>"<?= $req ?>>
                <option value="">— Select —</option>
                <?php foreach ($opts as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                <?php endforeach; ?>
              </select>

              <?php elseif ($field['field_type'] === 'radio'): ?>
              <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:4px">
                <?php foreach ($opts as $oi => $opt): ?>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px">
                  <input type="radio" name="<?= htmlspecialchars($field['field_key']) ?>" value="<?= htmlspecialchars($opt) ?>"<?= $req ?> />
                  <?= htmlspecialchars($opt) ?>
                </label>
                <?php endforeach; ?>
              </div>

              <?php elseif ($field['field_type'] === 'checkbox'): ?>
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                <input type="checkbox" id="cf_<?= $fi ?>" name="<?= htmlspecialchars($field['field_key']) ?>" value="1"<?= $req ?> style="width:16px;height:16px" />
                <?= htmlspecialchars($field['placeholder'] ?: $field['label']) ?>
              </label>

              <?php elseif ($field['field_type'] === 'file'): ?>
              <div>
                <label class="cf-file-label" for="cf_<?= $fi ?>">
                  <i class="fas fa-upload"></i> Choose File
                </label>
                <input type="file" id="cf_<?= $fi ?>" name="<?= htmlspecialchars($field['field_key']) ?>"
                       data-fieldkey="<?= htmlspecialchars($field['field_key']) ?>"
                       accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                       style="display:none"<?= $req ?>
                       onchange="document.getElementById('fname_<?= $fi ?>').textContent = this.files[0] ? this.files[0].name : ''" />
                <span class="cf-file-name" id="fname_<?= $fi ?>"><?= htmlspecialchars($field['placeholder'] ?: 'PDF, DOC, DOCX') ?></span>
              </div>

              <?php else: ?>
              <input type="<?= htmlspecialchars($field['field_type']) ?>" class="cf-input"
                     id="cf_<?= $fi ?>" name="<?= htmlspecialchars($field['field_key']) ?>"
                     placeholder="<?= htmlspecialchars($field['placeholder']) ?>"<?= $req ?> />
              <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <div class="progress-bar-wrap" id="progressWrap"><div class="progress-bar" id="progressBar"></div></div>
            <button type="submit" class="btn btn-primary apply-submit" id="applyBtn">
              <i class="fas fa-paper-plane"></i> Submit Application
            </button>
          </form>

          <div class="apply-msg" id="applyMsg"></div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Responsive -->
<style>
@media (max-width:900px) {
  .container > div[style*="grid-template-columns"] { display:block!important; }
  .apply-form-wrap { margin-top:24px; }
}
</style>

<script>
document.getElementById('applyForm') && document.getElementById('applyForm').addEventListener('submit', function(e) {
  e.preventDefault();
  var form    = this;
  var btn     = document.getElementById('applyBtn');
  var msgEl   = document.getElementById('applyMsg');
  var progWrap = document.getElementById('progressWrap');
  var progBar  = document.getElementById('progressBar');
  var fd = new FormData(form);

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';
  progWrap.style.display = 'block';
  msgEl.style.display = 'none';

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'career-apply.php');
  xhr.upload.onprogress = function(ev) {
    if (ev.lengthComputable) progBar.style.width = (ev.loaded/ev.total*100)+'%';
  };
  xhr.onload = function() {
    progWrap.style.display = 'none';
    try {
      var res = JSON.parse(xhr.responseText);
      if (res.success) {
        form.style.display = 'none';
        msgEl.className = 'apply-msg success';
        msgEl.innerHTML = '<i class="fas fa-check-circle"></i> ' + (res.message || 'Application submitted successfully! We\'ll be in touch soon.');
        msgEl.style.display = 'block';
      } else {
        msgEl.className = 'apply-msg error';
        msgEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (res.error || 'Submission failed. Please try again.');
        msgEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
      }
    } catch(ex) {
      msgEl.className = 'apply-msg error';
      msgEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.';
      msgEl.style.display = 'block';
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
    }
  };
  xhr.onerror = function() {
    progWrap.style.display = 'none';
    msgEl.className = 'apply-msg error';
    msgEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Network error. Please check your connection.';
    msgEl.style.display = 'block';
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
  };
  xhr.send(fd);
});
</script>

<?php include 'includes/footer.php'; ?>
