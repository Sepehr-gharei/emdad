<?php
/**
 * emdadcamera ULTIMATE SEO SYSTEM (Query String Version: ?paged=X)
 */

// ۱. هسته محاسبات پجینیشن (بر اساس Query String)
function emdadcamera_get_pagination_info() {
    // دریافت شماره صفحه فعلی فقط از طریق پارامتر paged
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    if ($current_page < 1) $current_page = 1;

    $data = ['is_target' => false, 'type' => '', 'max' => 0, 'current' => $current_page];
    $uri = $_SERVER['REQUEST_URI'];

    // تشخیص نوع صفحه
    if ( is_search() || isset($_GET['s']) ) {
        global $wp_query;
        $data['is_target'] = true;
        $data['type'] = 'Search';
        $data['max'] = $wp_query->max_num_pages;
    } elseif ( is_category() || strpos($uri, '/category/') !== false ) { // << اضافه شد: شرط کتگوری
        global $wp_query;
        $data['is_target'] = true;
        $data['type'] = 'Category';
        // در صفحات آرشیو، مکس پیج در کوئری اصلی موجود است
        $data['max'] = $wp_query->max_num_pages; 
        
    } elseif ( strpos($uri, '/blog') !== false ) {
        $data['is_target'] = true;
        $data['type'] = 'Blog';
        $count = wp_count_posts('post');
        $data['max'] = ceil($count->publish / 9);
    } elseif ( strpos($uri, '/portfolio') !== false ) { // اضافه شدن نمونه کار
        $data['is_target'] = true;
        $data['type'] = 'Portfolio';
        $count = wp_count_posts('portfolio');
        $data['max'] = ceil($count->publish / 12);
    } elseif ( strpos($uri, '/glossary') !== false ) {
        $data['is_target'] = true;
        $data['type'] = 'Glossary';
        $page_id = get_queried_object_id(); // دریافت ID برگه فعلی
        // اگر در صفحه سینگل نیستیم و در آرشیو هستیم
        if($page_id) {
             $meta = get_post_meta($page_id, '_glossary', true);
             $per_page = (is_array($meta) && !empty($meta['per_page'])) ? intval($meta['per_page']) : 9;
        } else {
             $per_page = 9;
        }
        $count = wp_count_posts('glossary');
        $data['max'] = ceil($count->publish / $per_page);
    }
    elseif ( strpos($uri, '/number-list') !== false ) {
        $data['is_target'] = true;
        $data['type'] = 'number-list';
        $page_id = get_queried_object_id(); // دریافت ID برگه فعلی
        // اگر در صفحه سینگل نیستیم و در آرشیو هستیم
        if($page_id) {
             $meta = get_post_meta($page_id, '_number-list', true);
             $per_page = (is_array($meta) && !empty($meta['per_page'])) ? intval($meta['per_page']) : 9;
        } else {
             $per_page = 9;
        }
        $count = wp_count_posts('glossary');
        $data['max'] = ceil($count->publish / $per_page);
    }elseif ( strpos($uri, '/google-update') !== false ) {
        $data['is_target'] = true;
        $data['type'] = 'google-update';
        $page_id = get_queried_object_id(); // دریافت ID برگه فعلی
        // اگر در صفحه سینگل نیستیم و در آرشیو هستیم
        if($page_id) {
             $meta = get_post_meta($page_id, '_google-update', true);
             $per_page = (is_array($meta) && !empty($meta['per_page'])) ? intval($meta['per_page']) : 9;
        } else {
             $per_page = 9;
        }
        $count = wp_count_posts('glossary');
        $data['max'] = ceil($count->publish / $per_page);
    }
    
    return $data;
}

// ۲. غیرفعال کردن کنونیکال Rank Math
add_filter( 'rank_math/frontend/canonical', function( $canonical ) {
    if ( is_404() ) return $canonical;
    $info = emdadcamera_get_pagination_info();
    if ( $info['is_target'] ) {
        return false; // غیرفعال کن تا خودمان دستی بسازیم
    }
    return $canonical;
}, 999999 );

// ۳. چاپ دستی تگ‌های SEO
add_action( 'wp_head', function() {
    if ( is_404() ) return;

    $info = emdadcamera_get_pagination_info();
    if ( ! $info['is_target'] ) return;

    // الف) ساخت آدرس تمیز (بدون پارامترهای اضافی اما با حفظ ساختار اصلی)
    // دریافت آدرس بدون کوئری استرینگ
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $base_url = $protocol . "://$_SERVER[HTTP_HOST]" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // پارامترهای فعلی را می‌گیریم (مثل sort یا s) اما paged را حذف می‌کنیم تا دستی اضافه کنیم
    $query_args = $_GET;
    unset($query_args['paged']); 

    // ساخت آدرس کنونیکال فعلی
    $canonical_url = $base_url;
    if($info['current'] > 1) {
        $query_args['paged'] = $info['current'];
    }
    
    if(!empty($query_args)) {
        $canonical_url = add_query_arg($query_args, $base_url);
    }

    echo "\n\n";
    echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '" />' . "\n";

    // ب) تزریق Prev و Next
    if ( $info['max'] > 1 ) {
        // لینک صفحه قبل
        if ( $info['current'] > 1 ) {
            $prev_args = $_GET;
            $prev_page = $info['current'] - 1;
            if($prev_page > 1) {
                $prev_args['paged'] = $prev_page;
            } else {
                unset($prev_args['paged']); // صفحه ۱ نباید ?paged=1 داشته باشد
            }
            $prev_link = add_query_arg($prev_args, $base_url);
            echo '<link rel="prev" href="' . esc_url( $prev_link ) . '" />' . "\n";
        }

        // لینک صفحه بعد
        if ( $info['current'] < $info['max'] ) {
            $next_args = $_GET;
            $next_args['paged'] = $info['current'] + 1;
            $next_link = add_query_arg($next_args, $base_url);
            echo '<link rel="next" href="' . esc_url( $next_link ) . '" />' . "\n";
        }
    }
    echo "\n\n";
}, 1 );

// ۴. هندل کردن ۴۰۴ صفحات خالی (بر اساس ?paged=)
add_action( 'template_redirect', function() {
    // فقط اگر پارامتر paged در آدرس بود چک کن
    if ( !isset($_GET['paged']) ) return;

    $info = emdadcamera_get_pagination_info();
    
    // اگر شماره صفحه درخواستی از ماکزیمم صفحات بیشتر بود -> 404
    if ( $info['is_target'] && $info['max'] > 0 && $info['current'] > $info['max'] ) {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        nocache_headers();
        include( get_query_template( '404' ) );
        exit;
    }
} );