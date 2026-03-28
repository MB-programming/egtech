<?php
require_once 'includes/admin-db.php';
$_ab        = dgtec_about_content();
$page_title = htmlspecialchars($_ab['hero_title']) . ' – DGTEC';
$page_desc  = htmlspecialchars(mb_substr($_ab['intro_desc1'], 0, 160));
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1><?= htmlspecialchars($_ab['hero_title']) ?></h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span><?= htmlspecialchars($_ab['hero_title']) ?></span>
    </nav>
  </div>
</section>

<!-- ======= ABOUT INTRO ======= -->
<section class="about-intro">
  <div class="container">
    <div class="about-grid">

      <div class="about-image-wrap">
        <img src="<?= htmlspecialchars($_ab['intro_image'] ?: 'assets/images/our-soul.webp') ?>" alt="<?= htmlspecialchars($_ab['intro_title']) ?>" />
        <?php if ($_ab['badge_number'] || $_ab['badge_text']): ?>
        <div class="about-experience-badge">
          <div class="badge-number"><?= htmlspecialchars($_ab['badge_number']) ?></div>
          <div class="badge-text"><?= htmlspecialchars($_ab['badge_text']) ?></div>
        </div>
        <?php endif; ?>
      </div>

      <div class="about-text">
        <span class="section-label"><?= htmlspecialchars($_ab['intro_label']) ?></span>
        <h2 class="section-title"><?= htmlspecialchars($_ab['intro_title']) ?></h2>
        <?php if ($_ab['intro_desc1']): ?>
        <p class="section-desc" style="margin-bottom:24px;"><?= htmlspecialchars($_ab['intro_desc1']) ?></p>
        <?php endif; ?>
        <?php if ($_ab['intro_desc2']): ?>
        <p style="font-size:15px; color:var(--gray); line-height:1.8; margin-bottom:32px;"><?= htmlspecialchars($_ab['intro_desc2']) ?></p>
        <?php endif; ?>
        <div style="display:flex; gap:16px; flex-wrap:wrap;">
          <a href="#services" class="btn btn-primary">View Services</a>
          <a href="index.php#solutions" class="btn btn-outline">View Solutions</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ======= WHY CHOOSE DGTEC ======= -->
<?php if (!empty($_ab['why_cards'])): ?>
<section class="why-choose-section">
  <div class="container">
    <div style="text-align:center; margin-bottom:16px;">
      <span class="section-label"><?= htmlspecialchars($_ab['why_label']) ?></span>
    </div>
    <h2 class="section-title" style="text-align:center;"><?= htmlspecialchars($_ab['why_title']) ?></h2>
    <div class="why-grid">
      <?php foreach ($_ab['why_cards'] as $_wc): ?>
      <div class="why-card">
        <div class="why-icon"><i class="<?= htmlspecialchars($_wc['icon'] ?: 'fas fa-check') ?>"></i></div>
        <h4><?= htmlspecialchars($_wc['text']) ?></h4>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2><?= htmlspecialchars($_ab['cta_title']) ?></h2>
      <p><?= htmlspecialchars($_ab['cta_desc']) ?></p>
      <div class="cta-btns">
        <a href="<?= htmlspecialchars($_ab['cta_btn1_url']) ?>" class="btn btn-white"><?= htmlspecialchars($_ab['cta_btn1_text']) ?></a>
        <a href="<?= htmlspecialchars($_ab['cta_btn2_url']) ?>" class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,.6);"><?= htmlspecialchars($_ab['cta_btn2_text']) ?></a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
