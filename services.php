<?php
require_once 'includes/admin-db.php';
$services   = dgtec_items_active('service');
$page_title = 'Our Services – Technical Recruitment, Outsourcing & Digital Transformation | DGTEC';
$page_desc  = 'DGTEC offers Expert Technical Recruitment, Scalable Outsourcing, Enterprise Digital Transformation, Tech Squad-as-a-Service and Data Handling Solutions in Saudi Arabia.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Our Services</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Services</span>
    </nav>
  </div>
</section>

<!-- ======= SERVICES LISTING ======= -->
<section class="listing-section">
  <div class="container">
    <div class="listing-section-header">
      <span class="section-label">What We Offer</span>
      <h2 class="section-title">End-to-End Services for<br>Modern Enterprises</h2>
      <p class="section-desc">
        From hiring exceptional talent to transforming your entire digital infrastructure — DGTEC delivers the full spectrum of technology and people services your business needs to lead in The Kingdom.
      </p>
    </div>

    <div class="listing-cards">

      <?php foreach ($services as $i => $svc): ?>
      <?php
        $features  = array_filter(explode('|', $svc['features']));
        $detailUrl = 'service-detail.php?slug=' . urlencode($svc['slug']);
      ?>
      <a href="<?= $detailUrl ?>" class="listing-card <?= $svc['is_reversed'] ? 'reversed' : '' ?>" style="text-decoration:none;color:inherit;display:flex">
        <div class="listing-card-image">
          <img src="<?= htmlspecialchars($svc['image']) ?>" alt="<?= htmlspecialchars($svc['title']) ?>" loading="lazy" />
          <div class="listing-card-image-overlay">
            <div class="listing-card-icon-large"><i class="<?= htmlspecialchars($svc['icon']) ?>"></i></div>
          </div>
        </div>
        <div class="listing-card-content" data-num="<?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>">
          <span class="section-label">Service <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
          <h3><?= htmlspecialchars($svc['title']) ?></h3>
          <p><?= htmlspecialchars($svc['description']) ?></p>
          <?php if ($features): ?>
          <div class="listing-feature-pills">
            <?php foreach ($features as $f): ?>
            <span class="listing-feature-pill"><i class="fas fa-check"></i> <?= htmlspecialchars(trim($f)) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <span class="listing-card-link">Explore This Service <i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Ready to Get Started?</h2>
      <p>Book a free consultation and let our experts design the right solution for your business.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Free Consultation</a>
        <a href="solutions.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">View Our Solutions</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
