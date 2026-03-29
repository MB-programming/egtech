<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('site_info');

/* ---- Defined form keys & their fields ---- */
$formDefs = [
    'contact' => [
        'label'  => 'Contact Form',
        'icon'   => 'fas fa-envelope',
        'fields' => ['name' => 'Name', 'email' => 'Email', 'mobile' => 'Phone', 'service' => 'Service', 'message' => 'Message'],
        'vars'   => ['{{name}}', '{{email}}', '{{service}}'],
    ],
    'career' => [
        'label'  => 'Career Application Form',
        'icon'   => 'fas fa-user-tie',
        'fields' => ['career_title' => 'Job Title', 'full_name' => 'Applicant Name', 'email' => 'Email', 'phone' => 'Phone', 'cover_letter' => 'Cover Letter'],
        'vars'   => ['{{career_title}}', '{{full_name}}', '{{email}}'],
        'note'   => 'Fields shown here are common defaults. All submitted fields are always available in the email.',
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $formKey = $_POST['form_key'] ?? '';
    if (isset($formDefs[$formKey])) {
        $emails  = array_filter(array_map('trim', preg_split('/[\n,]+/', $_POST['to_emails'] ?? '')));
        $fields  = array_keys(array_filter($_POST['fields'] ?? [], fn($v) => $v == '1'));
        dgtec_form_notification_save($formKey, [
            'is_enabled'    => isset($_POST['is_enabled']) ? 1 : 0,
            'to_emails'     => $emails,
            'subject'       => trim($_POST['subject'] ?? ''),
            'fields'        => $fields,
            'reply_to_field'=> trim($_POST['reply_to_field'] ?? 'email'),
        ]);
        header('Location: form-notifications.php?saved=' . $formKey); exit;
    }
}

$configs     = [];
foreach ($formDefs as $key => $_) {
    $configs[$key] = dgtec_form_notification_get($key);
}
$smtpOk      = !empty(dgtec_smtp_config()['host']);
$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'smtp';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Form Notifications – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
  <style>
    .fn-card { border:1.5px solid var(--border); border-radius:12px; overflow:hidden; margin-bottom:28px; }
    .fn-head { padding:18px 24px; background:var(--bg); display:flex; align-items:center; justify-content:space-between; gap:12px; border-bottom:1px solid var(--border); }
    .fn-head h3 { font-size:16px; font-weight:700; color:var(--primary); margin:0; display:flex; align-items:center; gap:8px; }
    .fn-body { padding:24px; }
    .toggle-switch { position:relative; display:inline-block; width:44px; height:24px; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-track { position:absolute; inset:0; background:#d1d5db; border-radius:34px; cursor:pointer; transition:.2s; }
    .toggle-track:before { content:''; position:absolute; height:18px; width:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
    .toggle-switch input:checked + .toggle-track { background:var(--btn); }
    .toggle-switch input:checked + .toggle-track:before { transform:translateX(20px); }
    .email-tag { display:inline-flex; align-items:center; gap:5px; background:rgba(3,134,158,.1); color:var(--btn); padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; margin:3px; }
    .var-chip { display:inline-block; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; padding:1px 7px; font-size:12px; font-family:monospace; cursor:pointer; margin:2px; }
    .var-chip:hover { background:var(--btn); color:#fff; border-color:var(--btn); }
    .field-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:8px; margin-top:8px; }
    .field-item { display:flex; align-items:center; gap:8px; padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; cursor:pointer; font-size:13px; transition:.15s; }
    .field-item:has(input:checked) { border-color:var(--btn); background:rgba(3,134,158,.05); }
    .field-item input { width:14px; height:14px; accent-color:var(--btn); }
  </style>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">Form <span>Notifications</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(),0,1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <?php if (isset($_GET['saved'])): ?>
      <div class="alert alert-success">Notification settings saved.</div>
      <?php endif; ?>

      <?php if (!$smtpOk): ?>
      <div class="alert alert-error" style="display:flex;align-items:center;gap:10px">
        <i class="fas fa-triangle-exclamation"></i>
        SMTP is not configured yet.
        <a href="smtp-settings.php" style="font-weight:700;color:inherit;text-decoration:underline">Configure SMTP →</a>
      </div>
      <?php endif; ?>

      <div class="page-header">
        <div><h1>Form Notifications</h1><p>Send email alerts when forms are submitted.</p></div>
        <a href="smtp-settings.php" class="btn btn-secondary"><i class="fas fa-server"></i> SMTP Settings</a>
      </div>

      <?php foreach ($formDefs as $formKey => $def):
        $cfg    = $configs[$formKey];
        $emails = json_decode($cfg['to_emails'] ?: '[]', true) ?: [];
        $fields = json_decode($cfg['fields_json'] ?: '[]', true) ?: [];
      ?>
      <div class="fn-card">
        <div class="fn-head">
          <h3><i class="<?= $def['icon'] ?>"></i> <?= $def['label'] ?></h3>
          <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--gray)">
            <span><?= $cfg['is_enabled'] ? '<span style="color:#16a34a;font-weight:600"><i class="fas fa-circle" style="font-size:8px"></i> Active</span>' : '<span style="color:var(--gray)">Inactive</span>' ?></span>
          </div>
        </div>
        <div class="fn-body">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="form_key" value="<?= $formKey ?>" />

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
              <label class="toggle-switch">
                <input type="checkbox" name="is_enabled" value="1" <?= $cfg['is_enabled'] ? 'checked' : '' ?> />
                <span class="toggle-track"></span>
              </label>
              <div>
                <strong style="font-size:14px">Enable notifications for this form</strong>
                <div style="font-size:12px;color:var(--gray)">Send an email every time someone submits this form.</div>
              </div>
            </div>

            <div class="form-grid">
              <!-- To Emails -->
              <div class="form-group full">
                <label class="form-label">Send notifications to</label>
                <textarea name="to_emails" class="form-input" rows="3"
                          placeholder="Enter email addresses, one per line or comma-separated"
                          style="resize:vertical"><?= htmlspecialchars(implode("\n", $emails)) ?></textarea>
                <p class="form-hint">You can enter multiple email addresses.</p>
              </div>

              <!-- Subject -->
              <div class="form-group full">
                <label class="form-label">Email Subject</label>
                <input type="text" name="subject" class="form-input"
                       value="<?= htmlspecialchars($cfg['subject']) ?>"
                       placeholder="e.g. New <?= $def['label'] ?> from {{name}}" />
                <p class="form-hint" style="margin-top:6px">
                  Available variables:
                  <?php foreach ($def['vars'] as $v): ?>
                  <span class="var-chip" onclick="insertVar(this, '<?= $formKey ?>')"><?= $v ?></span>
                  <?php endforeach; ?>
                </p>
              </div>

              <!-- Reply-To Field -->
              <div class="form-group">
                <label class="form-label">Reply-To Field</label>
                <select name="reply_to_field" class="form-input">
                  <?php foreach ($def['fields'] as $fk => $fl): ?>
                  <option value="<?= $fk ?>" <?= ($cfg['reply_to_field'] ?? 'email') === $fk ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fl) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <p class="form-hint">Used as the Reply-To header in the email.</p>
              </div>
            </div>

            <!-- Fields to include -->
            <div style="margin-top:8px">
              <label class="form-label">Fields to include in email body</label>
              <p style="font-size:12px;color:var(--gray);margin-bottom:8px">
                Leave all unchecked to include ALL fields.
                <?php if (!empty($def['note'])): ?>
                <br><em><?= htmlspecialchars($def['note']) ?></em>
                <?php endif; ?>
              </p>
              <div class="field-grid">
                <?php foreach ($def['fields'] as $fk => $fl): ?>
                <div class="field-item" onclick="this.querySelector('input').click()">
                  <input type="checkbox" name="fields[<?= $fk ?>]" value="1"
                         <?= in_array($fk, $fields) ? 'checked' : '' ?>
                         onclick="event.stopPropagation()" />
                  <span><?= htmlspecialchars($fl) ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div style="margin-top:20px;display:flex;justify-content:flex-end">
              <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save <?= $def['label'] ?> Settings</button>
            </div>
          </form>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </main>
</div>
<script>
function insertVar(chip, formKey) {
  var subjectInputs = document.querySelectorAll('input[name="subject"]');
  var formKeyInputs = document.querySelectorAll('input[name="form_key"]');
  for (var i = 0; i < formKeyInputs.length; i++) {
    if (formKeyInputs[i].value === formKey) {
      var input = formKeyInputs[i].closest('form').querySelector('input[name="subject"]');
      if (input) {
        var pos = input.selectionStart || input.value.length;
        input.value = input.value.slice(0, pos) + chip.textContent + input.value.slice(pos);
        input.focus();
        input.setSelectionRange(pos + chip.textContent.length, pos + chip.textContent.length);
      }
      break;
    }
  }
}
</script>
</body></html>
