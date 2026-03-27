/* ========================================
   DGTEC Website — main.js
   jQuery + GSAP animations
   ======================================== */
$(function () {

  /* ---- Sticky / Floating Header ---- */
  function updateHeader() {
    if ($(window).scrollTop() > 60) {
      $('.site-header').addClass('scrolled');
    } else {
      $('.site-header').removeClass('scrolled');
    }
  }
  $(window).on('scroll', updateHeader);
  updateHeader(); // run on load

  /* ---- Mobile Hamburger ---- */
  $('.hamburger').on('click', function () {
    $(this).toggleClass('open');
    $('.nav-menu').toggleClass('open');
    $('body').toggleClass('nav-open');
  });

  // Close menu on nav-link click (mobile)
  $('.nav-link:not(.has-dropdown)').on('click', function () {
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
      const $dropdown = $(this).siblings('.dropdown');
      $dropdown.slideToggle(300);
      $(this).find('i').toggleClass('rotated');
    }
  });

  /* ---- Active nav on scroll ---- */
  const sections = $('section[id]');
  $(window).on('scroll', function () {
    const scrollY = $(this).scrollTop();
    sections.each(function () {
      const top = $(this).offset().top - 100;
      const bottom = top + $(this).outerHeight();
      const id = $(this).attr('id');
      if (scrollY >= top && scrollY < bottom) {
        $('.nav-link').removeClass('active');
        $(`.nav-link[href="#${id}"]`).addClass('active');
      }
    });
  });

  /* ---- Smooth scroll ---- */
  $('a[href^="#"]').on('click', function (e) {
    const target = $(this.getAttribute('href'));
    if (target.length) {
      e.preventDefault();
      $('html, body').stop().animate({ scrollTop: target.offset().top - 80 }, 700, 'swing');
    }
  });

  /* ---- Hero Slider ---- */
  var currentSlide = 0;
  var $slides = $('.hero-slide');
  var $dots = $('.hero-dot');
  var totalSlides = $slides.length;
  var slideTimer;

  function animateSlideIn($slide) {
    if (typeof gsap !== 'undefined') {
      gsap.fromTo(
        $slide.find('.hero-label, .hero-title, .hero-desc, .hero-btns'),
        { y: 36, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.65, stagger: 0.12, ease: 'power3.out', clearProps: 'all' }
      );
    }
  }

  function goToSlide(n) {
    $slides.removeClass('active');
    $dots.removeClass('active');
    currentSlide = ((n % totalSlides) + totalSlides) % totalSlides;
    var $active = $slides.eq(currentSlide);
    $active.addClass('active');
    $dots.eq(currentSlide).addClass('active');
    animateSlideIn($active);
  }

  function nextSlide() { goToSlide(currentSlide + 1); }
  function prevSlide() { goToSlide(currentSlide - 1); }

  function startAutoplay() {
    slideTimer = setInterval(nextSlide, 5500);
  }
  function resetAutoplay() {
    clearInterval(slideTimer);
    startAutoplay();
  }

  $('.hero-next').on('click', function () { nextSlide(); resetAutoplay(); });
  $('.hero-prev').on('click', function () { prevSlide(); resetAutoplay(); });
  $('.hero-dot').on('click', function () { goToSlide($(this).index()); resetAutoplay(); });

  // Kick off
  if (totalSlides > 0) {
    // First slide already has .active in HTML; just animate + start timer
    setTimeout(function () { animateSlideIn($slides.eq(0)); }, 300);
    startAutoplay();
  }

  /* ---- GSAP Scroll Animations ---- */
  if (typeof gsap !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    // Services cards - stagger on scroll
    gsap.from('.service-card', {
      scrollTrigger: { trigger: '.services-section', start: 'top 75%' },
      y: 50, opacity: 0, duration: 0.6, stagger: 0.15, ease: 'power3.out'
    });

    // Solutions
    gsap.from('.solutions-content', {
      scrollTrigger: { trigger: '.solutions-section', start: 'top 70%' },
      x: -50, opacity: 0, duration: 0.8, ease: 'power3.out'
    });
    gsap.from('.solutions-image', {
      scrollTrigger: { trigger: '.solutions-section', start: 'top 70%' },
      x: 50, opacity: 0, duration: 0.8, ease: 'power3.out'
    });

    // Process steps
    gsap.from('.process-step', {
      scrollTrigger: { trigger: '.process-section', start: 'top 75%' },
      y: 60, opacity: 0, duration: 0.5, stagger: 0.12, ease: 'power3.out'
    });

    // Why cards (about page)
    gsap.from('.why-card', {
      scrollTrigger: { trigger: '.why-choose-section', start: 'top 75%' },
      y: 50, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out'
    });

    // Contact cards
    gsap.from('.contact-card', {
      scrollTrigger: { trigger: '.contact-section', start: 'top 75%' },
      x: -40, opacity: 0, duration: 0.5, stagger: 0.15, ease: 'power3.out'
    });
    gsap.from('.contact-form-wrap', {
      scrollTrigger: { trigger: '.contact-section', start: 'top 75%' },
      x: 40, opacity: 0, duration: 0.7, ease: 'power3.out'
    });

    // Listing cards (solutions.php / services.php)
    gsap.from('.listing-card', {
      scrollTrigger: { trigger: '.listing-cards', start: 'top 75%' },
      y: 60, opacity: 0, duration: 0.65, stagger: 0.18, ease: 'power3.out'
    });

    // Inner page overview
    gsap.from('.inner-overview-text', {
      scrollTrigger: { trigger: '.inner-overview', start: 'top 70%' },
      x: -50, opacity: 0, duration: 0.8, ease: 'power3.out'
    });
    gsap.from('.inner-overview-image', {
      scrollTrigger: { trigger: '.inner-overview', start: 'top 70%' },
      x: 50, opacity: 0, duration: 0.8, ease: 'power3.out'
    });
    gsap.from('.inner-feature-card', {
      scrollTrigger: { trigger: '.inner-features', start: 'top 75%' },
      y: 50, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out'
    });
    gsap.from('.inner-highlight-item', {
      scrollTrigger: { trigger: '.inner-highlights', start: 'top 80%' },
      y: 30, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out'
    });

    // Blog cards
    gsap.from('.blog-card', {
      scrollTrigger: { trigger: '.blog-grid', start: 'top 75%' },
      y: 50, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out'
    });

    // Post content
    gsap.from('.post-content', {
      scrollTrigger: { trigger: '.post-section', start: 'top 75%' },
      y: 40, opacity: 0, duration: 0.7, ease: 'power3.out'
    });
    gsap.from('.post-sidebar', {
      scrollTrigger: { trigger: '.post-section', start: 'top 75%' },
      x: 40, opacity: 0, duration: 0.7, ease: 'power3.out'
    });

    // Testimonials
    gsap.from('.testimonial-card', {
      scrollTrigger: { trigger: '.testimonials-section', start: 'top 75%' },
      y: 50, opacity: 0, duration: 0.55, stagger: 0.15, ease: 'power3.out'
    });

    // Home contact form
    gsap.from('.home-contact-text', {
      scrollTrigger: { trigger: '.home-contact-section', start: 'top 70%' },
      x: -40, opacity: 0, duration: 0.8, ease: 'power3.out'
    });
    gsap.from('.home-contact-form-wrap', {
      scrollTrigger: { trigger: '.home-contact-section', start: 'top 70%' },
      x: 40, opacity: 0, duration: 0.8, ease: 'power3.out'
    });

    // Process footer banner
    gsap.from('.process-footer-inner', {
      scrollTrigger: { trigger: '.process-footer', start: 'top 85%' },
      y: 30, opacity: 0, duration: 0.6, ease: 'power3.out'
    });

    // Achievements header
    gsap.from('.achievements-header', {
      scrollTrigger: { trigger: '.achievements-section', start: 'top 75%' },
      y: 30, opacity: 0, duration: 0.6, ease: 'power3.out'
    });

    // Page hero
    gsap.from('.page-hero-content', {
      y: 40, opacity: 0, duration: 0.8, ease: 'power3.out', delay: 0.3
    });

    // About intro
    gsap.from('.about-image-wrap', {
      scrollTrigger: { trigger: '.about-intro', start: 'top 75%' },
      scale: 0.95, opacity: 0, duration: 0.8, ease: 'power3.out'
    });
    gsap.from('.about-text', {
      scrollTrigger: { trigger: '.about-intro', start: 'top 75%' },
      x: 40, opacity: 0, duration: 0.8, ease: 'power3.out'
    });
  }

  /* ---- Counter Animations ---- */
  function animateCounters() {
    $('.counter').each(function () {
      const $el = $(this);
      const target = parseInt($el.data('target'), 10);
      const duration = 2000;
      const step = target / (duration / 16);
      let current = 0;
      const timer = setInterval(function () {
        current += step;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        $el.text(Math.floor(current).toLocaleString());
      }, 16);
    });
  }

  // Trigger counters when achievements section is visible
  let countersStarted = false;
  $(window).on('scroll', function () {
    if (countersStarted) return;
    const $section = $('.achievements-section');
    if ($section.length === 0) return;
    const top = $section.offset().top;
    if ($(this).scrollTop() + $(this).height() > top + 100) {
      countersStarted = true;
      animateCounters();
    }
  });

  /* ---- Contact Form Submit ---- */
  $('#contact-form').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('.form-submit');
    const $msg = $('#form-message');

    // Loading state
    $btn.prop('disabled', true).html('<span class="spinner"></span> Sending...');
    $msg.hide().removeClass('success error');

    $.ajax({
      url: 'php/contact-process.php',
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function (res) {
        $msg.text(res.message).addClass(res.success ? 'success' : 'error').show();
        if (res.success) {
          $form[0].reset();
          // GSAP success effect
          if (typeof gsap !== 'undefined') {
            gsap.from($msg[0], { scale: 0.9, opacity: 0, duration: 0.4, ease: 'back.out(2)' });
          }
        }
      },
      error: function () {
        $msg.text('Network error. Please try again.').addClass('error').show();
      },
      complete: function () {
        $btn.prop('disabled', false).html('Send Message <i class="fas fa-paper-plane"></i>');
      }
    });
  });

  /* ---- Solution Item Active Toggle ---- */
  $('.solution-item').on('click', function () {
    $('.solution-item').removeClass('active');
    $(this).addClass('active');
  });

  // Set first active by default
  $('.solution-item:first').addClass('active');

  /* ---- Marquee pause on hover ---- */
  $('.marquee-track').on('mouseenter', function () {
    $(this).css('animation-play-state', 'paused');
  }).on('mouseleave', function () {
    $(this).css('animation-play-state', 'running');
  });

  /* ---- Home Contact Form Submit ---- */
  $('#home-contact-form').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('.form-submit');
    const $msg = $('#home-form-message');

    $btn.prop('disabled', true).html('<span class="spinner"></span> Sending...');
    $msg.hide().removeClass('success error');

    $.ajax({
      url: 'php/contact-process.php',
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function (res) {
        $msg.text(res.message).addClass(res.success ? 'success' : 'error').show();
        if (res.success) {
          $form[0].reset();
          if (typeof gsap !== 'undefined') {
            gsap.from($msg[0], { scale: 0.9, opacity: 0, duration: 0.4, ease: 'back.out(2)' });
          }
        }
      },
      error: function () {
        $msg.text('Network error. Please try again.').addClass('error').show();
      },
      complete: function () {
        $btn.prop('disabled', false).html('Send Message <i class="fas fa-paper-plane"></i>');
      }
    });
  });

});
