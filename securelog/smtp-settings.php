<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('site_info');

$info = dgtec_site_info();
$msg  = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();

    if (isset($_POST['test_email'])) {
        /* ---- Test send ---- */
        require_once dirname(__DIR__) . '/includes/email.php';
        $testTo  = trim($_POST['test_to'] ?? $info['email'] ?? '');
        $smtpCfg = [
            'host'       => trim($_POST['smtp_host'] ?? ''),
            'port'       => (int)($_POST['smtp_port'] ?? 587),
            'encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
            'username'   => trim($_POST['smtp_username'] ?? ''),
            'password'   => trim($_POST['smtp_password'] ?? '') ?: ($info['smtp_password'] ?? ''),
            'from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'from_name'  => trim($_POST['smtp_from_name'] ?? 'DGTEC'),
        ];
        $html = dgtec_email_build_html('SMTP Test', 'This is a test email from DGTEC Admin.', [
            'Status'   => 'Connected successfully ✓',
            'Sent at'  => date('d M Y H:i:s'),
            'From'     => $smtpCfg['from_name'] . ' <' . $smtpCfg['from_email'] . '>',
        ]);
        $ok = dgtec_smtp_send($smtpCfg, [$testTo], 'DGTEC SMTP Test', $html);
        $msg = $ok ? 'Test email sent successfully to ' . htmlspecialchars($testTo) . '.' : 'Failed to send test email. Please check your SMTP settings.';
        $msgType = $ok ? 'success' : 'error';

    } else {
        /* ---- Save settings ---- */
        $save = [
            'smtp_host'       => trim($_POST['smtp_host'] ?? ''),
            'smtp_port'       => (int)($_POST['smtp_port'] ?? 587),
            'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
            'smtp_username'   => trim($_POST['smtp_username'] ?? ''),
            'smtp_from_name'  => trim($_POST['smtp_from_name'] ?? ''),
            'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
        ];
        /* Only update password if user entered a new one */
        $newPass = trim($_POST['smtp_password'] ?? '');
        if ($newPass !== '') $save['smtp_password'] = $newPass;

        dgtec_site_info_save($save);
        $msg = 'SMTP settings saved.';
        $info = dgtec_site_info(); /* reload */
    }
}

$unreadCount = dgtec_submissions_unread_count();
$activePage  = 'smtp';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>SMTP Settings – DGTEC Admin</title>
  <link rel="stylesheet" href="assets/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
  <style>
    .test-bar { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; margin-top:24px; padding-top:24px; border-top:1px solid var(--border); }
    .test-bar .form-group { flex:1; min-width:220px; margin:0; }
  </style>
</head>
<body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="topbar-title">SMTP <span>Settings</span></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr(admin_current_user(),0,1)) ?></div>
        <?= htmlspecialchars(admin_current_user()) ?>
      </div>
    </div>
    <div class="admin-content">

      <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div><h1>SMTP Settings</h1><p>Configure outgoing email for form notifications.</p></div>
        <a href="form-notifications.php" class="btn btn-secondary"><i class="fas fa-bell"></i> Form Notifications</a>
      </div>

      <form method="post" id="smtpForm">
        <?= csrf_field() ?>
        <div class="card">
          <div class="card-body">
            <h3 style="font-size:15px;font-weight:700;color:var(--primary);margin-bottom:20px"><i class="fas fa-server" style="color:var(--btn);margin-right:8px"></i>Server Configuration</h3>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-input" value="<?= htmlspecialchars($info['smtp_host'] ?? '') ?>" placeholder="e.g. smtp.gmail.com" />
              </div>
              <div class="form-group">
                <label class="form-label">Port</label>
                <select name="smtp_port" class="form-input" onchange="updateEncHint(this.value)">
                  <option value="587" <?= ($info['smtp_port']??587)==587?'selected':'' ?>>587 — STARTTLS (recommended)</option>
                  <option value="465" <?= ($info['smtp_port']??587)==465?'selected':'' ?>>465 — SSL/TLS</option>
                  <option value="25"  <?= ($info['smtp_port']??587)==25?'selected':'' ?>>25 — Plain (no encryption)</option>
                  <option value="2525" <?= ($info['smtp_port']??587)==2525?'selected':'' ?>>2525 — Alternative</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Encryption</label>
                <select name="smtp_encryption" class="form-input" id="encSelect">
                  <option value="tls"  <?= ($info['smtp_encryption']??'tls')==='tls'?'selected':'' ?>>STARTTLS (TLS)</option>
                  <option value="ssl"  <?= ($info['smtp_encryption']??'tls')==='ssl'?'selected':'' ?>>SSL</option>
                  <option value="none" <?= ($info['smtp_encryption']??'tls')==='none'?'selected':'' ?>>None</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="smtp_username" class="form-input" value="<?= htmlspecialchars($info['smtp_username'] ?? '') ?>" placeholder="your@email.com" autocomplete="off" />
              </div>
              <div class="form-group">
                <label class="form-label">Password</label>
                <div style="position:relative">
                  <input type="password" name="smtp_password" id="smtpPass" class="form-input"
                         placeholder="<?= ($info['smtp_password']??'') ? '••••••••• (saved)' : 'Enter password' ?>"
                         autocomplete="new-password" style="padding-right:40px" />
                  <button type="button" onclick="togglePass()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray)">
                    <i class="fas fa-eye" id="passEyeIcon"></i>
                  </button>
                </div>
                <p class="form-hint">Leave empty to keep current password.</p>
              </div>
            </div>

            <h3 style="font-size:15px;font-weight:700;color:var(--primary);margin:28px 0 20px"><i class="fas fa-envelope" style="color:var(--btn);margin-right:8px"></i>Sender Identity</h3>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">From Name</label>
                <input type="text" name="smtp_from_name" class="form-input" value="<?= htmlspecialchars($info['smtp_from_name'] ?? '') ?>" placeholder="e.g. DGTEC Team" />
              </div>
              <div class="form-group">
                <label class="form-label">From Email</label>
                <input type="email" name="smtp_from_email" class="form-input" value="<?= htmlspecialchars($info['smtp_from_email'] ?? '') ?>" placeholder="noreply@yourdomain.com" />
              </div>
            </div>

            <!-- Test email -->
            <div class="test-bar">
              <div class="form-group">
                <label class="form-label">Send Test Email To</label>
                <input type="email" name="test_to" class="form-input" value="<?= htmlspecialchars($info['email'] ?? '') ?>" placeholder="test@email.com" />
              </div>
              <button type="submit" name="test_email" value="1" class="btn btn-secondary" style="white-space:nowrap">
                <i class="fas fa-paper-plane"></i> Send Test
              </button>
            </div>
          </div>
          <div class="card-body" style="border-top:1px solid var(--border);display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save SMTP Settings</button>
          </div>
        </div>
      </form>

      <!-- Quick reference -->
      <div class="card" style="margin-top:20px">
        <div class="card-body">
          <h3 style="font-size:14px;font-weight:700;color:var(--dark);margin-bottom:12px"><i class="fas fa-circle-info" style="color:var(--btn);margin-right:6px"></i>Quick Reference</h3>
          <table style="font-size:13px;border-collapse:collapse;width:100%;max-width:500px">
            <tr style="background:var(--bg)"><th style="padding:8px 12px;text-align:left">Provider</th><th style="padding:8px 12px;text-align:left">Host</th><th style="padding:8px 12px">Port</th><th style="padding:8px 12px">Enc.</th></tr>
            <tr><td style="padding:8px 12px">Gmail</td><td style="padding:8px 12px">smtp.gmail.com</td><td style="padding:8px 12px;text-align:center">587</td><td style="padding:8px 12px;text-align:center">TLS</td></tr>
            <tr style="background:var(--bg)"><td style="padding:8px 12px">Outlook/Office365</td><td style="padding:8px 12px">smtp.office365.com</td><td style="padding:8px 12px;text-align:center">587</td><td style="padding:8px 12px;text-align:center">TLS</td></tr>
            <tr><td style="padding:8px 12px">SendGrid</td><td style="padding:8px 12px">smtp.sendgrid.net</td><td style="padding:8px 12px;text-align:center">587</td><td style="padding:8px 12px;text-align:center">TLS</td></tr>
            <tr style="background:var(--bg)"><td style="padding:8px 12px">Mailgun</td><td style="padding:8px 12px">smtp.mailgun.org</td><td style="padding:8px 12px;text-align:center">587</td><td style="padding:8px 12px;text-align:center">TLS</td></tr>
            <tr><td style="padding:8px 12px">Zoho Mail</td><td style="padding:8px 12px">smtp.zoho.com</td><td style="padding:8px 12px;text-align:center">587</td><td style="padding:8px 12px;text-align:center">TLS</td></tr>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>
<script>
function togglePass() {
  var i = document.getElementById('smtpPass');
  var e = document.getElementById('passEyeIcon');
  if (i.type === 'password') { i.type = 'text'; e.className = 'fas fa-eye-slash'; }
  else { i.type = 'password'; e.className = 'fas fa-eye'; }
}
function updateEncHint(port) {
  var enc = document.getElementById('encSelect');
  if (port === '465') enc.value = 'ssl';
  else if (port === '25') enc.value = 'none';
  else enc.value = 'tls';
}
</script>
</body></html>
