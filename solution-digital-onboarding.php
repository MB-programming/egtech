<?php
require_once 'includes/admin-db.php';
$_db_item = dgtec_item_get_by_page_url('solution', basename(__FILE__));
$page_title = 'Digital Onboarding & Compliance Solutions – DGTEC';
$page_desc  = 'Streamline client and employee onboarding with DGTEC\'s smart digital workflows and full compliance management solutions for Saudi enterprises.';
include 'includes/header.php';
if (!empty($_db_item['page_content'])): ?>
<div class="container" style="padding-top:60px;padding-bottom:60px">
  <?= $_db_item['page_content'] ?>
</div>
<?php include 'includes/footer.php'; exit; endif; ?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Digital Onboarding &amp; Compliance</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Solutions</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Digital Onboarding &amp; Compliance</span>
    </nav>
  </div>
</section>

<!-- ======= OVERVIEW ======= -->
<section class="inner-overview">
  <div class="container">
    <div class="inner-overview-grid">
      <div class="inner-overview-text">
        <span class="section-label">Smart Onboarding</span>
        <h2 class="section-title">Onboard Faster.<br>Comply Smarter.</h2>
        <p class="section-desc">
          Manual onboarding is slow, error-prone and costly. DGTEC's Digital Onboarding &amp; Compliance solution replaces paper-heavy processes with intelligent digital workflows — delivering a seamless experience for clients and employees while ensuring full regulatory compliance.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> Automated KYC and identity verification</li>
          <li><i class="fas fa-check-circle"></i> Digital document collection and e-signature</li>
          <li><i class="fas fa-check-circle"></i> Real-time compliance monitoring and alerts</li>
          <li><i class="fas fa-check-circle"></i> Multi-language support (Arabic &amp; English)</li>
          <li><i class="fas fa-check-circle"></i> Full audit trail for regulatory reporting</li>
          <li><i class="fas fa-check-circle"></i> Seamless integration with existing HR and ERP systems</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Request a Demo <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/our-soul.webp" alt="Digital Onboarding & Compliance" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">80<span>%</span></div>
        <p>Reduction in onboarding time</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">100<span>%</span></div>
        <p>Digital, paperless process</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">Zero</div>
        <p>Compliance gaps &amp; audit failures</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">24/7</div>
        <p>Real-time monitoring &amp; alerts</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">What's Included</span>
      <h2 class="section-title">Key Features</h2>
      <p class="section-desc">Everything you need to onboard clients and employees at scale — compliantly and efficiently.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-id-card"></i></div>
        <h4>KYC &amp; Identity Verification</h4>
        <p>Automated verification of national IDs, passports and Iqama — reducing fraud risk and manual checking time by over 90%.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-file-signature"></i></div>
        <h4>Digital Document Management</h4>
        <p>Collect, store and manage all onboarding documents digitally with e-signature support, version control and secure cloud storage.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-shield-halved"></i></div>
        <h4>Compliance Monitoring</h4>
        <p>Real-time monitoring against SAMA, ZATCA and other Saudi regulatory frameworks — with automated alerts before deadlines are missed.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-diagram-project"></i></div>
        <h4>Smart Workflow Builder</h4>
        <p>Build customised onboarding journeys with a no-code workflow designer — tailored to different employee types, departments or client categories.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-plug"></i></div>
        <h4>System Integration</h4>
        <p>Out-of-the-box connectors for SAP, Oracle, Workday and other HR/ERP systems ensure a unified data flow with no manual re-entry.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-chart-bar"></i></div>
        <h4>Audit Trail &amp; Reporting</h4>
        <p>Comprehensive, tamper-proof audit logs and compliance reports ready for internal and external regulatory review at any time.</p>
      </div>

    </div>
  </div>
</section>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Ready to Modernise Your Onboarding?</h2>
      <p>Get a personalised demo and see how DGTEC transforms onboarding in just weeks.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Request a Demo</a>
        <a href="index.php#solutions" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Solutions</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
