<?php
/**
 * DGTEC — Generic Service detail page
 * URL: service-detail.php?slug=<slug>
 */
require_once 'includes/admin-db.php';
require_once 'includes/item-page-renderer.php';

$slug     = trim($_GET['slug'] ?? '');
$_db_item = $slug ? dgtec_item_get_by_slug('service', $slug) : null;

if (!$_db_item) {
    http_response_code(404);
    include '404.php';
    exit;
}

$_pc = null;
if (!empty($_db_item['page_content'])) {
    $_pcDec = json_decode($_db_item['page_content'], true);
    if (is_array($_pcDec)) $_pc = $_pcDec;
}

$page_title = htmlspecialchars($_db_item['title']) . ' – DGTEC';
$page_desc  = htmlspecialchars(mb_substr($_db_item['description'], 0, 160));
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1><?= htmlspecialchars($_db_item['title']) ?></h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <a href="services.php">Our Services</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span><?= htmlspecialchars(mb_substr($_db_item['title'], 0, 60)) ?></span>
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
        <?php if ($_db_item['icon']): ?>
        <div style="font-size:48px;color:var(--p);margin-bottom:20px"><i class="<?= htmlspecialchars($_db_item['icon']) ?>"></i></div>
        <?php endif; ?>
        <h2 class="section-title"><?= htmlspecialchars($_db_item['title']) ?></h2>
        <p class="section-desc"><?= nl2br(htmlspecialchars($_db_item['description'])) ?></p>
        <?php
        $features = array_filter(array_map('trim', explode('|', $_db_item['features'] ?? '')));
        if (!empty($features)):
        ?>
        <ul class="feature-list">
          <?php foreach ($features as $f): ?>
          <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($f) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="contact.php" class="btn btn-primary">Get In Touch <i class="fas fa-arrow-right"></i></a>
      </div>
      <?php if ($_db_item['image']): ?>
      <div class="inner-overview-image">
        <img src="<?= htmlspecialchars($_db_item['image']) ?>" alt="<?= htmlspecialchars($_db_item['title']) ?>" loading="lazy" />
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php endif; ?>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Ready to Get Started?</h2>
      <p>Contact us today to learn how <?= htmlspecialchars($_db_item['title']) ?> can help your organisation.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Contact Us</a>
        <a href="services.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">All Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
