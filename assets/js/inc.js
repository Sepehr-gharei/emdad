jQuery(document).ready(function ($) {
    // ==========================================
    // 1. SLIDERS (Swiper)
    // ==========================================
    function initSliders() {
      if (typeof Swiper === "undefined") {
        console.error("Swiper library is not loaded");
        return;
      }
  
      $(".slider").each(function () {
        var $slider = $(this);
        var setting = $slider.attr("data-settings");
        var id = $slider.attr("id");
  
        if (!setting || !id) {
          return;
        }
  
        try {
          var items = JSON.parse(setting);
        } catch (e) {
          return;
        }
  
        var autoplaySetting =
          items.autoplay === "false"
            ? false
            : {
                delay: 160000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
              };
  
        try {
          const swiper = new Swiper("#" + id, {
            slidesPerView: items.columns || 1,
            navigation: {
              nextEl: ".button-next-" + id,
              prevEl: ".button-prev-" + id,
            },
            autoplay: autoplaySetting,
            loop: items.infinite || false,
            centeredSlides: items.centerMode || false,
            spaceBetween: parseInt(items.space) || 0,
            pagination: {
              el: "#" + id + " .swiper-pagination",
              clickable: true,
              dynamicBullets: true,
            },
            breakpoints: {
              0: { slidesPerView: Number(items.columns_small_mobile) || 1.5 },
              480: { slidesPerView: Number(items.columns_mobile) || 2 },
              768: { slidesPerView: Number(items.columns_mobile_tablet) || 3 },
              1024: { slidesPerView: Number(items.columns_tablet) || 4 },
              1280: { slidesPerView: Number(items.columns) || 5 },
            },
          });
        } catch (e) {
          console.error("Error initializing Swiper:", e, id);
        }
      });
    }
  
    if (typeof Swiper !== "undefined") {
      initSliders();
    } else {
      $(window).on("load", function () {
        setTimeout(initSliders, 100);
      });
    }
  
    // ==========================================
    // 2. OFF-CANVAS MENU (simplified)
    // ==========================================
    (function () {
      var $wrapper = $("#offcanvas-navbar");
      var $overlay = $("#overlay");
      var $body = $("body");
      var $hamburgerBtns = $(".ws-menu");
      var scrollTop = 0;
  
      function disableScroll() {
        scrollTop = $(window).scrollTop();
        $body.css({
          overflow: "hidden",
          position: "fixed",
          top: -scrollTop,
          width: "100%",
        });
      }
  
      function enableScroll() {
        $body.css({
          overflow: "",
          position: "",
          top: "",
        });
        $(window).scrollTop(scrollTop);
      }
  
      function openMenu() {
        if ($wrapper.hasClass("active")) return;
        $wrapper.addClass("active");
        $overlay.addClass("visible");
        disableScroll();
      }
  
      function closeMenu() {
        if (!$wrapper.hasClass("active")) return;
        $wrapper.removeClass("active");
        $overlay.removeClass("visible");
        enableScroll();
      }
  
      $hamburgerBtns.on("click", function (e) {
        e.stopPropagation();
        if ($wrapper.hasClass("active")) closeMenu();
        else openMenu();
      });
  
      $overlay.on("click", closeMenu);
      $wrapper.on("click", function (e) {
        e.stopPropagation();
      });
      $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $wrapper.hasClass("active")) closeMenu();
      });
  
      // ========== زیرمنوی داخلی آکاردئونی ==========
      $("#offcanvas-navbar").on("click", ".nav-link", function (e) {
        var $this = $(this);
        var $parent = $this.parent(); // li.nav-item
        var $submenu = $parent.find(".nav-submenu");
  
        if ($submenu.length === 0) return;
  
        e.preventDefault();
        e.stopPropagation();
  
        if ($parent.hasClass("open")) {
          $parent.removeClass("open");
        } else {
          $("#offcanvas-navbar .nav-item.open").not($parent).removeClass("open");
          $parent.addClass("open");
        }
      });
  
      $(
        ".submenu-link, .flat--menu .nav-link, .submenu-item-title, .offcanvas__logo, .hamburger-btns button, .mobile-social-icons a"
      ).on("click", function (e) {
        var $link = $(this);
        if ($link.attr("href") === "#" || !$link.attr("href")) {
          e.preventDefault();
        }
      });
    })();
  
    // ==========================================
    // 3. UNDERLINE NAVIGATION EFFECT
    // ==========================================
    (function() {
      var $navField = $(".nav-wrapper .nav-field");
      var $navItems = $navField.find(".item");
      
      var $underline = $('<div class="underline-nav"></div>');
      $navField.css("position", "relative").append($underline);
      
      $navItems.on("mouseenter", function() {
        var $p = $(this).find("p"); 
        var offset = $p.offset().left - $navField.offset().left;
        var width = $p.outerWidth();
        
        $underline.css({
          width: width,
          right: "auto",
          left: offset,
          visibility: "visible"
        });
      });
      
      $navField.on("mouseleave", function() {
        $underline.css("width", 0);
      });
      
      $underline.on("transitionend", function() {
        if (!$navField.find(".item:hover").length) {
          $underline.css("visibility", "hidden");
        }
      });
    })();
  
    // ==========================================
    // 4. MODERN TIMELINE
    // ==========================================
    (function() {
      const $items = $('.timeline-item');
      const $dot = $('.timeline-dot-mover');
      const $line = $('.timeline-line');
      const $section = $('.timeline-section');
      
      if (!$items.length) return;
      
      function moveDot() {
        const scrollTop = $(window).scrollTop();
        const sectionTop = $section.offset().top;
        const sectionH = $section.outerHeight();
        const lineTop = $line.offset().top;
        const lineH = $line.outerHeight();
        
        let progress = (scrollTop - (sectionTop - window.innerHeight * 0.2)) / (sectionH - window.innerHeight * 0.3);
        progress = Math.min(Math.max(progress, 0), 1);
        
        const dotTop = lineTop + (lineH - 24) * progress;
        $dot.css('top', dotTop - lineTop + 'px');
        
        const dotCenter = dotTop + 12;
        $items.each(function() {
          const $item = $(this);
          const itemTop = $item.offset().top;
          const active = dotCenter >= itemTop - 50 && dotCenter <= itemTop + $item.outerHeight() + 50;
          $item.toggleClass('active', active);
        });
      }
      
      const observer = new IntersectionObserver(entries => {
        entries.forEach(e => e.isIntersecting && $(e.target).addClass('visible') && observer.unobserve(e.target));
      }, { threshold: 0.2 });
      
      $items.each((i, el) => observer.observe(el));
      
      let ticking = false;
      $(window).on('scroll', () => {
        if (!ticking) { requestAnimationFrame(() => { moveDot(); ticking = false; }); ticking = true; }
      });
      
      let resizeTimer;
      $(window).on('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(moveDot, 100); });
      
      setTimeout(moveDot, 100);
    })();

    // ==========================================
    // 5. ARCHIVE VIEW & ANIMATION
    // ==========================================
    (function() {
      var $viewBtns = $('.view-btn');
      var $grid = $('#archive-products-grid');

      // تغییر نما (گرید / لیست)
      $viewBtns.on('click', function () {
        $viewBtns.removeClass('active');
        $(this).addClass('active');

        var view = $(this).data('view');
        if (view === 'list') {
          $grid.addClass('list-view');
        } else {
          $grid.removeClass('list-view');
        }

        // ذخیره در localStorage
        localStorage.setItem('archive-view', view);
      });

      // بازیابی نما از localStorage
      var savedView = localStorage.getItem('archive-view');
      if (savedView === 'list' && $grid.length) {
        $grid.addClass('list-view');
        $('[data-view="list"]').addClass('active');
        $('[data-view="grid"]').removeClass('active');
      }

      // انیمیشن ورود کارت‌ها
      var $cards = $('.archive-product-card');
      if ($cards.length) {
        // برای مانیتور کردن عناصر بهتر است همچنان از IntersectionObserver نیتیو استفاده شود
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry, i) {
            if (entry.isIntersecting) {
              setTimeout(function () {
                $(entry.target).css({
                  'opacity': '1',
                  'transform': 'translateY(0)'
                });
              }, i * 60);
              observer.unobserve(entry.target);
            }
          });
        }, { threshold: 0.1 });

        $cards.each(function () {
          $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)',
            'transition': 'opacity 0.5s ease, transform 0.5s ease, box-shadow 0.4s ease, border-color 0.3s ease'
          });
          observer.observe(this);
        });
      }
    })();
  
    // ==========================================
    // 6. OTHER INITIALIZATIONS (Fancybox, Menu, AJAX Forms)
    // ==========================================
    // Fancybox initialization
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind('[data-fancybox="gallery"]', {});
    }
  
    // Mobile submenu toggle
    if ($(window).width() < 990) {
      $(".menu-item-has-children > a").on("click", function (e) {
        e.preventDefault();
        $(this).next(".sub-menu").slideToggle();
      });
    }
  
    // --- AJAX Forms ---
    $(document).on('submit', '.emdad-ajax-form', function (e) {
      e.preventDefault();
      
      let form = $(this);
      let btn = form.find('button[type="submit"]');
      let btnTextSpan = btn.find('.btn__text');
      let originalText = btnTextSpan.length ? btnTextSpan.text() : btn.text();
      let resDiv = form.find('.emdad-form-response');
    
      if(btnTextSpan.length) btnTextSpan.text('در حال ارسال...');
      else btn.text('در حال ارسال...');
      btn.prop('disabled', true);
      resDiv.hide();
    
      let data = form.serializeArray();
      data.push({name: 'action', value: 'emdad_submit'});
      data.push({name: 'form_id', value: form.data('form')});
    
      $.post(ajax_object.ajax_url, data, function(response) {
          if(btnTextSpan.length) btnTextSpan.text(originalText);
          else btn.text(originalText);
          btn.prop('disabled', false);
    
          if(response.success) {
              resDiv.css({'background':'#d1e7dd', 'color':'#0f5132', 'border':'1px solid #badbcc'}).html(response.data.message).slideDown();
              form[0].reset();
          } else {
              resDiv.css({'background':'#f8d7da', 'color':'#842029', 'border':'1px solid #f5c2c7'}).html('خطا: ' + response.data.message).slideDown();
          }
      }).fail(function() {
          if(btnTextSpan.length) btnTextSpan.text(originalText);
          else btn.text(originalText);
          btn.prop('disabled', false);
          resDiv.css({'background':'#f8d7da', 'color':'#842029'}).html('خطا در برقراری ارتباط با سرور.').slideDown();
      });
    });
// ==========================================
// FLOATING CART
// ==========================================
(function ($) {
    var $fc    = $('#floatingCart');
    var $badge = $('#fcBadge');

    if (!$fc.length) return;

    function setCount(n) {
        n = parseInt(n) || 0;
        $badge.text(n > 0 ? n : '');
        $badge.attr('data-visible', n > 0 ? '1' : '0');
    }

    function pulse() {
        $fc.removeClass('is-pulse');
        void $fc[0].offsetWidth;
        $fc.addClass('is-pulse');
        setTimeout(function () { $fc.removeClass('is-pulse'); }, 800);
    }

    function countFrom(fragments) {
        var n = 0;
        if (!fragments) return n;
        $.each(fragments, function (_, html) {
            var $el  = $(html);
            var text = $el.find('.count').text()
                    || $el.attr('data-cart-quantity') || '';
            var num  = parseInt(text);
            if (!isNaN(num) && num > 0) { n = num; return false; }
        });
        return n;
    }

    $(document.body).on('wc_fragments_refreshed', function (e, data) {
        var n = countFrom(data && data.fragments);
        if (n >= 0) setCount(n);
    });

    $(document.body).on('added_to_cart', function (e, fragments) {
        var n = countFrom(fragments);
        if (n > 0) { setCount(n); pulse(); return; }
        $.get('/?wc-ajax=get_refreshed_fragments', function (res) {
            var count = countFrom(res && res.fragments);
            if (!count) count = (parseInt($badge.text()) || 0) + 1;
            setCount(count);
            pulse();
        }).fail(function () {
            setCount((parseInt($badge.text()) || 0) + 1);
            pulse();
        });
    });

    setCount(parseInt($badge.text()) || 0);
})(jQuery);

// ==========================================
// SCROLL TO TOP BUTTON
// ==========================================
(function () {
    var btn   = document.getElementById('scrollTopBtn');
    var bar   = document.querySelector('.st-svg__bar');
    var FC_C  = parseFloat((2 * Math.PI * 27).toFixed(4));

    if (!btn || !bar) return;

    bar.style.strokeDasharray  = FC_C;
    bar.style.strokeDashoffset = FC_C;

    window.addEventListener('scroll', function () {
        var scrolled  = window.pageYOffset || document.documentElement.scrollTop;
        var maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        var ratio     = maxScroll > 0 ? Math.min(scrolled / maxScroll, 1) : 0;

        bar.style.strokeDashoffset = FC_C * (1 - ratio);

        if (scrolled > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    }, { passive: true });

    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
});
