<?php
/* Admin 403 — included by admin_require_permission() */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
$unreadCount = function_exists('dgtec_submissions_unread_count') ? dgtec_submissions_unread_count() : 0;
$activePage  = '';
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Access Denied – DGTEC Admin</title>
<link rel="stylesheet" href="assets/admin.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"/>
</head><body>
<div class="admin-shell">
  <?php include 'includes/sidebar.php'; ?>
  <main class="admin-main">
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:70vh;text-align:center;gap:12px">
      <i class="fas fa-lock" style="font-size:52px;color:#d0d7e3"></i>
      <h1 style="font-size:28px;font-weight:800;color:var(--primary)">Access Denied</h1>
      <p style="color:var(--gray)">You don't have permission to access this page.<br>Contact your administrator.</p>
      <a href="dashboard.php" class="btn btn-primary" style="margin-top:8px"><i class="fas fa-house"></i> Back to Dashboard</a>
    </div>
  </main>
</div></body></html>
