<?php
/* Load site_info if not already loaded */
if (!function_exists('dgtec_site_info')) {
    require_once __DIR__ . '/admin-db.php';
}
$_fsite = dgtec_site_info();
$_footer_logo = $_fsite['footer_logo'] ?: 'assets/images/logo.webp';

/* Per-page SEO data (already loaded in header.php via $_seo; reuse or reload) */
if (!isset($_seo)) {
    $_seo_key_f = $seo_page_key ?? basename($_SERVER['PHP_SELF'], '.php');
    $_seo       = dgtec_seo_get($_seo_key_f);
}
?>
<!-- ======= FOOTER ======= -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <img src="<?= htmlspecialchars($_footer_logo) ?>" alt="DGTEC Logo" width="140" height="44" />
        <p><?= htmlspecialchars($_fsite['footer_description'] ?: 'We believe technology has the power to do amazing things. DGTEC delivers advanced integrated solutions that transform businesses across the Kingdom.') ?></p>
        <div class="footer-social">
          <?php foreach (dgtec_social_links_active() as $_sl): ?>
          <a href="<?= htmlspecialchars($_sl['url']) ?>" class="social-link" aria-label="<?= htmlspecialchars($_sl['platform']) ?>" target="_blank" rel="noopener noreferrer"><i class="<?= htmlspecialchars($_sl['icon']) ?>"></i></a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Our Solutions -->
      <div class="footer-col">
        <h4>Our Solutions</h4>
        <ul class="footer-links">
          <?php foreach (dgtec_items_active('solution') as $_fi): ?>
          <li><a href="solution-detail.php?slug=<?= urlencode($_fi['slug']) ?>"><?= htmlspecialchars($_fi['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Our Services -->
      <div class="footer-col">
        <h4>Our Services</h4>
        <ul class="footer-links">
          <?php foreach (dgtec_items_active('service') as $_fi): ?>
          <li><a href="service-detail.php?slug=<?= urlencode($_fi['slug']) ?>"><?= htmlspecialchars($_fi['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Contact -->
      <div class="footer-col">
        <h4>Contact Us</h4>
        <?php if ($_fsite['phone']): ?>
        <div class="footer-contact-item">
          <i class="fas fa-phone"></i>
          <span><a href="tel:<?= preg_replace('/[^+0-9]/', '', $_fsite['phone']) ?>"><?= htmlspecialchars($_fsite['phone']) ?></a></span>
        </div>
        <?php endif; ?>
        <?php if ($_fsite['email']): ?>
        <div class="footer-contact-item">
          <i class="fas fa-envelope"></i>
          <span><a href="mailto:<?= htmlspecialchars($_fsite['email']) ?>"><?= htmlspecialchars($_fsite['email']) ?></a></span>
        </div>
        <?php endif; ?>
        <?php if ($_fsite['address']): ?>
        <div class="footer-contact-item">
          <i class="fas fa-location-dot"></i>
          <span><?= htmlspecialchars($_fsite['address']) ?></span>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Footer Bottom -->
  <div class="footer-bottom">
    <div class="container" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; width:100%;">
      <p>&copy; <?= date('Y') ?> by DGTEC. All rights reserved.</p>
      <div class="footer-bottom-links">
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="blog.php">Blogs</a>
      </div>
    </div>
  </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<!-- GSAP + ScrollTrigger -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" crossorigin="anonymous"></script>
<!-- Main JS -->
<script src="js/main.js"></script>

<!-- Google Analytics (GA4) -->
<?php if (!empty($_fsite['google_analytics'])): ?>
<?php $ga_id = htmlspecialchars($_fsite['google_analytics']); ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga_id ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= $ga_id ?>');
</script>
<?php endif; ?>

<!-- Global body injection (all pages) -->
<?php if (!empty($_fsite['global_body_code'])): ?>
<?= $_fsite['global_body_code'] . "\n" ?>
<?php endif; ?>

<!-- Per-page body injection -->
<?php if (!empty($_seo['body_code'])): ?>
<?= $_seo['body_code'] . "\n" ?>
<?php endif; ?>

</body>
</html>
