<?php
/**
 * Shared admin sidebar — include in every admin panel page.
 * Requires $unreadCount and $activePage to be set before including.
 */
$_nav_active = $activePage ?? '';
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <img src="../assets/images/logo.webp" alt="DGTEC" />
    <span>Admin</span>
  </div>
  <nav class="sidebar-nav">

    <?php if (admin_can('slides') || admin_can('services') || admin_can('solutions') || admin_can('partners') || admin_can('blog') || admin_can('pages') || admin_can('careers') || admin_can('social_media')): ?>
    <p class="nav-section">Content</p>
    <?php if (admin_can('slides')): ?>
    <a href="slides.php" <?= $_nav_active === 'slides' ? 'class="active"' : '' ?>><i class="fas fa-images"></i> Hero Slides</a>
    <?php endif; ?>
    <?php if (admin_can('services')): ?>
    <a href="services.php" <?= $_nav_active === 'services' ? 'class="active"' : '' ?>><i class="fas fa-briefcase"></i> Services</a>
    <?php endif; ?>
    <?php if (admin_can('solutions')): ?>
    <a href="solutions.php" <?= $_nav_active === 'solutions' ? 'class="active"' : '' ?>><i class="fas fa-lightbulb"></i> Solutions</a>
    <?php endif; ?>
    <?php if (admin_can('partners')): ?>
    <a href="partners.php" <?= $_nav_active === 'partners' ? 'class="active"' : '' ?>><i class="fas fa-handshake"></i> Partners</a>
    <a href="reviews.php" <?= $_nav_active === 'reviews' ? 'class="active"' : '' ?>><i class="fas fa-star"></i> Client Reviews</a>
    <?php endif; ?>
    <?php if (admin_can('blog')): ?>
    <a href="blog.php" <?= $_nav_active === 'blog' ? 'class="active"' : '' ?>><i class="fas fa-newspaper"></i> Blog Posts</a>
    <?php endif; ?>
    <?php if (admin_can('pages')): ?>
    <a href="pages.php" <?= $_nav_active === 'pages' ? 'class="active"' : '' ?>><i class="fas fa-file-lines"></i> Pages</a>
    <?php endif; ?>
    <?php if (admin_can('careers')): ?>
    <a href="careers.php" <?= $_nav_active === 'careers' ? 'class="active"' : '' ?>><i class="fas fa-user-tie"></i> Careers</a>
    <?php endif; ?>
    <?php if (admin_can('social_media')): ?>
    <a href="social-media.php" <?= $_nav_active === 'social-media' ? 'class="active"' : '' ?>><i class="fas fa-share-nodes"></i> Social Media</a>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (admin_can('submissions') || admin_can('career_applications')): ?>
    <p class="nav-section">Inbox</p>
    <?php if (admin_can('submissions')): ?>
    <a href="submissions.php" <?= $_nav_active === 'submissions' ? 'class="active"' : '' ?>>
      <i class="fas fa-envelope"></i> Submissions
      <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
      <span style="margin-left:auto;background:#dc2626;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700"><?= $unreadCount ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>
    <?php if (admin_can('career_applications')): ?>
    <a href="career-applications.php" <?= $_nav_active === 'career-applications' ? 'class="active"' : '' ?>>
      <i class="fas fa-inbox"></i> Applications
      <?php $__appNew = dgtec_career_applications_count(); if ($__appNew > 0): ?>
      <span style="margin-left:auto;background:#dc2626;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700"><?= $__appNew ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (admin_can('site_info') || admin_can('home_content') || admin_can('about_content') || admin_can('seo') || admin_can('nav_menus')): ?>
    <p class="nav-section">Settings</p>
    <?php if (admin_can('site_info')): ?>
    <a href="site-info.php" <?= $_nav_active === 'site-info' ? 'class="active"' : '' ?>><i class="fas fa-gear"></i> Site Info</a>
    <?php endif; ?>
    <?php if (admin_can('home_content')): ?>
    <a href="home-content.php" <?= $_nav_active === 'home-content' ? 'class="active"' : '' ?>><i class="fas fa-house"></i> Home Content</a>
    <?php endif; ?>
    <?php if (admin_can('about_content')): ?>
    <a href="about-content.php" <?= $_nav_active === 'about-content' ? 'class="active"' : '' ?>><i class="fas fa-circle-info"></i> About Content</a>
    <?php endif; ?>
    <?php if (admin_can('seo')): ?>
    <a href="seo.php" <?= $_nav_active === 'seo' ? 'class="active"' : '' ?>><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</a>
    <?php endif; ?>
    <?php if (admin_can('nav_menus')): ?>
    <a href="nav-menus.php" <?= $_nav_active === 'nav-menus' ? 'class="active"' : '' ?>><i class="fas fa-bars"></i> Nav Menus</a>
    <?php endif; ?>
    <?php if (admin_can('site_info')): ?>
    <a href="smtp-settings.php" <?= $_nav_active === 'smtp' ? 'class="active"' : '' ?>><i class="fas fa-server"></i> SMTP / Email</a>
    <a href="form-notifications.php" <?= $_nav_active === 'form-notifications' ? 'class="active"' : '' ?>><i class="fas fa-bell"></i> Form Notifications</a>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (admin_can('users')): ?>
    <p class="nav-section">Team</p>
    <a href="users.php" <?= $_nav_active === 'users' ? 'class="active"' : '' ?>><i class="fas fa-users"></i> Users</a>
    <a href="roles.php" <?= $_nav_active === 'roles' ? 'class="active"' : '' ?>><i class="fas fa-shield-halved"></i> Roles</a>
    <?php endif; ?>

    <p class="nav-section">Site</p>
    <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a>
  </nav>
  <div class="sidebar-footer">
    <?php $__role = admin_current_role(); if ($__role): ?>
    <div style="font-size:11px;color:var(--gray);padding:0 16px 8px;opacity:.7"><?= htmlspecialchars($__role) ?></div>
    <?php endif; ?>
    <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
  </div>
</aside>
