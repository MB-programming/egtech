<?php
require_once 'includes/admin-db.php';
$_db_item = dgtec_item_get_by_page_url('service', basename(__FILE__));
$page_title = 'Enterprise Digital Transformation – DGTEC';
$page_desc  = 'DGTEC delivers end-to-end enterprise digital transformation powered by Zenoo and Newgen — AI-driven automation, smart workflows and Vision 2030 alignment for Saudi organisations.';
include 'includes/header.php';
if (!empty($_db_item['page_content'])): ?>
<div class="container" style="padding-top:60px;padding-bottom:60px">
  <?= $_db_item['page_content'] ?>
</div>
<?php include 'includes/footer.php'; exit; endif; ?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Enterprise Digital Transformation</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Services</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Enterprise Digital Transformation</span>
    </nav>
  </div>
</section>

<!-- ======= OVERVIEW ======= -->
<section class="inner-overview">
  <div class="container">
    <div class="inner-overview-grid">
      <div class="inner-overview-text">
        <span class="section-label">Digital Transformation</span>
        <h2 class="section-title">From Legacy Systems<br>to Digital Leader.</h2>
        <p class="section-desc">
          Powered by Zenoo and Newgen, DGTEC's Enterprise Digital Transformation service takes you from strategy to implementation — modernising your technology stack, automating your core processes and building a digital foundation aligned with Saudi Vision 2030.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> Digital strategy and technology roadmap</li>
          <li><i class="fas fa-check-circle"></i> Legacy system modernisation and migration</li>
          <li><i class="fas fa-check-circle"></i> AI-driven process automation implementation</li>
          <li><i class="fas fa-check-circle"></i> Cloud adoption and infrastructure transformation</li>
          <li><i class="fas fa-check-circle"></i> Change management and staff enablement</li>
          <li><i class="fas fa-check-circle"></i> Vision 2030 compliance alignment</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Start Your Transformation <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/hero-bg.png" alt="Enterprise Digital Transformation" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">6<span>+</span></div>
        <p>Years delivering digital transformation</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">85<span>+</span></div>
        <p>Transformation projects delivered</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">2030</div>
        <p>Vision-aligned framework</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">Full</div>
        <p>End-to-end delivery responsibility</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">What We Deliver</span>
      <h2 class="section-title">Transformation Capabilities</h2>
      <p class="section-desc">A full transformation partner — from the first strategy session to go-live and beyond.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-map"></i></div>
        <h4>Digital Strategy &amp; Roadmap</h4>
        <p>We assess your current state and define a clear, prioritised digital roadmap — with measurable milestones and ROI targets from day one.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-cloud-arrow-up"></i></div>
        <h4>Cloud &amp; Infrastructure</h4>
        <p>Seamless migration to cloud platforms (Azure, AWS, GCP) with full security, governance and compliance built into the architecture from the start.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-brain"></i></div>
        <h4>AI &amp; Automation Integration</h4>
        <p>Embedding intelligent automation, machine learning and AI decisioning into your core processes — increasing efficiency and enabling data-driven decisions.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-people-arrows"></i></div>
        <h4>Change Management</h4>
        <p>Structured change programmes ensure your people adopt new systems effectively — with communications, training, champions networks and ongoing support.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-shield-halved"></i></div>
        <h4>Cybersecurity &amp; Compliance</h4>
        <p>Security-by-design across every transformation workstream — aligned with NCA, SAMA and international standards to protect your digital investment.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-rotate"></i></div>
        <h4>Continuous Optimisation</h4>
        <p>Post-implementation support, monitoring and quarterly optimisation reviews ensure your digital systems evolve alongside your business needs.</p>
      </div>

    </div>
  </div>
</section>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Ready to Lead Digitally?</h2>
      <p>Our transformation experts are ready to design your roadmap. Let's start the conversation.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Book a Strategy Session</a>
        <a href="index.php#services" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
