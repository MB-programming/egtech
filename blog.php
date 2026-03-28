<?php
require_once 'includes/admin-db.php';
$_si       = dgtec_site_info();
$page_title = 'Blog – Insights & News | DGTEC';
$page_desc  = 'Explore the latest insights, news and thought leadership from DGTEC on AI, digital transformation, technical recruitment and enterprise technology in Saudi Arabia.';
$blog_posts = dgtec_blogs_active();
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Blog &amp; Insights</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Blog</span>
    </nav>
  </div>
</section>

<!-- ======= BLOG ARCHIVE ======= -->
<section class="blog-section">
  <div class="container">
    <div class="blog-section-header">
      <span class="section-label">Latest Articles</span>
      <h2 class="section-title">Insights &amp; Thought Leadership</h2>
    </div>

    <?php if (!empty($blog_posts)): ?>
    <div class="blog-grid">
      <?php foreach ($blog_posts as $post): ?>
      <a href="blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column">
        <div class="blog-card-image">
          <img src="<?= htmlspecialchars($post['image'] ?: 'assets/images/hero-slider.webp') ?>"
               alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" />
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <?php if ($post['category']): ?>
            <span class="blog-card-tag"><?= htmlspecialchars($post['category']) ?></span>
            <?php endif; ?>
            <?php if ($post['published_at']): ?>
            <span class="blog-card-date">
              <i class="fas fa-calendar-alt"></i>
              <?= date('F j, Y', strtotime($post['published_at'])) ?>
            </span>
            <?php endif; ?>
          </div>
          <h3><?= htmlspecialchars($post['title']) ?></h3>
          <p><?= htmlspecialchars($post['excerpt']) ?></p>
          <span class="blog-read-more">Read Article <i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:80px 20px;color:#64748b">
      <i class="fas fa-newspaper" style="font-size:48px;opacity:.25;display:block;margin-bottom:16px"></i>
      <p>No articles published yet. Check back soon.</p>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ======= CTA ======= -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Ready to Transform Your Business?</h2>
      <p>Talk to our experts and discover how DGTEC can power your next chapter.</p>
      <div class="cta-btns">
        <a href="contact.php" class="btn btn-white">Free Consultation</a>
        <a href="index.php#services" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);">Our Services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
