<?php
require_once 'includes/admin-db.php';

$slug = trim($_GET['slug'] ?? '');
$post = $slug ? dgtec_blog_get_by_slug($slug) : null;

/* 404 if not found */
if (!$post) {
    header('Location: blog.php');
    exit;
}

/* Recent posts for sidebar (exclude current) */
$all_posts    = dgtec_blogs_active();
$recent_posts = array_filter($all_posts, fn($p) => $p['id'] !== $post['id']);
$recent_posts = array_slice(array_values($recent_posts), 0, 4);

/* Collect unique categories for topics widget */
$categories = array_unique(array_filter(array_column($all_posts, 'category')));

$_si       = dgtec_site_info();
$page_title = htmlspecialchars($post['title']) . ' – DGTEC Blog';
$page_desc  = htmlspecialchars(mb_substr($post['excerpt'], 0, 160));
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Blog Post</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <a href="blog.php">Blog</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span><?= htmlspecialchars(mb_substr($post['title'], 0, 60)) ?></span>
    </nav>
  </div>
</section>

<!-- ======= POST CONTENT ======= -->
<section class="post-section">
  <div class="container">
    <div class="post-grid">

      <!-- Main Post -->
      <article class="post-content">

        <?php if ($post['image']): ?>
        <div class="post-featured-image">
          <img src="<?= htmlspecialchars($post['image']) ?>"
               alt="<?= htmlspecialchars($post['title']) ?>" />
        </div>
        <?php endif; ?>

        <div class="post-meta">
          <?php if ($post['category']): ?>
          <span class="post-tag"><?= htmlspecialchars($post['category']) ?></span>
          <?php endif; ?>
          <?php if ($post['published_at']): ?>
          <span class="post-date">
            <i class="fas fa-calendar-alt"></i>
            <?= date('F j, Y', strtotime($post['published_at'])) ?>
          </span>
          <?php endif; ?>
          <span class="post-author"><i class="fas fa-user"></i> DGTEC Editorial Team</span>
        </div>

        <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>

        <div class="post-body">
          <?= $post['content'] /* stored as HTML — rendered as-is */ ?>
        </div>

        <?php if ($post['category']): ?>
        <div class="post-tags">
          <span>Tags:</span>
          <?php foreach (explode('&', $post['category']) as $tag): ?>
          <a href="blog.php" class="post-tag-badge"><?= htmlspecialchars(trim($tag)) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </article>

      <!-- Sidebar -->
      <aside class="post-sidebar">

        <?php if (!empty($recent_posts)): ?>
        <div class="sidebar-widget">
          <h4>Recent Articles</h4>
          <?php foreach ($recent_posts as $rp): ?>
          <div class="sidebar-post">
            <div class="sidebar-post-img">
              <img src="<?= htmlspecialchars($rp['image'] ?: 'assets/images/hero-slider.webp') ?>"
                   alt="<?= htmlspecialchars($rp['title']) ?>" loading="lazy" />
            </div>
            <div class="sidebar-post-info">
              <a href="blog-post.php?slug=<?= urlencode($rp['slug']) ?>">
                <h5><?= htmlspecialchars($rp['title']) ?></h5>
              </a>
              <?php if ($rp['published_at']): ?>
              <span><?= date('F j, Y', strtotime($rp['published_at'])) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($categories)): ?>
        <div class="sidebar-widget">
          <h4>Topics</h4>
          <div class="sidebar-tags">
            <?php foreach ($categories as $cat): ?>
            <span class="sidebar-tag"><?= htmlspecialchars($cat) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="sidebar-widget" style="background:var(--primary);border-radius:var(--radius);padding:32px 24px;text-align:center;">
          <i class="fas fa-comments" style="font-size:32px;color:var(--accent);margin-bottom:16px;display:block;"></i>
          <h4 style="color:#fff;border-bottom-color:var(--accent);padding-bottom:0;margin-bottom:12px;">Need Expert Advice?</h4>
          <p style="font-size:14px;color:rgba(255,255,255,.75);margin-bottom:20px;line-height:1.6;">
            Talk to our team and get a free consultation tailored to your business.
          </p>
          <a href="contact.php" class="btn btn-white" style="width:100%;justify-content:center;">Book Consultation</a>
        </div>

      </aside>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
