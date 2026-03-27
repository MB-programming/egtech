/* ========================================
   DGTEC Website — main.js
   jQuery + GSAP (hero only) + IntersectionObserver (scroll reveals)
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
      $slides.eq(current).removeClass('active');
      $dots.eq(current).removeClass('active');

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
     GSAP — hero text entrance ONLY (no ScrollTrigger)
  ================================================================ */
  if (typeof gsap !== 'undefined') {
    try {
      /* Hero slider text entrance */
      if (document.querySelector('.hero-slide')) {
        gsap.fromTo(
          '.hero-slide.active .hero-text > *',
          { y: 28, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.65, stagger: 0.12,
            ease: 'power3.out', delay: 0.4, clearProps: 'all' }
        );
      }

      /* Page hero (about / contact / inner pages) */
      if (document.querySelector('.page-hero-content')) {
        gsap.fromTo(
          '.page-hero-content',
          { y: 40, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out',
            delay: 0.3, clearProps: 'all' }
        );
      }
    } catch (err) {
      console.warn('GSAP init error:', err);
    }
  }

  /* ================================================================
     SCROLL REVEAL — IntersectionObserver driven
     Each entry: [ selector, direction, staggerDelay ]
       direction: 'up' | 'left' | 'right'
       staggerDelay: seconds between sibling items (0 = no stagger)
  ================================================================ */

  var REVEAL_MAP = [
    /* Homepage */
    ['.service-card',           'up',    0.10],
    ['.solutions-content',      'left',  0   ],
    ['.solutions-image',        'right', 0   ],
    ['.process-step',           'up',    0.12],
    ['.process-footer-inner',   'up',    0   ],
    ['.achievements-header',    'up',    0   ],
    ['.testimonial-card',       'up',    0.12],
    ['.home-contact-text',      'left',  0   ],
    ['.home-contact-form-wrap', 'right', 0   ],
    /* Listing pages */
    ['.listing-card',           'up',    0   ],
    /* Inner detail pages */
    ['.inner-overview-text',    'left',  0   ],
    ['.inner-overview-image',   'right', 0   ],
    ['.inner-feature-card',     'up',    0.10],
    ['.inner-highlight-item',   'up',    0.10],
    /* Blog */
    ['.blog-card',              'up',    0.10],
    ['.post-content',           'up',    0   ],
    ['.post-sidebar',           'right', 0   ],
    /* About */
    ['.why-card',               'up',    0.10],
    ['.about-image-wrap',       'left',  0   ],
    ['.about-text',             'right', 0   ],
    /* Contact */
    ['.contact-card',           'left',  0.10],
    ['.contact-form-wrap',      'right', 0   ],
  ];

  /* Group siblings so stagger is calculated per-group */
  function applyRevealClasses() {
    REVEAL_MAP.forEach(function (entry) {
      var selector    = entry[0];
      var dir         = entry[1];
      var staggerStep = entry[2];
      var dirClass    = dir === 'left' ? 'r-left' : dir === 'right' ? 'r-right' : 'r-up';

      /* Group by parent so stagger resets for each separate group */
      var elements = document.querySelectorAll(selector);
      if (!elements.length) return;

      var groups = {};
      elements.forEach(function (el) {
        var parentKey = el.parentElement ? el.parentElement.dataset.revealGroup || (function () {
          var id = 'rg-' + Math.random().toString(36).slice(2);
          el.parentElement.dataset.revealGroup = id;
          return id;
        })() : '__root__';
        if (!groups[parentKey]) groups[parentKey] = [];
        groups[parentKey].push(el);
      });

      Object.values(groups).forEach(function (groupEls) {
        groupEls.forEach(function (el, i) {
          el.classList.add('scroll-reveal', dirClass);
          if (staggerStep > 0) {
            el.style.transitionDelay = (i * staggerStep) + 's';
          }
        });
      });
    });
  }

  applyRevealClasses();

  /* Create observer */
  if ('IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('scroll-visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px'
    });

    document.querySelectorAll('.scroll-reveal').forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    /* Fallback: show everything immediately if IntersectionObserver unsupported */
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
