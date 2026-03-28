<?php
require_once 'includes/admin-db.php';
$_db_item = dgtec_item_get_by_page_url('service', basename(__FILE__));
$page_title = 'Scalable Outsourcing Solutions – DGTEC';
$page_desc  = 'Reduce operational costs by up to 55% with DGTEC\'s scalable IT and business process outsourcing solutions for Saudi enterprises. Flexible, compliant, managed.';
include 'includes/header.php';
if (!empty($_db_item['page_content'])): ?>
<div class="container" style="padding-top:60px;padding-bottom:60px">
  <?= $_db_item['page_content'] ?>
</div>
<?php include 'includes/footer.php'; exit; endif; ?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Scalable Outsourcing Solutions</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Services</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Scalable Outsourcing Solutions</span>
    </nav>
  </div>
</section>

<!-- ======= OVERVIEW ======= -->
<section class="inner-overview">
  <div class="container">
    <div class="inner-overview-grid">
      <div class="inner-overview-text">
        <span class="section-label">Managed Outsourcing</span>
        <h2 class="section-title">Cut Costs. Keep<br>Control. Scale Fast.</h2>
        <p class="section-desc">
          DGTEC's Scalable Outsourcing Solutions give you access to skilled, managed resources at a fraction of the cost of traditional employment — with no hiring risk, no overhead and full compliance with Saudi labour regulations.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> Up to 55% reduction in operational costs</li>
          <li><i class="fas fa-check-circle"></i> Fully managed teams with dedicated account managers</li>
          <li><i class="fas fa-check-circle"></i> Flexible engagement models (monthly, project-based)</li>
          <li><i class="fas fa-check-circle"></i> Saudi labour law and GOSI compliance</li>
          <li><i class="fas fa-check-circle"></i> Rapid scale-up and scale-down capability</li>
          <li><i class="fas fa-check-circle"></i> Regular KPI reporting and performance reviews</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Get a Cost Estimate <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/our-soul.webp" alt="Scalable Outsourcing Solutions" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">55<span>%</span></div>
        <p>Average cost reduction achieved</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">48<span>h</span></div>
        <p>Typical resource deployment time</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">100<span>%</span></div>
        <p>Saudi labour law compliance</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">Zero</div>
        <p>Hidden costs or lock-in contracts</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">What We Offer</span>
      <h2 class="section-title">Outsourcing Capabilities</h2>
      <p class="section-desc">Everything you need to outsource operations effectively — with full visibility and control.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-coins"></i></div>
        <h4>Significant Cost Reduction</h4>
        <p>Eliminate recruitment fees, benefits overhead, equipment costs and training investment — achieving up to 55% savings versus direct employment.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-people-group"></i></div>
        <h4>Fully Managed Teams</h4>
        <p>DGTEC handles all HR, payroll, benefits, visas and performance management — delivering a seamless, managed resource you direct without the overhead.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-sliders"></i></div>
        <h4>Flexible Contracts</h4>
        <p>Monthly rolling, fixed-term or project-based engagements — scale your outsourced headcount up or down as business needs change, with minimal notice.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-gavel"></i></div>
        <h4>Full Legal Compliance</h4>
        <p>Complete adherence to Saudi Labour Law, GOSI, Saudisation (Nitaqat) and Wage Protection System — protecting your business at every level.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-star-half-stroke"></i></div>
        <h4>Quality Assurance</h4>
        <p>Ongoing performance management, skills development and quality reviews ensure outsourced teams meet and exceed agreed service levels.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-chart-line"></i></div>
        <h4>KPI Reporting</h4>
        <p>Transparent monthly performance reports covering productivity, quality, attendance and cost — giving you complete visibility over your outsourced operations.</p>
      </div>

    </div>
  </div>
</section>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>See How Much You Could Save</h2>
      <p>Book a free consultation and we'll produce a custom cost comparison for your specific requirements.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Get a Free Cost Analysis</a>
        <a href="index.php#services" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
