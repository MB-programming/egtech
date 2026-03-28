<?php
/**
 * DGTEC — Custom CMS Page renderer
 * URL: page.php?slug=<slug>
 */
require_once __DIR__ . '/includes/admin-db.php';

$slug    = trim($_GET['slug'] ?? '');
$pg      = $slug ? dgtec_page_get_by_slug($slug) : null;

/* 404 if not found or inactive */
if (!$pg) {
    http_response_code(404);
    $page_title   = '404 – Page Not Found | DGTEC';
    $page_desc    = 'The page you are looking for could not be found.';
    $seo_page_key = '404';
    include __DIR__ . '/includes/header.php';
    ?>
    <main style="min-height:72vh;display:flex;align-items:center;justify-content:center;padding:80px 24px;background:var(--dark,#0a0f1e)">
      <div style="text-align:center;max-width:540px;margin:0 auto">
        <div style="font-size:clamp(96px,18vw,180px);font-weight:900;line-height:1;
                    background:linear-gradient(135deg,#2563eb,#7c3aed);
                    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
                    background-clip:text;margin-bottom:8px;letter-spacing:-4px">404</div>
        <h1 style="font-size:clamp(22px,4vw,32px);font-weight:700;color:#f1f5f9;margin:0 0 14px">Page Not Found</h1>
        <p style="color:#94a3b8;font-size:16px;line-height:1.6;margin:0 0 36px">
          The page you&rsquo;re looking for doesn&rsquo;t exist or has been moved.
        </p>
        <a href="index.php"
           style="display:inline-flex;align-items:center;gap:8px;padding:13px 28px;
                  background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;
                  border-radius:50px;font-weight:600;font-size:15px;text-decoration:none">
          <i class="fas fa-house"></i> Go Home
        </a>
      </div>
    </main>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$page_title   = htmlspecialchars($pg['title']) . ' – DGTEC';
$page_desc    = htmlspecialchars(mb_substr(strip_tags($pg['content']), 0, 160));
$seo_page_key = 'page:' . $slug;

include __DIR__ . '/includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1><?= htmlspecialchars($pg['title']) ?></h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span><?= htmlspecialchars(mb_substr($pg['title'], 0, 60)) ?></span>
    </nav>
  </div>
</section>

<!-- ======= PAGE CONTENT ======= -->
<section style="padding:80px 0;background:var(--dark,#0a0f1e)">
  <div class="container">
    <div class="cms-page-content" style="max-width:860px;margin:0 auto;color:#e2e8f0;font-size:16px;line-height:1.8">
      <?= $pg['content'] ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
