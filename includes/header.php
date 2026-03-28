<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');

/* Load site_info + SEO helpers if not already loaded */
if (!function_exists('dgtec_site_info')) {
    require_once __DIR__ . '/admin-db.php';
}
$_site        = dgtec_site_info();
$_header_logo = $_site['header_logo'] ?: 'assets/images/logo.webp';

/* ── Determine SEO page key ──
 * Individual pages can set $seo_page_key before including header.php.
 * If not set, fall back to current page basename.
 * For blog-post.php, blog-post.php sets $seo_page_key = 'blog:' . $slug.
 */
$_seo_key  = $seo_page_key ?? $current_page;
$_seo      = dgtec_seo_get($_seo_key);

/* Effective values: per-page SEO → page-level vars → site defaults */
$_eff_title    = $_seo['meta_title']  ?: ($page_title   ?? 'DGTEC – Technological Transformation in The Kingdom');
$_eff_desc     = $_seo['meta_desc']   ?: ($page_desc    ?? ($_site['site_description'] ?: 'DGTEC – Leading integrated solutions company delivering advanced Technical recruitment, outsourcing, AI and digital transformation in Saudi Arabia.'));
$_eff_robots   = $_seo['robots']      ?: 'index,follow';
$_eff_canon    = $_seo['canonical']   ?: '';
$_eff_og_title = $_seo['og_title']    ?: $_eff_title;
$_eff_og_desc  = $_seo['og_desc']     ?: $_eff_desc;
$_eff_og_img   = $_seo['og_image']    ?: '';

/* Favicon: dedicated favicon entry in site_info, else fall back to header logo */
$_favicon_raw  = $_site['favicon'] ?: '';
$_favicon_ext  = $_favicon_raw ? strtolower(pathinfo($_favicon_raw, PATHINFO_EXTENSION)) : '';
$_favicon_mime = match($_favicon_ext) {
    'ico'  => 'image/x-icon',
    'svg'  => 'image/svg+xml',
    'png'  => 'image/png',
    default => 'image/x-icon',
};
$_favicon_url  = $_favicon_raw ?: 'assets/images/favicon.ico';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO: Primary meta -->
  <title><?= htmlspecialchars($_eff_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($_eff_desc) ?>" />
  <meta name="robots" content="<?= htmlspecialchars($_eff_robots) ?>" />
  <?php if ($_eff_canon): ?>
  <link rel="canonical" href="<?= htmlspecialchars($_eff_canon) ?>" />
  <?php endif; ?>

  <!-- SEO: Open Graph -->
  <meta property="og:type"        content="website" />
  <meta property="og:title"       content="<?= htmlspecialchars($_eff_og_title) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($_eff_og_desc) ?>" />
  <?php if ($_eff_og_img): ?>
  <meta property="og:image"       content="<?= htmlspecialchars($_eff_og_img) ?>" />
  <?php endif; ?>

  <!-- SEO: Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="<?= htmlspecialchars($_eff_og_title) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($_eff_og_desc) ?>" />
  <?php if ($_eff_og_img): ?>
  <meta name="twitter:image"       content="<?= htmlspecialchars($_eff_og_img) ?>" />
  <?php endif; ?>

  <!-- Favicon -->
  <link rel="icon" type="<?= $_favicon_mime ?>" href="<?= htmlspecialchars($_favicon_url) ?>" />

  <!-- Fonts & icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Asap:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="css/style.css" />

  <!-- JSON-LD Schema (per-page) -->
  <?php if (!empty($_seo['schema_json'])): ?>
  <script type="application/ld+json"><?= $_seo['schema_json'] ?></script>
  <?php endif; ?>

  <!-- Global head injection (all pages) -->
  <?php if (!empty($_site['global_head_code'])): ?>
  <?= $_site['global_head_code'] . "\n" ?>
  <?php endif; ?>

  <!-- Per-page head injection -->
  <?php if (!empty($_seo['head_code'])): ?>
  <?= $_seo['head_code'] . "\n" ?>
  <?php endif; ?>
</head>
<body>

<!-- ======= HEADER (desktop) ======= -->
<header class="site-header">
  <div class="container">
    <nav class="navbar">

      <a href="index.php" class="nav-logo">
        <img src="<?= htmlspecialchars($_header_logo) ?>" alt="DGTEC Logo" width="140" height="48" />
      </a>

      <ul class="nav-menu" id="nav-menu">
        <li class="nav-item">
          <a href="index.php" class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>">Home</a>
        </li>
        <li class="nav-item">
          <a href="about.php" class="nav-link <?= $current_page === 'about' ? 'active' : '' ?>">About</a>
        </li>
        <li class="nav-item has-dropdown">
          <a href="solutions.php" class="nav-link <?= $current_page === 'solutions' ? 'active' : '' ?>">Our Solutions <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown">
            <li><a href="solutions.php" class="dd-view-all"><i class="fas fa-grip"></i> View All Solutions <i class="fas fa-arrow-right dd-arrow"></i></a></li>
            <li class="dd-sep"></li>
            <?php foreach (dgtec_items_active('solution') as $_hn): ?>
            <li><a href="solution-detail.php?slug=<?= urlencode($_hn['slug']) ?>" class="dd-item">
              <span class="dd-icon"><i class="<?= htmlspecialchars($_hn['icon'] ?: 'fas fa-lightbulb') ?>"></i></span>
              <span class="dd-label"><?= htmlspecialchars($_hn['title']) ?></span>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item has-dropdown">
          <a href="services.php" class="nav-link <?= $current_page === 'services' ? 'active' : '' ?>">Our Services <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown">
            <li><a href="services.php" class="dd-view-all"><i class="fas fa-grip"></i> View All Services <i class="fas fa-arrow-right dd-arrow"></i></a></li>
            <li class="dd-sep"></li>
            <?php foreach (dgtec_items_active('service') as $_hn): ?>
            <li><a href="service-detail.php?slug=<?= urlencode($_hn['slug']) ?>" class="dd-item">
              <span class="dd-icon"><i class="<?= htmlspecialchars($_hn['icon'] ?: 'fas fa-briefcase') ?>"></i></span>
              <span class="dd-label"><?= htmlspecialchars($_hn['title']) ?></span>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item">
          <a href="blog.php" class="nav-link <?= $current_page === 'blog' ? 'active' : '' ?>">Blogs</a>
        </li>
        <li class="nav-item">
          <a href="contact.php" class="nav-link <?= $current_page === 'contact' ? 'active' : '' ?>">Contact</a>
        </li>
      </ul>

      <a href="contact.php" class="btn btn-primary nav-cta-desktop">Free Consultation</a>

      <!-- Hamburger (mobile only) -->
      <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </nav>
  </div>
</header>

<!-- ======= FULL-SCREEN MOBILE MENU ======= -->
<div class="fs-menu" id="fs-menu" aria-hidden="true">

  <!-- Decorative glow -->
  <div class="fs-glow"></div>

  <div class="fs-inner">

    <!-- Top bar -->
    <div class="fs-top">
      <a href="index.php" class="fs-logo">
        <img src="<?= htmlspecialchars($_header_logo) ?>" alt="DGTEC" />
      </a>
      <button class="fs-close" id="fs-close" aria-label="Close menu">
        <span></span><span></span>
      </button>
    </div>

    <!-- Navigation links -->
    <nav class="fs-nav" aria-label="Mobile navigation">
      <ul>

        <li class="fs-item" style="--d:.12s">
          <a href="index.php" class="fs-link">
            <span class="fs-num">01</span>
            <span class="fs-text">Home</span>
          </a>
        </li>

        <li class="fs-item" style="--d:.20s">
          <a href="about.php" class="fs-link">
            <span class="fs-num">02</span>
            <span class="fs-text">About</span>
          </a>
        </li>

        <li class="fs-item has-fs-sub" style="--d:.28s">
          <div class="fs-link fs-toggle" role="button" tabindex="0">
            <span class="fs-num">03</span>
            <span class="fs-text">Our Solutions</span>
            <i class="fas fa-plus fs-plus"></i>
          </div>
          <ul class="fs-sub">
            <li><a href="solutions.php"><i class="fas fa-grip"></i> All Solutions</a></li>
            <?php foreach (dgtec_items_active('solution') as $_mn): ?>
            <li><a href="solution-detail.php?slug=<?= urlencode($_mn['slug']) ?>">
              <i class="<?= htmlspecialchars($_mn['icon'] ?: 'fas fa-lightbulb') ?>"></i>
              <?= htmlspecialchars(mb_substr($_mn['title'], 0, 40)) ?>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="fs-item has-fs-sub" style="--d:.36s">
          <div class="fs-link fs-toggle" role="button" tabindex="0">
            <span class="fs-num">04</span>
            <span class="fs-text">Our Services</span>
            <i class="fas fa-plus fs-plus"></i>
          </div>
          <ul class="fs-sub">
            <li><a href="services.php"><i class="fas fa-grip"></i> All Services</a></li>
            <?php foreach (dgtec_items_active('service') as $_mn): ?>
            <li><a href="service-detail.php?slug=<?= urlencode($_mn['slug']) ?>">
              <i class="<?= htmlspecialchars($_mn['icon'] ?: 'fas fa-briefcase') ?>"></i>
              <?= htmlspecialchars(mb_substr($_mn['title'], 0, 40)) ?>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="fs-item" style="--d:.44s">
          <a href="blog.php" class="fs-link">
            <span class="fs-num">05</span>
            <span class="fs-text">Blog &amp; Insights</span>
          </a>
        </li>

        <li class="fs-item" style="--d:.52s">
          <a href="contact.php" class="fs-link">
            <span class="fs-num">06</span>
            <span class="fs-text">Contact Us</span>
          </a>
        </li>

      </ul>
    </nav>

    <!-- Footer -->
    <div class="fs-footer">
      <a href="contact.php" class="btn btn-primary fs-cta">
        Free Consultation <i class="fas fa-arrow-right"></i>
      </a>
      <div class="fs-socials">
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" aria-label="X / Twitter"><i class="fab fa-x-twitter"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>

  </div><!-- /.fs-inner -->
</div><!-- /.fs-menu -->
