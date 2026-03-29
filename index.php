<?php
require_once 'includes/admin-db.php';
$_si          = dgtec_site_info();
$_hc          = dgtec_home_content();
$_hp          = dgtec_home_process();
$_ha          = dgtec_home_achievements();
$page_title   = 'DGTEC – Technological Transformation in The Kingdom';
$page_desc    = $_si['site_description'] ?: 'DGTEC delivers advanced Technical Recruitment, Scalable Outsourcing, AI automation and Digital Transformation solutions in Saudi Arabia.';
$hero_slides  = dgtec_slides_active();
$partners     = dgtec_partners_active();
$reviews      = dgtec_reviews_active();
$_home_svcs   = array_slice(dgtec_items_active('service'),  0, (int)($_hc['services_count']  ?? 5));
$_home_sols   = array_slice(dgtec_items_active('solution'), 0, (int)($_hc['solutions_count'] ?? 3));
include 'includes/header.php';
?>

<!-- ======= HERO SLIDER (dynamic from DB) ======= -->
<section class="hero-section" id="home">
  <div class="hero-slider-wrapper">
<?php foreach ($hero_slides as $i => $sl): ?>
    <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>">
      <?php if ($sl['bg_image']): ?>
      <div class="hero-slide-bg" style="background-image:url('<?= htmlspecialchars($sl['bg_image']) ?>')"></div>
      <?php endif; ?>
      <div class="hero-slide-overlay" style="background:linear-gradient(105deg,<?= hex_rgba($sl['gradient_color1'], $sl['gradient_opacity1']) ?> 0%,<?= hex_rgba($sl['gradient_color2'], $sl['gradient_opacity2']) ?> 100%)"></div>
      <div class="container hero-content">
        <div class="hero-text">
          <span class="hero-label"><?= htmlspecialchars($sl['label']) ?></span>
          <h1 class="hero-title">
            <?php foreach (explode("\n", $sl['title']) as $line): ?><?= htmlspecialchars($line) ?><br><?php endforeach; ?>
            <?php if ($sl['highlight_text']): ?>
            <span<?= $sl['highlight_color'] ? ' style="color:' . htmlspecialchars($sl['highlight_color']) . '"' : '' ?>><?= htmlspecialchars($sl['highlight_text']) ?></span>
            <?php endif; ?>
          </h1>
          <p class="hero-desc"><?= htmlspecialchars($sl['description']) ?></p>
          <div class="hero-btns">
            <?php if ($sl['btn1_text']): ?>
            <a href="<?= htmlspecialchars($sl['btn1_url']) ?>" class="btn btn-white"><?= htmlspecialchars($sl['btn1_text']) ?></a>
            <?php endif; ?>
            <?php if ($sl['btn2_text']): ?>
            <a href="<?= htmlspecialchars($sl['btn2_url']) ?>" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.5)"><?= htmlspecialchars($sl['btn2_text']) ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
<?php endforeach; ?>
  </div><!-- /.hero-slider-wrapper -->

  <!-- Slider Controls -->
  <div class="hero-slider-nav">
    <button class="hero-nav-btn hero-prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
    <div class="hero-dots">
      <?php foreach ($hero_slides as $i => $sl): ?>
      <span class="hero-dot <?= $i === 0 ? 'active' : '' ?>"></span>
      <?php endforeach; ?>
    </div>
    <button class="hero-nav-btn hero-next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
  </div>
</section>


<!-- ======= SERVICES SECTION ======= -->
<section class="services-section" id="services">
  <div class="container">
    <div class="services-header">
      <div>
        <span class="section-label"><?= htmlspecialchars($_hc['services_label']) ?></span>
        <h2 class="section-title"><?= htmlspecialchars($_hc['services_title']) ?></h2>
      </div>
      <p class="section-desc"><?= htmlspecialchars($_hc['services_desc']) ?></p>
    </div>
    <div class="services-grid">
      <?php foreach ($_home_svcs as $_svc): ?>
      <div class="service-card">
        <div class="service-icon">
          <i class="<?= htmlspecialchars($_svc['icon'] ?: 'fas fa-briefcase') ?>"></i>
        </div>
        <h3><?= htmlspecialchars($_svc['title']) ?></h3>
        <p><?= htmlspecialchars($_svc['description']) ?></p>
        <a href="service-detail.php?slug=<?= urlencode($_svc['slug']) ?>" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ======= SOLUTIONS SECTION ======= -->
<section class="solutions-section" id="solutions">
  <div class="container">
    <div class="solutions-inner">
      <div class="solutions-content">
        <span class="section-label"><?= htmlspecialchars($_hc['solutions_label']) ?></span>
        <h2 class="section-title"><?= htmlspecialchars($_hc['solutions_title']) ?></h2>
        <p class="section-desc"><?= htmlspecialchars($_hc['solutions_desc']) ?></p>
        <div class="solutions-cards">
          <?php foreach ($_home_sols as $_i => $_sol): ?>
          <a href="solution-detail.php?slug=<?= urlencode($_sol['slug']) ?>" class="solution-item <?= $_i === 0 ? 'active' : '' ?>" style="text-decoration:none;color:inherit">
            <div class="solution-item-icon">
              <i class="<?= htmlspecialchars($_sol['icon'] ?: 'fas fa-lightbulb') ?>"></i>
            </div>
            <div class="solution-item-text">
              <h4><?= htmlspecialchars($_sol['title']) ?></h4>
              <p><?= htmlspecialchars(mb_substr($_sol['description'], 0, 120)) ?><?= mb_strlen($_sol['description']) > 120 ? '…' : '' ?></p>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="solutions-image">
        <img src="assets/images/our-soul.webp" alt="Our Solutions" loading="lazy" />
      </div>
    </div>
  </div>
</section>


<!-- ======= BUSINESS PROCESS ROAD ======= -->
<section class="process-section" id="process">
  <div class="container">
    <div class="process-header">
      <span class="section-label"><?= htmlspecialchars($_hc['process_label']) ?></span>
      <h2 class="section-title"><?= htmlspecialchars($_hc['process_title']) ?></h2>
      <p class="section-desc"><?= htmlspecialchars($_hc['process_desc']) ?></p>
    </div>
    <div class="process-grid">
      <?php foreach ($_hp as $_pi => $_step): ?>
      <div class="process-step">
        <div class="step-number"><?= str_pad($_pi + 1, 2, '0', STR_PAD_LEFT) ?></div>
        <div class="step-icon"><i class="<?= htmlspecialchars($_step['icon'] ?: 'fas fa-circle') ?>"></i></div>
        <h4><?= htmlspecialchars($_step['title']) ?></h4>
        <p><?= htmlspecialchars($_step['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="process-footer">
      <div class="process-footer-inner">
        <i class="fas fa-circle-check"></i>
        <p>Every step is backed by <strong>dedicated support</strong>, transparent reporting and a team fully committed to your success.</p>
        <a href="contact.php" class="btn btn-primary">Start Your Journey</a>
      </div>
    </div>
  </div>
</section>


<!-- ======= ACHIEVEMENTS ======= -->
<section class="achievements-section" id="achievements">
  <div class="container">
    <div class="achievements-header">
      <span class="section-label" style="color:var(--accent)"><?= htmlspecialchars($_hc['achievements_label']) ?></span>
      <h2 class="section-title" style="color:#fff"><?= htmlspecialchars($_hc['achievements_title']) ?></h2>
      <p class="section-desc" style="color:rgba(255,255,255,.7);margin:0 auto"><?= htmlspecialchars($_hc['achievements_desc']) ?></p>
    </div>
    <div class="achievements-grid">
      <?php foreach ($_ha as $_ach): ?>
      <div class="achievement-item">
        <div class="achievement-icon"><i class="<?= htmlspecialchars($_ach['icon'] ?: 'fas fa-star') ?>"></i></div>
        <div class="achievement-number">
          <span class="counter" data-target="<?= (int)$_ach['number'] ?>">0</span><span class="plus"><?= htmlspecialchars($_ach['suffix'] ?? '+') ?></span>
        </div>
        <p class="achievement-label"><?= htmlspecialchars($_ach['label']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ======= OUR CLIENTS / PARTNERS MARQUEE ======= -->
<section class="clients-section" id="clients">
  <div class="container">
    <div class="clients-header">
      <span class="section-label"><?= htmlspecialchars($_hc['partners_label']) ?></span>
      <h2 class="section-title"><?= htmlspecialchars($_hc['partners_title']) ?></h2>
    </div>
  </div>
  <?php if (!empty($partners)): ?>
  <div class="marquee-wrapper">
    <div class="marquee-track">
      <?php foreach ($partners as $p): ?>
      <?php if ($p['logo']): ?>
      <div class="partner-logo">
        <?php if ($p['website_url']): ?>
        <a href="<?= htmlspecialchars($p['website_url']) ?>" target="_blank" rel="noopener">
          <img src="<?= htmlspecialchars($p['logo']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
        </a>
        <?php else: ?>
        <img src="<?= htmlspecialchars($p['logo']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
      <!-- Duplicate for seamless loop -->
      <?php foreach ($partners as $p): ?>
      <?php if ($p['logo']): ?>
      <div class="partner-logo">
        <?php if ($p['website_url']): ?>
        <a href="<?= htmlspecialchars($p['website_url']) ?>" target="_blank" rel="noopener">
          <img src="<?= htmlspecialchars($p['logo']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
        </a>
        <?php else: ?>
        <img src="<?= htmlspecialchars($p['logo']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>


<!-- ======= TESTIMONIALS SLIDER — Our Clients Says ======= -->
<section class="testimonials-section" id="testimonials">
  <div class="container">
    <div class="testimonials-header">
      <span class="section-label"><?= htmlspecialchars($_hc['testimonials_label']) ?></span>
      <h2 class="section-title"><?= htmlspecialchars($_hc['testimonials_title']) ?></h2>
      <p class="section-desc" style="margin:0 auto;text-align:center"><?= htmlspecialchars($_hc['testimonials_desc']) ?></p>
    </div>

    <?php if (!empty($reviews)): ?>
    <div class="testi-slider">
      <div class="testi-track">
        <?php foreach ($reviews as $ri => $rv): ?>
        <div class="testi-slide <?= $ri === 0 ? 'active' : '' ?>">
          <div class="testimonial-card">
            <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">"<?= htmlspecialchars($rv['review']) ?>"</p>
            <div class="testimonial-author">
              <?php if ($rv['image']): ?>
              <img src="<?= htmlspecialchars($rv['image']) ?>" alt="<?= htmlspecialchars($rv['name']) ?>"
                   style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex-shrink:0" />
              <?php else: ?>
              <div class="testimonial-avatar"><?= strtoupper(substr($rv['name'], 0, 1)) ?></div>
              <?php endif; ?>
              <div class="testimonial-info">
                <h5><?= htmlspecialchars($rv['name']) ?></h5>
                <span><?= htmlspecialchars($rv['job_title']) ?></span>
              </div>
              <div class="testimonial-stars"><?= str_repeat('★', (int)$rv['stars']) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div><!-- /.testi-track -->

      <div class="testi-controls">
        <button class="testi-btn testi-prev" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
        <div class="testi-dots">
          <?php foreach ($reviews as $ri => $rv): ?>
          <span class="testi-dot <?= $ri === 0 ? 'active' : '' ?>"></span>
          <?php endforeach; ?>
        </div>
        <button class="testi-btn testi-next" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
      </div>
    </div><!-- /.testi-slider -->
    <?php endif; ?>
  </div>
</section>


<!-- ======= CONTACT FORM SECTION ======= -->
<section class="home-contact-section" id="home-contact">
  <div class="container">
    <div class="home-contact-inner">

      <div class="home-contact-text scroll-reveal r-left">
        <span class="section-label"><?= htmlspecialchars($_hc['contact_label']) ?></span>
        <h2 class="section-title" style="color:#fff"><?= nl2br(htmlspecialchars($_hc['contact_title'])) ?></h2>
        <p class="section-desc" style="color:rgba(255,255,255,.75)"><?= htmlspecialchars($_hc['contact_desc']) ?></p>
        <div class="home-contact-info">
          <?php if ($_si['phone']): ?>
          <div class="home-contact-info-item">
            <div class="hci-icon"><i class="fas fa-phone"></i></div>
            <span><?= htmlspecialchars($_si['phone']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($_si['email']): ?>
          <div class="home-contact-info-item">
            <div class="hci-icon"><i class="fas fa-envelope"></i></div>
            <span><?= htmlspecialchars($_si['email']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($_si['address']): ?>
          <div class="home-contact-info-item">
            <div class="hci-icon"><i class="fas fa-location-dot"></i></div>
            <span><?= htmlspecialchars($_si['address']) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="home-contact-form-wrap scroll-reveal r-right">
        <h3><?= htmlspecialchars($_hc['contact_form_title']) ?></h3>
        <p><?= htmlspecialchars($_hc['contact_form_subtitle']) ?></p>
        <form id="home-contact-form" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="hc-name">Full Name</label>
              <input type="text" id="hc-name" name="name" placeholder="Your full name" required />
            </div>
            <div class="form-group">
              <label for="hc-email">Email Address</label>
              <input type="email" id="hc-email" name="email" placeholder="your@email.com" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="hc-mobile">Phone Number</label>
              <input type="tel" id="hc-mobile" name="mobile" placeholder="+966 5x xxx xxxx" />
            </div>
            <div class="form-group">
              <label for="hc-service">Service Interested In</label>
              <select id="hc-service" name="service">
                <option value="">Select a service…</option>
                <option>Expert Technical Recruitment</option>
                <option>Scalable Outsourcing Solutions</option>
                <option>Enterprise Digital Transformation</option>
                <option>Tech Squad-as-a-Service</option>
                <option>Data Handling Solutions</option>
                <option>Other</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="hc-message">Message</label>
            <textarea id="hc-message" name="message" rows="4" placeholder="Tell us about your project…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary form-submit">
            Send Message <i class="fas fa-paper-plane"></i>
          </button>
          <div id="home-form-message" class="form-message"></div>
        </form>
      </div>

    </div>
  </div>
</section>


<?php include 'includes/footer.php'; ?>
