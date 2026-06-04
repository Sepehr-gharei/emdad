<?php

function emdadcamera_Get_Setting($section, $id) {
	$theme_options = get_option('emdadcamera_main_option', [] );
	$section = $theme_options[$section];
	$id = !empty($section[$id]) ? $section[$id] : '';
	return $id;
}

function emdadcamera_breadcrumbs() {
	if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs();
}


function emdadcamera_optimized_search_filter($query) {
    // فقط در سمت کاربر (نه ادمین) + کوئری اصلی + صفحه جستجو اجرا شود
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        
        // 1. محدود کردن جستجو فقط به پست‌ها و برگه‌ها (حذف محصولات و...)
        $query->set('post_type', array('post', 'page'));

        // 2. کوئری متا برای فیلتر کردن قالب‌های اختصاصی
        // این شرط هم پست‌ها را شامل می‌شود (چون قالب ندارند) و هم برگه‌های پیش‌فرض را
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key'     => '_wp_page_template',
                'compare' => 'NOT EXISTS', // اگر اصلا قالبی ست نشده بود (پست‌ها و برگه‌های ساده)
            ),
            array(
                'key'     => '_wp_page_template',
                'value'   => 'default', // اگر صراحتا روی default بود
            )
        );

        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'emdadcamera_optimized_search_filter');

function emdadcamera_search_posts_per_page($query) {
    if ( $query->is_search() && $query->is_main_query() && !is_admin() ) {
        $query->set( 'posts_per_page', 30 );
    }
}
add_action( 'pre_get_posts', 'emdadcamera_search_posts_per_page' );


// ==================== امنیت لاگین: غیرفعال‌سازی wp-login.php ====================

/**
 * ریدایرکت wp-login.php به صفحه لاگین پرسنل
 * - GET: نمایش فرم لاگین پرسنل
 * - POST (لاگین): بعد از ارسال فرم وردپرس، ریدایرکت
 * - action=logout: بعد از خروج، برگشت به صفحه پرسنل
 */
add_action('login_init', function () {
    $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'login';

    // بعد از logout → به صفحه لاگین پرسنل بفرست
    if ($action === 'loggedout') {
        wp_safe_redirect(home_url('/staff-login/'));
        exit;
    }

    // POST (ارسال فرم وردپرس پیش‌فرض) → اجازه بده پردازش بشه (برای جلوگیری از شکست AJAX)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }

    // هر GET روی wp-login.php → به صفحه پرسنل ریدایرکت کن
    wp_safe_redirect(home_url('/staff-login/'));
    exit;
});

/**
 * بعد از logout موفق، به صفحه لاگین پرسنل بفرست
 */
add_filter('logout_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    return home_url('/staff-login/');
}, 10, 3);

/**
 * اگر کسی مستقیم آدرس wp-login.php تایپ کنه (قبل از init)
 * این hook زودتر اجرا می‌شه
 */
add_action('init', function () {
    if (
        isset($_SERVER['REQUEST_URI']) &&
        strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false &&
        $_SERVER['REQUEST_METHOD'] === 'GET' &&
        !isset($_GET['action'])
    ) {
        wp_safe_redirect(home_url('/staff-login/'));
        exit;
    }
});

/**
 * لینک login را در همه جای وردپرس به صفحه پرسنل تغییر بده
 */
add_filter('login_url', function ($login_url, $redirect, $force_reauth) {
    return home_url('/staff-login/');
}, 10, 3);

/**
 * لینک logout را درست نگه دار (مشکل خروج از ادمین)
 * وردپرس بعد از logout به login_url ریدایرکت می‌کنه — این فیلتر مطمئن می‌شه
 * که logout URL همچنان کار می‌کنه و بعدش به صفحه پرسنل می‌ره
 */
add_filter('logout_url', function ($logout_url, $redirect) {
    // اگر redirect خاصی تعریف نشده، بعد از logout به staff-login بفرست
    if (empty($redirect)) {
        $logout_url = add_query_arg('redirect_to', urlencode(home_url('/staff-login/')), $logout_url);
    }
    return $logout_url;
}, 10, 2);

/**
 * بعد از logout در wp-admin، به صفحه لاگین پرسنل بفرست
 */
add_action('wp_logout', function () {
    if (!headers_sent()) {
        wp_safe_redirect(home_url('/staff-login/'));
        exit;
    }
});

// اضافه کردن پشتیبانی از فایل APK در وردپرس
function add_apk_mime_type( $mimes ) {
    $mimes['apk'] = 'application/vnd.android.package-archive';
    return $mimes;
}
add_filter( 'upload_mimes', 'add_apk_mime_type' );

// بررسی نوع فایل واقعی برای APK
function fix_apk_upload_check( $data, $file, $filename, $mimes ) {
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
    if ( $ext === 'apk' ) {
        $data['ext']  = 'apk';
        $data['type'] = 'application/vnd.android.package-archive';
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'fix_apk_upload_check', 10, 4 );