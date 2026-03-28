<?php
/**
 * Shared admin sidebar — include in every admin panel page.
 * Requires $unreadCount and $activePage to be set before including.
 * $activePage: 'slides' | 'services' | 'solutions' | 'partners' | 'reviews' | 'site-info' | 'blog' | 'submissions' | 'seo' | 'nav-menus'
 */
$_nav_active = $activePage ?? '';
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <img src="../assets/images/logo.webp" alt="DGTEC" />
    <span>Admin</span>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-section">Content</p>
    <a href="slides.php" <?= $_nav_active === 'slides' ? 'class="active"' : '' ?>><i class="fas fa-images"></i> Hero Slides</a>
    <a href="services.php" <?= $_nav_active === 'services' ? 'class="active"' : '' ?>><i class="fas fa-briefcase"></i> Services</a>
    <a href="solutions.php" <?= $_nav_active === 'solutions' ? 'class="active"' : '' ?>><i class="fas fa-lightbulb"></i> Solutions</a>
    <a href="partners.php" <?= $_nav_active === 'partners' ? 'class="active"' : '' ?>><i class="fas fa-handshake"></i> Partners</a>
    <a href="reviews.php" <?= $_nav_active === 'reviews' ? 'class="active"' : '' ?>><i class="fas fa-star"></i> Client Reviews</a>
    <a href="blog.php" <?= $_nav_active === 'blog' ? 'class="active"' : '' ?>><i class="fas fa-newspaper"></i> Blog Posts</a>
    <a href="pages.php" <?= $_nav_active === 'pages' ? 'class="active"' : '' ?>><i class="fas fa-file-lines"></i> Pages</a>
    <a href="social-media.php" <?= $_nav_active === 'social-media' ? 'class="active"' : '' ?>><i class="fas fa-share-nodes"></i> Social Media</a>
    <p class="nav-section">Inbox</p>
    <a href="submissions.php" <?= $_nav_active === 'submissions' ? 'class="active"' : '' ?>>
      <i class="fas fa-envelope"></i> Submissions
      <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
      <span style="margin-left:auto;background:#dc2626;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700"><?= $unreadCount ?></span>
      <?php endif; ?>
    </a>
    <p class="nav-section">Settings</p>
    <a href="site-info.php" <?= $_nav_active === 'site-info' ? 'class="active"' : '' ?>><i class="fas fa-gear"></i> Site Info</a>
    <a href="seo.php" <?= $_nav_active === 'seo' ? 'class="active"' : '' ?>><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</a>
    <a href="nav-menus.php" <?= $_nav_active === 'nav-menus' ? 'class="active"' : '' ?>><i class="fas fa-bars"></i> Nav Menus</a>
    <p class="nav-section">Site</p>
    <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
  </div>
</aside>
