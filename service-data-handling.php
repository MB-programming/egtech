<?php
require_once 'includes/admin-db.php';
require_once 'includes/item-page-renderer.php';
$_db_item = dgtec_item_get_by_slug('service', basename(__FILE__, '.php'));
$_pc = null;
if (!empty($_db_item['page_content'])) {
    $_pcDec = json_decode($_db_item['page_content'], true);
    if (is_array($_pcDec)) $_pc = $_pcDec;
}
$page_title = 'Data Handling Solutions – DGTEC';
$page_desc  = 'DGTEC\'s Data Handling Solutions cover the full data lifecycle — collection, cleansing, governance, analytics and AI-ready pipelines for Saudi enterprises.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Data Handling Solutions</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Services</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Data Handling Solutions</span>
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
        <span class="section-label">Data Management</span>
        <h2 class="section-title">Your Data.<br>Trusted, Structured,<br><span style="color:var(--accent)">AI-Ready.</span></h2>
        <p class="section-desc">
          Data is your most valuable asset — but only if it's clean, governed and accessible. DGTEC's Data Handling Solutions cover the complete data lifecycle, from collection and enrichment to governance frameworks and AI-ready pipeline architecture that unlocks real business intelligence.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> End-to-end data collection and ingestion</li>
          <li><i class="fas fa-check-circle"></i> Data cleansing, deduplication and enrichment</li>
          <li><i class="fas fa-check-circle"></i> Enterprise data governance frameworks</li>
          <li><i class="fas fa-check-circle"></i> Analytics dashboards and business intelligence</li>
          <li><i class="fas fa-check-circle"></i> AI and ML-ready data pipeline architecture</li>
          <li><i class="fas fa-check-circle"></i> Security, privacy and PDPL compliance</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Assess Your Data <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/process-road.webp" alt="Data Handling Solutions" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">99<span>%</span></div>
        <p>Data quality score post-cleansing</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">3x</div>
        <p>Faster AI model training with clean data</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">PDPL</div>
        <p>Saudi data protection compliance</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">360°</div>
        <p>Full data lifecycle management</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">Our Data Capabilities</span>
      <h2 class="section-title">End-to-End Data Services</h2>
      <p class="section-desc">Every capability you need to transform raw data into a strategic asset that drives decisions and powers AI.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-database"></i></div>
        <h4>Data Collection &amp; Ingestion</h4>
        <p>Automated pipelines that collect structured and unstructured data from any source — ERP systems, APIs, IoT devices, web and legacy databases.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-broom"></i></div>
        <h4>Data Cleansing &amp; Enrichment</h4>
        <p>Intelligent deduplication, standardisation, gap-filling and third-party enrichment — ensuring your data is accurate, consistent and trustworthy.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-landmark"></i></div>
        <h4>Data Governance Framework</h4>
        <p>Policies, ownership structures, data dictionaries and quality controls that ensure every dataset meets enterprise standards for reliability and compliance.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-chart-pie"></i></div>
        <h4>Analytics &amp; BI Dashboards</h4>
        <p>Custom business intelligence dashboards built on Power BI, Tableau or native tools — turning raw data into actionable insights for decision-makers.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-microchip"></i></div>
        <h4>AI-Ready Data Pipelines</h4>
        <p>Architected specifically for machine learning — with feature stores, labelling workflows and model-ready data structures that accelerate AI model development.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-lock"></i></div>
        <h4>Security &amp; PDPL Compliance</h4>
        <p>End-to-end data security with encryption, access control, audit logs and full alignment with Saudi Arabia's Personal Data Protection Law (PDPL).</p>
      </div>

    </div>
  </div>
</section>

<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Make Your Data Work For You</h2>
      <p>Book a free data assessment and discover the hidden value locked inside your organisation's data.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Free Data Assessment</a>
        <a href="index.php#services" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
