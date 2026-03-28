<?php
/**
 * DGTEC — Custom 404 Not Found page
 */
http_response_code(404);

$page_title   = '404 – Page Not Found | DGTEC';
$page_desc    = 'The page you are looking for could not be found.';
$seo_page_key = '404';

require_once __DIR__ . '/includes/admin-db.php';
require_once __DIR__ . '/includes/header.php';
?>

<main style="min-height:72vh;display:flex;align-items:center;justify-content:center;padding:80px 24px;background:var(--dark, #0a0f1e)">
  <div style="text-align:center;max-width:540px;margin:0 auto">

    <!-- Large 404 number -->
    <div style="font-size:clamp(96px,18vw,180px);font-weight:900;line-height:1;
                background:linear-gradient(135deg,#2563eb,#7c3aed);
                -webkit-background-clip:text;-webkit-text-fill-color:transparent;
                background-clip:text;margin-bottom:8px;letter-spacing:-4px">
      404
    </div>

    <h1 style="font-size:clamp(22px,4vw,32px);font-weight:700;color:#f1f5f9;margin:0 0 14px">
      Page Not Found
    </h1>

    <p style="color:#94a3b8;font-size:16px;line-height:1.6;margin:0 0 36px">
      The page you&rsquo;re looking for doesn&rsquo;t exist or has been moved.<br />
      Let&rsquo;s get you back on track.
    </p>

    <div style="display:flex;flex-wrap:wrap;gap:14px;justify-content:center">
      <a href="index.php"
         style="display:inline-flex;align-items:center;gap:8px;padding:13px 28px;
                background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;
                border-radius:50px;font-weight:600;font-size:15px;text-decoration:none;
                transition:.2s;box-shadow:0 4px 20px rgba(37,99,235,.4)">
        <i class="fas fa-house"></i> Go Home
      </a>
      <a href="contact.php"
         style="display:inline-flex;align-items:center;gap:8px;padding:13px 28px;
                border:1.5px solid rgba(255,255,255,.18);color:#e2e8f0;border-radius:50px;
                font-weight:600;font-size:15px;text-decoration:none;transition:.2s">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
