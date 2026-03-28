<?php
require_once 'includes/admin-db.php';
require_once 'includes/item-page-renderer.php';
$_db_item = dgtec_item_get_by_slug('service', basename(__FILE__, '.php'));
$_pc = null;
if (!empty($_db_item['page_content'])) {
    $_pcDec = json_decode($_db_item['page_content'], true);
    if (is_array($_pcDec)) $_pc = $_pcDec;
}
$page_title = 'Tech Squad-as-a-Service – DGTEC';
$page_desc  = 'Deploy a dedicated, fully managed technical squad on demand. DGTEC\'s Tech Squad-as-a-Service gives you agile engineering capacity without the overhead of permanent hiring.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Tech Squad-as-a-Service</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Our Services</span>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Tech Squad-as-a-Service</span>
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
        <span class="section-label">On-Demand Engineering</span>
        <h2 class="section-title">Your Dedicated Team.<br>Your Pace. Your Goals.</h2>
        <p class="section-desc">
          DGTEC's Tech Squad-as-a-Service gives you a fully assembled, managed engineering team — deployed in days, not months. No lengthy recruitment cycles, no permanent headcount risk, no overhead. Just a high-performing squad focused entirely on your product and priorities.
        </p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> Cross-functional squads assembled in 48–72 hours</li>
          <li><i class="fas fa-check-circle"></i> Full-stack, backend, frontend, QA, DevOps roles</li>
          <li><i class="fas fa-check-circle"></i> Dedicated Project Manager included</li>
          <li><i class="fas fa-check-circle"></i> Agile / Scrum delivery methodology</li>
          <li><i class="fas fa-check-circle"></i> Weekly sprint reports and sprint demos</li>
          <li><i class="fas fa-check-circle"></i> Flexible squad size — scale up or down monthly</li>
        </ul>
        <a href="contact.php" class="btn btn-primary">Build Your Squad <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="inner-overview-image">
        <img src="assets/images/team.png" alt="Tech Squad-as-a-Service" loading="lazy" />
      </div>
    </div>
  </div>
</section>

<!-- ======= HIGHLIGHTS ======= -->
<section class="inner-highlights">
  <div class="container">
    <div class="inner-highlights-grid">
      <div class="inner-highlight-item">
        <div class="num">48<span>h</span></div>
        <p>Squad deployment time</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">3<span>–</span>20</div>
        <p>Engineers per squad (scalable)</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">Agile</div>
        <p>2-week sprint delivery cycles</p>
      </div>
      <div class="inner-highlight-item">
        <div class="num">Zero</div>
        <p>Minimum term commitment</p>
      </div>
    </div>
  </div>
</section>

<!-- ======= KEY FEATURES ======= -->
<section class="inner-features">
  <div class="container">
    <div class="inner-features-header">
      <span class="section-label">Squad Capabilities</span>
      <h2 class="section-title">What Your Squad Delivers</h2>
      <p class="section-desc">Everything you need for high-velocity technical delivery — in one managed engagement.</p>
    </div>
    <div class="inner-features-grid">

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-rocket"></i></div>
        <h4>Rapid Deployment</h4>
        <p>A fully assembled, briefed and tooled squad ready to begin sprinting within 48–72 hours of project kickoff — no ramp-up delay.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-code"></i></div>
        <h4>Full-Stack Engineering</h4>
        <p>Frontend (React, Vue, Angular), backend (Node, Python, PHP, Java), mobile (iOS/Android), DevOps and QA — we build the squad around your technology stack.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-user-tie"></i></div>
        <h4>Dedicated Project Manager</h4>
        <p>Every squad includes an experienced PM who manages sprints, coordinates communication, removes blockers and keeps delivery on track.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-arrows-up-down"></i></div>
        <h4>Elastic Scaling</h4>
        <p>Add engineers mid-project, reduce headcount between releases or pause the squad entirely — with just 30 days notice and no penalty.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-file-code"></i></div>
        <h4>Agile Methodology</h4>
        <p>Two-week sprints with planning, standups, retrospectives and demo sessions — keeping stakeholders aligned and delivery predictable at every stage.</p>
      </div>

      <div class="inner-feature-card">
        <div class="inner-feature-icon"><i class="fas fa-clipboard-list"></i></div>
        <h4>Transparent Reporting</h4>
        <p>Weekly velocity reports, sprint burndowns and monthly executive summaries ensure complete visibility into progress, costs and risks at all times.</p>
      </div>

    </div>
  </div>
</section>

<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Start Shipping Faster — From Day One</h2>
      <p>Tell us your technical needs and we'll propose the ideal squad composition within 24 hours.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Build Your Squad Now</a>
        <a href="index.php#services" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
