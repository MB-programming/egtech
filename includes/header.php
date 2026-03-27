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
          <a href="#" class="nav-link">Our Solutions <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown">
            <li><a href="#">Digital Onboarding &amp; Compliance</a></li>
            <li><a href="#">Enterprise Content &amp; Process Automation</a></li>
            <li><a href="#">Tea Boy – Smart Internal Operations</a></li>
          </ul>
        </li>
        <li class="nav-item has-dropdown">
          <a href="#" class="nav-link">Our Services <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown">
            <li><a href="#">Expert Technical Recruitment</a></li>
            <li><a href="#">Scalable Outsourcing Solutions</a></li>
            <li><a href="#">Enterprise Digital Transformation</a></li>
            <li><a href="#">Tech Squad-as-a-Service</a></li>
            <li><a href="#">Data Handling Solutions</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">Blogs</a>
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
