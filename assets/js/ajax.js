/**
 * emdadcamera — blog filter
 * مسیر: /assets/js/ajax.js
 */
jQuery(function ($) {
    'use strict';

    var $wrap    = $('#blog-posts-wrap');
    var $filters = $('.archive-filters .filter-btn');
    var $spinner = $('#blog-spinner');

    if (!$wrap.length) return;

    var currentCat   = $wrap.data('cat') || '';
    var currentCatId = parseInt($wrap.data('cat-id')) || 0;
    var currentPaged = parseInt($wrap.data('paged')) || 1;
    var busy         = false;

    // متغیر کش برای ذخیره نتایج و لود آنی
    var responseCache = {};

    function showLoading() {
        $wrap.css({ opacity: 0.4, pointerEvents: 'none', position: 'relative' });
        $spinner.css({
            position : 'absolute',
            top      : ($wrap.outerHeight() / 2 - 20) + 'px',
            left     : '50%',
            transform: 'translateX(-50%)',
        }).show();
        $('html,body').scrollTop($wrap.offset().top - 100);
    }

    function hideLoading() {
        $spinner.hide();
        $wrap.css({ opacity: 1, pointerEvents: '', position: '' });
    }

    // تابع رندر برای جلوگیری از تکرار کد
    function renderView(html, cat, catId, paged, push) {
        $wrap.html(html);
        currentCat   = cat;
        currentCatId = catId;
        currentPaged = paged;
        $wrap.data('cat', cat);
        $wrap.data('cat-id', catId);
        $wrap.data('paged', paged);

        if (push !== false) {
            var p = new URLSearchParams();
            if (cat)       p.set('cat',   cat);
            if (paged > 1) p.set('paged', paged);
            var qs = p.toString();
            history.pushState(
                { cat: cat, catId: catId, paged: paged },
                '',
                location.pathname + (qs ? '?' + qs : '')
            );
        }

        $filters.removeClass('active')
                .filter('[data-cat-id="' + catId + '"]')
                .addClass('active');
    }

    function fetch(cat, catId, paged, push) {
        if (busy) return;

        var cacheKey = catId + '_' + paged;

        // اگر نتیجه در کش مرورگر بود، همان را آنی نشان بده
        if (responseCache[cacheKey]) {
            renderView(responseCache[cacheKey], cat, catId, paged, push);
            return;
        }

        busy = true;
        showLoading();

        $.post(
            ajax_object.ajax_url,
            {
                action : 'blog_filter',
                nonce  : ajax_object.nonce,
                cat    : cat,
                cat_id : catId, // ارسال مستقیم ID برای سرعت بخشیدن به سمت سرور
                paged  : paged,
            },
            function (res) {
                if (res && res.success) {
                    responseCache[cacheKey] = res.data.html; // ذخیره نتیجه برای کلیک‌های بعدی
                    renderView(res.data.html, cat, catId, paged, push);
                } else {
                    $wrap.html('<p class="no-posts-found">خطا در بارگذاری.</p>');
                }
            },
            'json'
        ).always(function () {
            hideLoading();
            busy = false;
        });
    }

    $filters.on('click', function () {
        var catId = parseInt($(this).data('cat-id')) || 0;
        var cat   = $(this).data('cat') || '';
        if (catId === currentCatId) return;
        fetch(cat, catId, 1, true);
    });

    $wrap.on('click', '.blog-pagination .page-numbers', function (e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.hasClass('current') || $btn.hasClass('dots')) return;
        var href = $btn.attr('href') || '';
        var m    = href.match(/paged=(\d+)/) || href.match(/\/page\/(\d+)/);
        fetch(currentCat, currentCatId, m ? parseInt(m[1]) : 1, true);
    });

    window.addEventListener('popstate', function (e) {
        if (e.state !== null) {
            fetch(e.state.cat || '', e.state.catId || 0, e.state.paged || 1, false);
        } else {
            location.reload();
        }
    });
});