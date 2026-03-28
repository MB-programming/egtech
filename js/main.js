/* ========================================
   DGTEC Website — main.js
   jQuery + GSAP (hero entrance only) + IntersectionObserver
   ======================================== */
$(function () {

  /* ---- Floating Header ---- */
  function updateHeader() {
    $('.site-header').toggleClass('scrolled', $(window).scrollTop() > 60);
  }
  $(window).on('scroll.header', updateHeader);
  updateHeader();

  /* ---- Mobile Hamburger ---- */
  $('.hamburger').on('click', function () {
    $(this).toggleClass('open');
    $('.nav-menu').toggleClass('open');
    $('body').toggleClass('nav-open');
  });

  // Close menu when a non-dropdown link is clicked
  $('.nav-menu .nav-link').not('.has-dropdown > .nav-link').on('click', function () {
    if ($('.nav-menu').hasClass('open')) {
      $('.hamburger').removeClass('open');
      $('.nav-menu').removeClass('open');
      $('body').removeClass('nav-open');
    }
  });

  // Mobile: toggle dropdowns on parent click
  $('.nav-item.has-dropdown > .nav-link').on('click', function (e) {
    if (window.innerWidth <= 768) {
      e.preventDefault();
      var $dd = $(this).siblings('.dropdown');
      $dd.slideToggle(280);
      $(this).find('i.fa-chevron-down').toggleClass('rotated');
    }
  });

  /* ---- Smooth scroll for on-page anchor links ---- */
  $('a[href^="#"]').on('click', function (e) {
    var $t = $(this.getAttribute('href'));
    if ($t.length) {
      e.preventDefault();
      $('html,body').stop().animate({ scrollTop: $t.offset().top - 80 }, 700, 'swing');
    }
  });

  /* ================================================================
     HERO SLIDER
  ================================================================ */
  var $slides = $('.hero-slide');
  var $dots   = $('.hero-dot');

  if ($slides.length > 0) {
    var current   = 0;
    var total     = $slides.length;
    var autoTimer = null;

    function showSlide(n) {
      $slides.eq(current).removeClass('active');
      $dots.eq(current).removeClass('active');
      current = ((n % total) + total) % total;
      $slides.eq(current).addClass('active');
      $dots.eq(current).addClass('active');
    }
    function startAuto() { autoTimer = setInterval(function () { showSlide(current + 1); }, 5500); }
    function resetAuto() { clearInterval(autoTimer); startAuto(); }

    $('.hero-next').on('click', function () { showSlide(current + 1); resetAuto(); });
    $('.hero-prev').on('click', function () { showSlide(current - 1); resetAuto(); });
    $dots.on('click', function () { showSlide($(this).index()); resetAuto(); });
    startAuto();
  }

  /* ================================================================
     GSAP — hero text & page-hero entrance ONLY (no ScrollTrigger)
  ================================================================ */
  if (typeof gsap !== 'undefined') {
    try {
      if (document.querySelector('.hero-slide.active .hero-text')) {
        gsap.fromTo(
          '.hero-slide.active .hero-text > *',
          { y: 28, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.65, stagger: 0.12,
            ease: 'power3.out', delay: 0.35, clearProps: 'all' }
        );
      }
      if (document.querySelector('.page-hero-content')) {
        gsap.fromTo(
          '.page-hero-content',
          { y: 36, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.75, ease: 'power3.out',
            delay: 0.25, clearProps: 'all' }
        );
      }
    } catch (e) { /* silent */ }
  }

  /* ================================================================
     SCROLL REVEAL — IntersectionObserver
     Elements get scroll-reveal classes directly in HTML (index.php)
     OR via JS below for inner / listing pages.
  ================================================================ */

  /* Auto-apply classes for inner pages (these pages don't have classes in HTML) */
  var pageClasses = [
    /* listing pages */
    { sel: '.listing-card',         dir: 'r-up',    step: 0 },
    /* inner detail pages */
    { sel: '.inner-overview-text',  dir: 'r-left',  step: 0 },
    { sel: '.inner-overview-image', dir: 'r-right', step: 0 },
    { sel: '.inner-feature-card',   dir: 'r-up',    step: 0.10 },
    { sel: '.inner-highlight-item', dir: 'r-up',    step: 0.10 },
    /* blog */
    { sel: '.blog-card',            dir: 'r-up',    step: 0.10 },
    { sel: '.post-content',         dir: 'r-up',    step: 0 },
    { sel: '.post-sidebar',         dir: 'r-right', step: 0 },
    /* about */
    { sel: '.why-card',             dir: 'r-up',    step: 0.10 },
    { sel: '.about-image-wrap',     dir: 'r-left',  step: 0 },
    { sel: '.about-text',           dir: 'r-right', step: 0 },
    /* contact */
    { sel: '.contact-card',         dir: 'r-left',  step: 0.10 },
    { sel: '.contact-form-wrap',    dir: 'r-right', step: 0 },
  ];

  pageClasses.forEach(function (cfg) {
    var els = document.querySelectorAll(cfg.sel);
    if (!els.length) return;
    /* Skip if already has a reveal direction class (set in HTML) */
    els.forEach(function (el, i) {
      if (el.classList.contains('r-up') || el.classList.contains('r-left') || el.classList.contains('r-right')) return;
      el.classList.add('scroll-reveal', cfg.dir);
      if (cfg.step > 0) el.style.transitionDelay = (i * cfg.step) + 's';
    });
  });

  /* Set up observer for ALL .scroll-reveal elements (HTML-placed + JS-placed) */
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('scroll-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.05 });

    document.querySelectorAll('.scroll-reveal').forEach(function (el) {
      observer.observe(el);
    });
  } else {
    /* No IntersectionObserver — show everything */
    document.querySelectorAll('.scroll-reveal').forEach(function (el) {
      el.classList.add('scroll-visible');
    });
  }

  /* ================================================================
     COUNTER ANIMATIONS (Achievements section)
  ================================================================ */
  function animateCounters() {
    $('.counter').each(function () {
      var $el    = $(this);
      var target = parseInt($el.data('target'), 10);
      var step   = target / (2000 / 16);
      var cur    = 0;
      var timer  = setInterval(function () {
        cur += step;
        if (cur >= target) { cur = target; clearInterval(timer); }
        $el.text(Math.floor(cur).toLocaleString());
      }, 16);
    });
  }
  var countersRun = false;
  $(window).on('scroll.counters', function () {
    if (countersRun) return;
    var $s = $('.achievements-section');
    if (!$s.length) return;
    if ($(this).scrollTop() + $(this).height() > $s.offset().top + 100) {
      countersRun = true;
      $(window).off('scroll.counters');
      animateCounters();
    }
  });

  /* ================================================================
     CONTACT FORM — contact.php
  ================================================================ */
  $('#contact-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this),
        $btn  = $form.find('.form-submit'),
        $msg  = $('#form-message');
    $btn.prop('disabled', true).html('<span class="spinner"></span> Sending…');
    $msg.hide().removeClass('success error');
    $.ajax({
      url: 'php/contact-process.php', type: 'POST',
      data: $form.serialize(), dataType: 'json',
      success: function (r) {
        $msg.text(r.message).addClass(r.success ? 'success' : 'error').show();
        if (r.success) $form[0].reset();
      },
      error: function () { $msg.text('Network error. Please try again.').addClass('error').show(); },
      complete: function () { $btn.prop('disabled', false).html('Send Message <i class="fas fa-paper-plane"></i>'); }
    });
  });

  /* ================================================================
     HOME CONTACT FORM — index.php
  ================================================================ */
  $('#home-contact-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this),
        $btn  = $form.find('.form-submit'),
        $msg  = $('#home-form-message');
    $btn.prop('disabled', true).html('<span class="spinner"></span> Sending…');
    $msg.hide().removeClass('success error');
    $.ajax({
      url: 'php/contact-process.php', type: 'POST',
      data: $form.serialize(), dataType: 'json',
      success: function (r) {
        $msg.text(r.message).addClass(r.success ? 'success' : 'error').show();
        if (r.success) $form[0].reset();
      },
      error: function () { $msg.text('Network error. Please try again.').addClass('error').show(); },
      complete: function () { $btn.prop('disabled', false).html('Send Message <i class="fas fa-paper-plane"></i>'); }
    });
  });

  /* ---- Solution item active toggle ---- */
  $('.solution-item').on('click', function () {
    $('.solution-item').removeClass('active');
    $(this).addClass('active');
  });

  /* ---- Marquee pause on hover ---- */
  $('.marquee-track')
    .on('mouseenter', function () { $(this).css('animation-play-state', 'paused'); })
    .on('mouseleave', function () { $(this).css('animation-play-state', 'running'); });

});
