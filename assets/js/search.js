jQuery(document).ready(function ($) {
    const ajaxUrl = typeof ajax_object !== 'undefined' ? ajax_object.ajax_url : '/wp-admin/admin-ajax.php';

    // 1. هندل کردن دسکتاپ
    $('#desktopSearchToggle').on('click', function (e) {
        e.stopPropagation();
        const $container = $('.emdad-search-container.desktop-search');
        $container.toggleClass('active');
        if ($container.hasClass('active')) {
            $container.find('input').focus();
        } else {
            $container.find('.emdad-search-results-dropdown').hide();
        }
    });

    // 2. هندل کردن موبایل (نمایش نوار جستجو زیر هدر بدون پاپ‌آپ)
    $('#mobileSearchToggle').on('click', function (e) {
        e.stopPropagation();
        $('#headerSearchBox').toggleClass('active');
        if ($('#headerSearchBox').hasClass('active')) {
            setTimeout(() => { $('#headerSearchBox input').focus(); }, 100);
        }
    });

    // دکمه بستن جستجوی موبایل
    $('#searchCloseBtn').on('click', function () {
        $('#headerSearchBox').removeClass('active');
    });

    // جلوگیری از بسته شدن هنگام کلیک داخل باکس‌های جستجو
    $('.emdad-search-container, .header-search-box').on('click', function (e) {
        e.stopPropagation();
    });
    
    // بسته شدن منوها با کلیک در فضای خالی سایت
    $(document).on('click', function () {
        $('.emdad-search-container.desktop-search').removeClass('active');
        $('.emdad-search-results-dropdown').hide();
        $('#headerSearchBox').removeClass('active');
    });

    // 3. ریکوئست AJAX برای جستجو
    let searchTimer;
    $('.emdad-live-search-input').on('keyup', function () {
        const $input = $(this);
        const term = $input.val().trim();
        const $wrapper = $input.closest('.desktop-search, .mobile-search-container');
        const $spinner = $wrapper.find('.emdad-search-spinner');
        const $resultsBox = $wrapper.find('.emdad-search-results-dropdown');

        clearTimeout(searchTimer);

        if (term.length < 2) {
            $resultsBox.hide().empty();
            $spinner.hide();
            return;
        }

        $spinner.show();
        $resultsBox.show().html('<div class="emdad-search-loading-text">در حال جستجو...</div>');

        searchTimer = setTimeout(function () {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'emdad_live_search',
                    term: term
                },
                success: function (response) {
                    $spinner.hide();
                    if (response.success) {
                        $resultsBox.html(response.data);
                    } else {
                        $resultsBox.html('<div class="emdad-search-no-result">خطایی رخ داد.</div>');
                    }
                },
                error: function () {
                    $spinner.hide();
                    $resultsBox.html('<div class="emdad-search-no-result">خطا در برقراری ارتباط با سرور.</div>');
                }
            });
        }, 500); 
    });
});