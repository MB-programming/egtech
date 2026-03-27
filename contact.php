<?php
$page_title = 'Contact DGTEC – Get In Touch';
$page_desc  = 'Contact DGTEC for free consultation, technical recruitment, outsourcing solutions, and digital transformation services in Saudi Arabia.';
include 'includes/header.php';
?>

<!-- ======= PAGE HERO ======= -->
<section class="page-hero">
  <div class="container page-hero-content">
    <h1>Contact</h1>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <i class="fas fa-chevron-right" style="font-size:10px;"></i>
      <span>Contact</span>
    </nav>
  </div>
</section>

<!-- ======= CONTACT SECTION ======= -->
<section class="contact-section">
  <div class="container">

    <!-- Section Header -->
    <div style="text-align:center; margin-bottom:60px;">
      <span class="section-label">Get In Touch</span>
      <h2 class="section-title" style="margin:0 auto 12px;">SAY HELLO</h2>
      <p class="section-desc" style="margin:0 auto;">
        Get in touch with us and let's discuss how we can help transform your business.
      </p>
    </div>

    <div class="contact-grid">

      <!-- Contact Info Cards -->
      <div class="contact-info-cards">

        <div class="contact-card">
          <div class="contact-card-icon">
            <i class="fas fa-phone"></i>
          </div>
          <div class="contact-card-text">
            <h4>Call us now</h4>
            <a href="tel:+966539796000">+966 539 796 000</a>
          </div>
        </div>

        <div class="contact-card">
          <div class="contact-card-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="contact-card-text">
            <h4>Support email</h4>
            <a href="mailto:projects@dgtec.com.sa">projects@dgtec.com.sa</a>
          </div>
        </div>

        <div class="contact-card">
          <div class="contact-card-icon">
            <i class="fas fa-location-dot"></i>
          </div>
          <div class="contact-card-text">
            <h4>Our address</h4>
            <p>King Abdulaziz Rd, As Sulimaniyah,<br>Riyadh 12243, Saudi Arabia</p>
          </div>
        </div>

        <!-- Contact image -->
        <div style="border-radius:var(--radius); overflow:hidden; margin-top:8px;">
          <img src="assets/images/contact-us.webp" alt="Contact DGTEC" style="width:100%; height:220px; object-fit:cover;" loading="lazy" />
        </div>

      </div>

      <!-- Contact Form -->
      <div class="contact-form-wrap">
        <h3>Send us a message</h3>
        <p>Fill in the form below and we'll get back to you as soon as possible.</p>

        <form id="contact-form" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="name">Your Name <span style="color:#e74c3c;">*</span></label>
              <input type="text" id="name" name="name" placeholder="John Doe" required />
            </div>
            <div class="form-group">
              <label for="email">Email Address <span style="color:#e74c3c;">*</span></label>
              <input type="email" id="email" name="email" placeholder="john@example.com" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="mobile">Mobile Number <span style="color:#e74c3c;">*</span></label>
              <input type="tel" id="mobile" name="mobile" placeholder="+966 5XX XXX XXXX" required />
            </div>
            <div class="form-group">
              <label for="service">Service Name <span style="color:#e74c3c;">*</span></label>
              <input type="text" id="service" name="service" placeholder="e.g. Technical Recruitment" required />
            </div>
          </div>

          <div class="form-group">
            <label for="message">Your Message <span style="color:var(--gray); font-weight:400;">(Optional)</span></label>
            <textarea id="message" name="message" placeholder="Tell us about your project or inquiry..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary form-submit">
            Send Message <i class="fas fa-paper-plane"></i>
          </button>

          <div id="form-message" class="form-message" role="alert"></div>
        </form>
      </div>

    </div>

    <!-- Google Map embed (optional - replace src if needed) -->
    <div style="margin-top:70px; border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.3432857839956!2d46.67481531500003!3d24.688760984133185!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f03e8f7b33d67%3A0x7d1a1a1a1a1a1a1a!2sKing%20Abdulaziz%20Rd%2C%20As%20Sulimaniyah%2C%20Riyadh%2012243%2C%20Saudi%20Arabia!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s"
        width="100%"
        height="380"
        style="border:0; display:block;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        title="DGTEC Location"
      ></iframe>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
