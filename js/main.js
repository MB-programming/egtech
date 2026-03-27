/* ========================================
   DGTEC Website — main.js
   jQuery + GSAP animations
   ======================================== */
$(function () {

  /* ---- Sticky Header ---- */
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 60) {
      $('.site-header').addClass('scrolled');
    } else {
      $('.site-header').removeClass('scrolled');
    }
  });

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

  /* ---- GSAP Hero Animations ---- */
  if (typeof gsap !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    // Hero entrance
    const heroTl = gsap.timeline({ delay: 0.2 });
    heroTl
      .from('.hero-label',   { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' })
      .from('.hero-title',   { y: 40, opacity: 0, duration: 0.7, ease: 'power3.out' }, '-=0.3')
      .from('.hero-desc',    { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' }, '-=0.4')
      .from('.hero-btns',    { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' }, '-=0.4')
      .from('.hero-scroll',  { opacity: 0, duration: 0.5 }, '-=0.2');

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

  /* ---- Hero parallax ---- */
  $(window).on('scroll', function () {
    const scrolled = $(this).scrollTop();
    $('.hero-bg').css('transform', `translateY(${scrolled * 0.35}px)`);
  });

});
