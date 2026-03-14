/**
 * Illusia Character Gallery — Carousel + Lightbox
 *
 * Vanilla JS for:
 *  - Auto-sliding carousel with random start, prev/next, dots
 *  - Full-screen lightbox with keyboard navigation
 *  - TCG card flip (front/back 3D animation)
 *
 * @package Illusia Theme
 * @since 1.12.2
 */

(function () {
  'use strict';

  // =========================================================================
  // CAROUSEL
  // =========================================================================

  function initCarousels() {
    document.querySelectorAll('.illusia-char-carousel').forEach(initCarousel);
  }

  function initCarousel(el) {
    var track  = el.querySelector('.illusia-char-carousel__track');
    var slides = el.querySelectorAll('.illusia-char-carousel__slide');
    var dots   = el.querySelectorAll('.illusia-char-carousel__dot');
    var prevBtn = el.querySelector('.illusia-char-carousel__btn--prev');
    var nextBtn = el.querySelector('.illusia-char-carousel__btn--next');

    if (!track || slides.length === 0) return;

    var total = slides.length;

    if (total === 1) {
      el.classList.add('illusia-char-carousel--single');
    }

    // Random start slide
    var current = Math.floor(Math.random() * total);
    var autoplayMs = parseInt(el.dataset.autoplay, 10) || 5000;
    var timer = null;

    function goTo(index) {
      current = ((index % total) + total) % total;
      track.style.transform = 'translateX(-' + (current * 100) + '%)';
      dots.forEach(function (d, i) {
        d.classList.toggle('illusia-char-carousel__dot--active', i === current);
      });
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAutoplay() {
      stopAutoplay();
      if (total > 1) {
        timer = setInterval(next, autoplayMs);
      }
    }

    function stopAutoplay() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    // Init position
    goTo(current);

    // Buttons
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); startAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); startAutoplay(); });

    // Dots
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        goTo(parseInt(dot.dataset.index, 10));
        startAutoplay();
      });
    });

    // Click slide → open lightbox
    slides.forEach(function (slide) {
      var img = slide.querySelector('img');
      if (img) {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function () {
          var urls = [];
          slides.forEach(function (s) {
            var i = s.querySelector('img');
            if (i) urls.push(i.dataset.full || i.src);
          });
          openLightbox(urls, current);
        });
      }
    });

    // Pause on hover
    el.addEventListener('mouseenter', stopAutoplay);
    el.addEventListener('mouseleave', startAutoplay);

    startAutoplay();
  }

  // =========================================================================
  // LIGHTBOX
  // =========================================================================

  var lightbox = null;
  var lbImg = null;
  var lbCounter = null;
  var lbImages = [];
  var lbIndex = 0;

  function initLightbox() {
    lightbox = document.getElementById('illusia-char-lightbox');
    if (!lightbox) return;

    lbImg = lightbox.querySelector('.illusia-char-lightbox__img');
    lbCounter = lightbox.querySelector('.illusia-char-lightbox__counter');

    var closeBtn = lightbox.querySelector('.illusia-char-lightbox__close');
    var prevBtn  = lightbox.querySelector('.illusia-char-lightbox__nav--prev');
    var nextBtn  = lightbox.querySelector('.illusia-char-lightbox__nav--next');

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (prevBtn)  prevBtn.addEventListener('click', function () { lbGo(lbIndex - 1); });
    if (nextBtn)  nextBtn.addEventListener('click', function () { lbGo(lbIndex + 1); });

    // Click backdrop to close
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLightbox();
    });

    // Keyboard
    document.addEventListener('keydown', function (e) {
      if (!lightbox.classList.contains('illusia-char-lightbox--open')) return;

      if (e.key === 'Escape') closeLightbox();
      else if (e.key === 'ArrowLeft') lbGo(lbIndex - 1);
      else if (e.key === 'ArrowRight') lbGo(lbIndex + 1);
    });

    // Single portrait images (no carousel) should also open lightbox
    document.querySelectorAll('.illusia-char-lightbox-trigger').forEach(function (img) {
      img.style.cursor = 'pointer';
      img.addEventListener('click', function () {
        openLightbox([img.dataset.full || img.src], 0);
      });
    });

    // Gallery grid items open lightbox too
    document.querySelectorAll('.illusia-char-sheet__gallery-item').forEach(function (item, idx) {
      item.addEventListener('click', function (e) {
        e.preventDefault();
        var urls = [];
        document.querySelectorAll('.illusia-char-sheet__gallery-item img').forEach(function (i) {
          urls.push(i.dataset.full || i.src);
        });
        openLightbox(urls, idx);
      });
    });
  }

  function openLightbox(images, startIndex) {
    if (!lightbox || !lbImg || images.length === 0) return;

    lbImages = images;
    lbIndex = startIndex || 0;

    lightbox.classList.toggle('illusia-char-lightbox--single', images.length <= 1);
    lbGo(lbIndex);
    lightbox.classList.add('illusia-char-lightbox--open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('illusia-char-lightbox--open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function lbGo(index) {
    var total = lbImages.length;
    lbIndex = ((index % total) + total) % total;
    lbImg.src = lbImages[lbIndex];

    if (lbCounter && total > 1) {
      lbCounter.textContent = (lbIndex + 1) + ' / ' + total;
    }
  }

  // =========================================================================
  // TCG FLIP
  // =========================================================================

  function initTcgFlip() {
    document.querySelectorAll('.illusia-char-sheet__flip-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var sheet = btn.closest('.illusia-char-sheet');
        if (sheet) {
          sheet.classList.toggle('illusia-char-sheet--flipped');
        }
      });
    });
  }

  // =========================================================================
  // INIT
  // =========================================================================

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initCarousels();
      initLightbox();
      initTcgFlip();
    });
  } else {
    initCarousels();
    initLightbox();
    initTcgFlip();
  }
})();
