<?php
require_once 'includes/admin-db.php';
$solutions  = dgtec_items_active('solution');
$page_title = 'Our Solutions – Digital Onboarding, Automation & Smart Operations | DGTEC';
$page_desc  = 'Explore DGTEC\'s intelligent solutions: Digital Onboarding & Compliance, Enterprise Content & Process Automation, and Tea Boy Smart Internal Operations.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Our Solutions</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Solutions</span>
    </nav>
  </div>
</section>

<!-- ======= SOLUTIONS LISTING ======= -->
<section class="listing-section">
  <div class="container">
    <div class="listing-section-header">
      <span class="section-label">Intelligent Technology</span>
      <h2 class="section-title">Purpose-Built Solutions<br>for Saudi Enterprises</h2>
      <p class="section-desc">
        A comprehensive suite of AI-driven solutions designed to digitise, automate and optimise your operations — fully aligned with Saudi Vision 2030.
      </p>
    </div>

    <div class="listing-cards">

      <?php foreach ($solutions as $i => $sol): ?>
      <?php
        $features  = array_filter(explode('|', $sol['features']));
        $detailUrl = htmlspecialchars($sol['slug'] . '.php');
      ?>
      <a href="<?= $detailUrl ?>" class="listing-card <?= $sol['is_reversed'] ? 'reversed' : '' ?>" style="text-decoration:none;color:inherit;display:flex">
        <div class="listing-card-image">
          <img src="<?= htmlspecialchars($sol['image']) ?>" alt="<?= htmlspecialchars($sol['title']) ?>" loading="lazy" />
          <div class="listing-card-image-overlay">
            <div class="listing-card-icon-large"><i class="<?= htmlspecialchars($sol['icon']) ?>"></i></div>
          </div>
        </div>
        <div class="listing-card-content" data-num="<?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>">
          <span class="section-label">Solution <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
          <h3><?= htmlspecialchars($sol['title']) ?></h3>
          <p><?= htmlspecialchars($sol['description']) ?></p>
          <?php if ($features): ?>
          <div class="listing-feature-pills">
            <?php foreach ($features as $f): ?>
            <span class="listing-feature-pill"><i class="fas fa-check"></i> <?= htmlspecialchars(trim($f)) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <span class="listing-card-link">Explore This Solution <i class="fas fa-arrow-right"></i></span>
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
      <h2>Not Sure Which Solution Fits?</h2>
      <p>Our experts will assess your operations and recommend the right starting point — at no cost to you.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Book a Free Assessment</a>
        <a href="services.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">View Our Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
