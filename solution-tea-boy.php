<?php
require_once 'includes/admin-db.php';
require_once 'includes/item-page-renderer.php';
$_db_item = dgtec_item_get_by_page_url('solution', basename(__FILE__));
$_pc = null;
if (!empty($_db_item['page_content'])) {
    $_pcDec = json_decode($_db_item['page_content'], true);
    if (is_array($_pcDec)) $_pc = $_pcDec;
}
$page_title = 'Tea Boy – Smart Internal Operations Automation | DGTEC';
$page_desc  = 'Tea Boy by DGTEC is an AI-powered smart internal operations platform that automates day-to-day workplace requests, service management and internal workflows.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Tea Boy – Smart Internal Operations</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Solutions</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Tea Boy – Smart Internal Operations</span>
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
        <span class="section-label">Internal Operations AI</span>
        <h2 class="section-title">Your Smartest<br>Internal Team Member.</h2>
        <p class="section-desc">
          Tea Boy is DGTEC's proprietary AI-powered platform built specifically for internal operations. From facilities requests to IT support, travel bookings and administrative tasks — Tea Boy handles it all intelligently, so your team can focus on the work that actually moves the business forward.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> AI-powered request routing and assignment</li>
          <li><i class="fas fa-check-circle"></i> Smart scheduling and resource management</li>
          <li><i class="fas fa-check-circle"></i> Internal service desk with SLA tracking</li>
          <li><i class="fas fa-check-circle"></i> Asset tracking and lifecycle management</li>
          <li><i class="fas fa-check-circle"></i> Mobile-first employee experience app</li>
          <li><i class="fas fa-check-circle"></i> Analytics dashboard for operations leadership</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">See Tea Boy in Action <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/team.png" alt="Tea Boy Smart Internal Operations" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">70<span>%</span></div>
        <p>Faster internal request resolution</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">50<span>%</span></div>
        <p>Reduction in admin overhead</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">98<span>%</span></div>
        <p>Employee satisfaction score</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">1</div>
        <p>Unified platform for all internal ops</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">What Tea Boy Does</span>
      <h2 class="section-title">Key Features</h2>
      <p class="section-desc">A smart, unified platform for every internal service your organisation provides.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-ticket"></i></div>
        <h4>Smart Request Management</h4>
        <p>Employees submit any internal request through one simple interface — Tea Boy automatically routes, prioritises and assigns it to the right team with full tracking.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-calendar-check"></i></div>
        <h4>Intelligent Scheduling</h4>
        <p>Automated scheduling for meeting rooms, maintenance visits, equipment loans and staff resources — with conflict detection and smart suggestions.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-headset"></i></div>
        <h4>Internal Service Desk</h4>
        <p>A full-featured IT and facilities service desk with SLA management, escalation rules, knowledge base and multi-department support queues.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-boxes-stacked"></i></div>
        <h4>Asset Tracking</h4>
        <p>Track every physical and digital asset across locations and departments — with automated lifecycle alerts, maintenance reminders and disposal workflows.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-mobile-screen"></i></div>
        <h4>Mobile Employee App</h4>
        <p>A beautifully designed mobile app gives every employee instant access to request services, track progress and receive notifications — from anywhere.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-gauge-high"></i></div>
        <h4>Operations Dashboard</h4>
        <p>Real-time visibility into all internal operations — request volumes, resolution times, SLA compliance, team performance and cost analysis.</p>
      </div>

    </div>
  </div>
</section>

<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Let Tea Boy Run Your Internal Operations</h2>
      <p>See a live demo and discover how Tea Boy transforms workplace operations in days, not months.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Book a Demo</a>
        <a href="index.php#solutions" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Solutions</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
