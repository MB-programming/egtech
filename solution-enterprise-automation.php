<?php
require_once 'includes/admin-db.php';
require_once 'includes/item-page-renderer.php';
$_db_item = dgtec_item_get_by_page_url('solution', basename(__FILE__));
$_pc = null;
if (!empty($_db_item['page_content'])) {
    $_pcDec = json_decode($_db_item['page_content'], true);
    if (is_array($_pcDec)) $_pc = $_pcDec;
}
$page_title = 'Enterprise Content & Process Automation – DGTEC';
$page_desc  = 'Automate complex business processes and manage enterprise content with DGTEC\'s intelligent automation solutions powered by Newgen for Saudi organisations.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Enterprise Content &amp; Process Automation</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Solutions</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Enterprise Content &amp; Process Automation</span>
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
        <span class="section-label">Intelligent Automation</span>
        <h2 class="section-title">Eliminate Manual Work.<br>Accelerate Every Process.</h2>
        <p class="section-desc">
          Powered by Newgen — a global leader in enterprise content management and BPM — DGTEC delivers end-to-end process automation that reduces operational costs, eliminates errors and gives your team back the time to focus on what matters most.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> End-to-end business process automation (BPA)</li>
          <li><i class="fas fa-check-circle"></i> Intelligent document capture and classification</li>
          <li><i class="fas fa-check-circle"></i> Low-code workflow design and deployment</li>
          <li><i class="fas fa-check-circle"></i> Enterprise content management (ECM)</li>
          <li><i class="fas fa-check-circle"></i> Advanced analytics and process intelligence</li>
          <li><i class="fas fa-check-circle"></i> Scalable cloud or on-premise deployment</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Request a Demo <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/process-road.webp" alt="Enterprise Process Automation" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">65<span>%</span></div>
        <p>Reduction in manual processing time</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">3x</div>
        <p>Faster process cycle times</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">99<span>%</span></div>
        <p>Data accuracy after automation</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">ROI</div>
        <p>Measurable within 6 months</p>
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
      <p class="section-desc">A complete enterprise automation stack to transform how your organisation works.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-robot"></i></div>
        <h4>Business Process Automation</h4>
        <p>Automate multi-step, cross-department workflows with intelligent routing, escalation rules and real-time tracking from end to end.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-file-magnifying-glass"></i></div>
        <h4>Intelligent Document Capture</h4>
        <p>AI-powered OCR and document classification automatically extracts, validates and routes data from any document type with high accuracy.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-server"></i></div>
        <h4>Enterprise Content Management</h4>
        <p>Centralise, organise and govern all enterprise content with version control, access permissions and lifecycle management built in.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
        <h4>Low-Code Workflow Designer</h4>
        <p>Business teams can design and deploy new automated workflows without writing a single line of code using the drag-and-drop builder.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-chart-pie"></i></div>
        <h4>Process Intelligence &amp; Analytics</h4>
        <p>Real-time dashboards and reports reveal bottlenecks, cycle times and performance trends — enabling continuous process improvement.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-link"></i></div>
        <h4>Enterprise Integration Hub</h4>
        <p>Pre-built connectors and open APIs link your automation layer to SAP, Microsoft 365, Salesforce, Oracle and any other core system.</p>
      </div>

    </div>
  </div>
</section>

<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Start Automating Your Business Today</h2>
      <p>Our team will map your current processes and show you exactly where automation delivers the fastest results.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Book a Process Review</a>
        <a href="index.php#solutions" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Solutions</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
