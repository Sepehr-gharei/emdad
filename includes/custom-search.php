<?php 
add_action('wp_ajax_emdad_live_search', 'emdad_live_search_handler');
add_action('wp_ajax_nopriv_emdad_live_search', 'emdad_live_search_handler');

function emdad_live_search_handler() {
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    if (empty($term) || mb_strlen($term) < 2) {
        wp_send_json_error();
    }

    // کش کردن نتایج برای سرعت بیشتر
    $cache_key = 'emdad_search_' . md5($term);
    $cached_results = get_transient($cache_key);

    if (false !== $cached_results) {
        wp_send_json_success($cached_results);
    }

    $args = array(
        's'              => $term,
        'post_type'      => array('product', 'post'),
        'post_status'    => 'publish',
        'posts_per_page' => 8,
    );

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<div class="emdad-ajax-scroll-wrapper">';
        while ($query->have_posts()) {
            $query->the_post();
            global $post;
            
            $post_type = get_post_type();
            $title     = get_the_title();
            $permalink = get_permalink();
            $thumbnail = get_the_post_thumbnail_url($post->ID, 'thumbnail');
            
            $img_html  = $thumbnail ? '<img src="' . esc_url($thumbnail) . '" class="emdad-search-img" alt="' . esc_attr($title) . '">' : '<div class="emdad-search-no-img">'. emdadcamera_icon('search-icon') .'</div>';
            
            $price_html = '';
            $badge_html = '';

            if ($post_type === 'product' && function_exists('wc_get_product')) {
                $product = wc_get_product($post->ID);
                $price_html = '<div class="emdad-search-price">' . $product->get_price_html() . '</div>';
                $badge_html = '<span class="emdad-search-badge product-badge">محصول</span>';
            } else {
                $badge_html = '<span class="emdad-search-badge post-badge">مقاله</span>';
            }
            ?>
            <a href="<?php echo esc_url($permalink); ?>" class="emdad-search-result-item">
                <?php echo $img_html; ?>
                <div class="emdad-search-result-info">
                    <span class="emdad-search-result-title"><?php echo esc_html($title); ?></span>
                    <div class="emdad-search-result-meta">
                        <?php echo $badge_html; ?>
                        <?php echo $price_html; ?>
                    </div>
                </div>
            </a>
            <?php
        }
        echo '</div>';
    } else {
        echo '<div class="emdad-search-no-result">متأسفانه نتیجه‌ای یافت نشد.</div>';
    }

    wp_reset_postdata();
    $final_html = ob_get_clean();
    set_transient($cache_key, $final_html, HOUR_IN_SECONDS);

    wp_send_json_success($final_html);
}