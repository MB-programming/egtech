/* ========================================
   DGTEC Website — main.js
   jQuery + GSAP animations
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

  // Close menu on link click (mobile)
  $('.nav-menu .nav-link').not('.has-dropdown > .nav-link').on('click', function () {
    if ($('.nav-menu').hasClass('open')) {
      $('.hamburger').removeClass('open');
      $('.nav-menu').removeClass('open');
      $('body').removeClass('nav-open');
    }
  });

  // Mobile dropdown toggle
  $('.nav-item.has-dropdown > .nav-link').on('click', function (e) {
    if (window.innerWidth <= 768) {
      e.preventDefault();
      $(this).siblings('.dropdown').slideToggle(300);
      $(this).find('i').toggleClass('rotated');
    }
  });

  /* ---- Active nav link on scroll ---- */
  var $sections = $('section[id]');
  if ($sections.length) {
    $(window).on('scroll.nav', function () {
      var scrollY = $(this).scrollTop();
      $sections.each(function () {
        var top    = $(this).offset().top - 100;
        var bottom = top + $(this).outerHeight();
        var id     = $(this).attr('id');
        if (scrollY >= top && scrollY < bottom) {
          $('.nav-link').removeClass('active');
          $('.nav-link[href="#' + id + '"]').addClass('active');
        }
      });
    });
  }

  /* ---- Smooth scroll for anchor links ---- */
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
      // remove active from current
      $slides.eq(current).removeClass('active');
      $dots.eq(current).removeClass('active');

      // switch
      current = ((n % total) + total) % total;

      $slides.eq(current).addClass('active');
      $dots.eq(current).addClass('active');
    }

    function startAuto() {
      autoTimer = setInterval(function () { showSlide(current + 1); }, 5500);
    }
    function resetAuto() { clearInterval(autoTimer); startAuto(); }

    $('.hero-next').on('click', function () { showSlide(current + 1); resetAuto(); });
    $('.hero-prev').on('click', function () { showSlide(current - 1); resetAuto(); });
    $dots.on('click', function () { showSlide($(this).index()); resetAuto(); });

    startAuto();
  }

  /* ================================================================
     GSAP SCROLL ANIMATIONS
     Key rules applied to every animation:
       • immediateRender: false  → GSAP does NOT set opacity:0 until trigger fires
       • clearProps: 'all'       → removes all inline styles after animation ends
       • once: true              → plays once, does not reverse on scroll up
       • Existence check         → only animate if the selector exists on this page
  ================================================================ */

  if (typeof gsap !== 'undefined') {

    try {

      gsap.registerPlugin(ScrollTrigger);

      /* Helper — only animates if at least one element matches */
      function anim(selector, fromVars, triggerEl, startPos) {
        if (!document.querySelector(selector)) return;
        fromVars.immediateRender = false;
        fromVars.clearProps      = 'all';
        fromVars.scrollTrigger  = {
          trigger     : triggerEl || selector,
          start       : startPos  || 'top 88%',
          once        : true,
          toggleActions: 'play none none none'
        };
        gsap.from(selector, fromVars);
      }

      /* ---- Hero text entrance (no scroll trigger — fires on load) ---- */
      if (document.querySelector('.hero-slide')) {
        gsap.fromTo(
          '.hero-slide.active .hero-text > *',
          { y: 28, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.65, stagger: 0.12,
            ease: 'power3.out', delay: 0.4, clearProps: 'all' }
        );
      }

      /* ---- Page hero (about / contact / inner pages) ---- */
      if (document.querySelector('.page-hero-content')) {
        gsap.fromTo(
          '.page-hero-content',
          { y: 40, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out',
            delay: 0.3, clearProps: 'all' }
        );
      }

      /* ---- Homepage sections ---- */
      anim('.service-card',       { y: 50, opacity: 0, duration: 0.55, stagger: 0.12, ease: 'power3.out' }, '.services-section');
      anim('.solutions-content',  { x: -50, opacity: 0, duration: 0.7,  ease: 'power3.out' }, '.solutions-section');
      anim('.solutions-image',    { x:  50, opacity: 0, duration: 0.7,  ease: 'power3.out' }, '.solutions-section');
      anim('.process-step',       { y: 55, opacity: 0, duration: 0.5, stagger: 0.12, ease: 'power3.out' }, '.process-section');
      anim('.process-footer-inner', { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' }, '.process-footer');
      anim('.achievements-header',  { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' }, '.achievements-section');
      anim('.testimonial-card',     { y: 50, opacity: 0, duration: 0.55, stagger: 0.15, ease: 'power3.out' }, '.testimonials-section');
      anim('.home-contact-text',      { x: -40, opacity: 0, duration: 0.8, ease: 'power3.out' }, '.home-contact-section');
      anim('.home-contact-form-wrap', { x:  40, opacity: 0, duration: 0.8, ease: 'power3.out' }, '.home-contact-section');

      /* ---- Listing pages (solutions.php / services.php) ---- */
      anim('.listing-card', { y: 60, opacity: 0, duration: 0.65, stagger: 0.18, ease: 'power3.out' }, '.listing-cards');

      /* ---- Inner detail pages ---- */
      anim('.inner-overview-text',  { x: -50, opacity: 0, duration: 0.8, ease: 'power3.out' }, '.inner-overview');
      anim('.inner-overview-image', { x:  50, opacity: 0, duration: 0.8, ease: 'power3.out' }, '.inner-overview');
      anim('.inner-feature-card',   { y: 50, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }, '.inner-features');
      anim('.inner-highlight-item', { y: 30, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }, '.inner-highlights-grid');

      /* ---- Blog pages ---- */
      anim('.blog-card',    { y: 50, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }, '.blog-grid');
      anim('.post-content', { y: 40, opacity: 0, duration: 0.7, ease: 'power3.out' }, '.post-section');
      anim('.post-sidebar', { x: 40, opacity: 0, duration: 0.7, ease: 'power3.out' }, '.post-section');

      /* ---- About page ---- */
      anim('.why-card',        { y: 50, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }, '.why-choose-section');
      anim('.about-image-wrap',{ scale: 0.95, opacity: 0, duration: 0.8, ease: 'power3.out' }, '.about-intro');
      anim('.about-text',      { x: 40, opacity: 0, duration: 0.8, ease: 'power3.out' }, '.about-intro');

      /* ---- Contact page ---- */
      anim('.contact-card',     { x: -40, opacity: 0, duration: 0.5, stagger: 0.15, ease: 'power3.out' }, '.contact-section');
      anim('.contact-form-wrap',{ x:  40, opacity: 0, duration: 0.7, ease: 'power3.out' }, '.contact-section');

    } catch (err) {
      /* If GSAP fails for any reason, elements stay visible (no opacity:0 applied) */
      console.warn('GSAP init error:', err);
    }

  } // end gsap block


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
      url     : 'php/contact-process.php',
      type    : 'POST',
      data    : $form.serialize(),
      dataType: 'json',
      success : function (res) {
        $msg.text(res.message).addClass(res.success ? 'success' : 'error').show();
        if (res.success) $form[0].reset();
      },
      error   : function () { $msg.text('Network error. Please try again.').addClass('error').show(); },
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
      url     : 'php/contact-process.php',
      type    : 'POST',
      data    : $form.serialize(),
      dataType: 'json',
      success : function (res) {
        $msg.text(res.message).addClass(res.success ? 'success' : 'error').show();
        if (res.success) $form[0].reset();
      },
      error   : function () { $msg.text('Network error. Please try again.').addClass('error').show(); },
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
