<?php
require_once 'includes/admin-db.php';
require_once 'includes/item-page-renderer.php';
$_db_item = dgtec_item_get_by_slug('service', basename(__FILE__, '.php'));
$_pc = null;
if (!empty($_db_item['page_content'])) {
    $_pcDec = json_decode($_db_item['page_content'], true);
    if (is_array($_pcDec)) $_pc = $_pcDec;
}
$page_title = 'Expert Technical Recruitment – DGTEC';
$page_desc  = 'DGTEC connects Saudi organisations with top-tier technical, managerial and engineering talent. Fast, precise and Vision 2030 aligned recruitment solutions.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Expert Technical Recruitment</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Services</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Expert Technical Recruitment</span>
    </nav>
  </div>
</section>

<?php if ($_pc !== null): ?>
<?= dgtec_render_item_overview($_pc['hero']     ?? []) ?>
<?= dgtec_render_item_stats   ($_pc['stats']    ?? []) ?>
<?= dgtec_render_item_features($_pc['features'] ?? []) ?>
<?php else: ?>

<!-- ======= OVERVIEW ======= -->
<section class="inner-overview">
  <div class="container">
    <div class="inner-overview-grid">
      <div class="inner-overview-text">
        <span class="section-label">Talent Acquisition</span>
        <h2 class="section-title">The Right People.<br>Right Now.</h2>
        <p class="section-desc">
          In a market where top technical talent receives multiple offers within days, speed and precision define success. DGTEC's Expert Technical Recruitment service combines deep Saudi market knowledge, an extensive talent network and AI-powered screening to place the right people — fast.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> Technical, managerial and engineering roles</li>
          <li><i class="fas fa-check-circle"></i> Permanent, contract and interim placements</li>
          <li><i class="fas fa-check-circle"></i> Saudi national &amp; expatriate talent pipelines</li>
          <li><i class="fas fa-check-circle"></i> AI-powered skills matching and screening</li>
          <li><i class="fas fa-check-circle"></i> Saudisation (Nitaqat) compliance guidance</li>
          <li><i class="fas fa-check-circle"></i> 90-day post-placement guarantee</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Start Hiring <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/team.png" alt="Expert Technical Recruitment" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">72<span>h</span></div>
        <p>Average time to first shortlist</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">250<span>+</span></div>
        <p>Technical placements completed</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">95<span>%</span></div>
        <p>Offer acceptance rate</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">90</div>
        <p>Day post-hire guarantee</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">How We Recruit</span>
      <h2 class="section-title">Our Recruitment Capabilities</h2>
      <p class="section-desc">From sourcing to placement — every step is designed to deliver quality and speed.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-magnifying-glass"></i></div>
        <h4>Targeted Talent Sourcing</h4>
        <p>We tap into active and passive candidate pools across Saudi Arabia, GCC and global markets — reaching talent that standard job boards can't find.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-laptop-code"></i></div>
        <h4>Technical Skills Assessment</h4>
        <p>Rigorous technical screening — including domain tests, coding assessments and structured competency interviews — ensures only qualified candidates reach you.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-users-viewfinder"></i></div>
        <h4>Cultural Fit Evaluation</h4>
        <p>Beyond skills, we assess values alignment, communication style and team dynamics to maximise retention and performance after hire.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-gauge-high"></i></div>
        <h4>Fast-Track Placement</h4>
        <p>Our dedicated recruitment pods move at speed — delivering a qualified shortlist within 72 hours for most technical roles.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-flag"></i></div>
        <h4>Saudisation Compliance</h4>
        <p>Expert guidance on Nitaqat requirements, Saudisation quotas and government programme alignment to protect your compliance status.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-handshake-angle"></i></div>
        <h4>Post-Placement Support</h4>
        <p>A 90-day guarantee with dedicated account management — we stay engaged to ensure your new hire settles in and performs from day one.</p>
      </div>

    </div>
  </div>
</section>

<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Ready to Hire Your Next Top Performer?</h2>
      <p>Tell us about your open role and we'll have a shortlist ready within 72 hours.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Start Hiring Now</a>
        <a href="index.php#services" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
