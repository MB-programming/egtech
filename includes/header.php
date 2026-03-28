<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $page_desc ?? 'DGTEC – Leading integrated solutions company delivering advanced Technical recruitment, outsourcing, AI and digital transformation in Saudi Arabia.' ?>" />
  <title><?= $page_title ?? 'DGTEC – Technological Transformation in The Kingdom' ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/webp" href="assets/images/logo.webp" />

  <!-- Font Asap via Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Asap:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

  <!-- Main CSS -->
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- ======= HEADER ======= -->
<header class="site-header">
  <div class="container">
    <nav class="navbar">

      <!-- Logo -->
      <a href="index.php" class="nav-logo">
        <img src="assets/images/logo.webp" alt="DGTEC Logo" width="140" height="48" />
      </a>

      <!-- Nav Menu -->
      <ul class="nav-menu" id="nav-menu">
        <li class="nav-item">
          <a href="index.php" class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>">Home</a>
        </li>
        <li class="nav-item">
          <a href="about.php" class="nav-link <?= $current_page === 'about' ? 'active' : '' ?>">About</a>
        </li>
        <li class="nav-item has-dropdown">
          <a href="solutions.php" class="nav-link <?= $current_page === 'solutions' ? 'active' : '' ?>">Our Solutions <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown">
            <li><a href="solutions.php" class="dd-view-all"><i class="fas fa-grip"></i> View All Solutions <i class="fas fa-arrow-right dd-arrow"></i></a></li>
            <li class="dd-sep"></li>
            <li><a href="solution-digital-onboarding.php" class="dd-item"><span class="dd-icon"><i class="fas fa-id-card-clip"></i></span><span class="dd-label">Digital Onboarding &amp; Compliance</span></a></li>
            <li><a href="solution-enterprise-automation.php" class="dd-item"><span class="dd-icon"><i class="fas fa-robot"></i></span><span class="dd-label">Enterprise Content &amp; Process Automation</span></a></li>
            <li><a href="solution-tea-boy.php" class="dd-item"><span class="dd-icon"><i class="fas fa-mug-hot"></i></span><span class="dd-label">Tea Boy – Smart Internal Operations</span></a></li>
          </ul>
        </li>
        <li class="nav-item has-dropdown">
          <a href="services.php" class="nav-link <?= $current_page === 'services' ? 'active' : '' ?>">Our Services <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown">
            <li><a href="services.php" class="dd-view-all"><i class="fas fa-grip"></i> View All Services <i class="fas fa-arrow-right dd-arrow"></i></a></li>
            <li class="dd-sep"></li>
            <li><a href="service-recruitment.php" class="dd-item"><span class="dd-icon"><i class="fas fa-users"></i></span><span class="dd-label">Expert Technical Recruitment</span></a></li>
            <li><a href="service-outsourcing.php" class="dd-item"><span class="dd-icon"><i class="fas fa-handshake"></i></span><span class="dd-label">Scalable Outsourcing Solutions</span></a></li>
            <li><a href="service-digital-transformation.php" class="dd-item"><span class="dd-icon"><i class="fas fa-wand-magic-sparkles"></i></span><span class="dd-label">Enterprise Digital Transformation</span></a></li>
            <li><a href="service-tech-squad.php" class="dd-item"><span class="dd-icon"><i class="fas fa-code"></i></span><span class="dd-label">Tech Squad-as-a-Service</span></a></li>
            <li><a href="service-data-handling.php" class="dd-item"><span class="dd-icon"><i class="fas fa-database"></i></span><span class="dd-label">Data Handling Solutions</span></a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="blog.php" class="nav-link <?= $current_page === 'blog' ? 'active' : '' ?>">Blogs</a>
        </li>
        <li class="nav-item">
          <a href="contact.php" class="nav-link <?= $current_page === 'contact' ? 'active' : '' ?>">Contact</a>
        </li>
        <!-- CTA inside mobile menu only -->
        <li class="nav-item nav-cta-mobile">
          <a href="contact.php" class="btn btn-primary">Free Consultation</a>
        </li>
      </ul>

      <!-- Desktop CTA (hidden on mobile via CSS) -->
      <a href="contact.php" class="btn btn-primary nav-cta-desktop">Free Consultation</a>

      <!-- Hamburger -->
      <button class="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>

    </nav>
  </div>
</header>
