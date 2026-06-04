<?php

function emdadcamera_set_post_views($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);

    if ($count == '') {
        $count = 0;
        add_post_meta($postID, $count_key, 0);
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function emdadcamera_track_post_views() {
    if ( !is_single() ) return;
    global $post;
    if ( empty($post->ID) ) return;

    $post_id = $post->ID;
    $cookie_name = 'emdadcamera_viewed';
    $lifetime = 2 * HOUR_IN_SECONDS;

    $seen = [];
    if ( isset($_COOKIE[$cookie_name]) ) {
        $seen = json_decode( stripslashes($_COOKIE[$cookie_name]), true );
        if ( !is_array($seen) ) $seen = [];
    }

    if ( !in_array($post_id, $seen) ) {
        emdadcamera_set_post_views($post_id);
        $seen[] = $post_id;
        setcookie($cookie_name, wp_json_encode($seen), time() + $lifetime, '/');
        $_COOKIE[$cookie_name] = wp_json_encode($seen);
    }
}
add_action('wp_head', 'emdadcamera_track_post_views', 1);


function emdadcamera_get_post_views($postID = null) {
    if ( empty($postID) ) $postID = get_the_ID();
    $count = get_post_meta($postID, 'post_views_count', true);
    if ($count == '') $count = 0;
    return number_format_i18n($count);
}


function emdadcamera_add_views_meta_box() {
    add_meta_box(
        'emdadcamera_views_box',
        'تعداد بازدید',
        'emdadcamera_render_views_meta_box',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'emdadcamera_add_views_meta_box');

function emdadcamera_render_views_meta_box($post) {
    wp_nonce_field('emdadcamera_save_views', 'emdadcamera_views_nonce');
    $count = get_post_meta($post->ID, 'post_views_count', true);
    if ($count == '') $count = 0;
    ?>
    <p>
        <label for="emdadcamera_views_field">تعداد بازدید:</label>
        <input type="number" name="emdadcamera_views_field" id="emdadcamera_views_field"
               value="<?php echo esc_attr($count); ?>" min="0" step="1"
               style="width:100%; text-align:center; font-weight:bold;">
    </p>
    <p style="font-size:12px;color:#777;">می‌توانید مقدار بازدید را به‌صورت دستی تغییر دهید.</p>
    <?php
}

function emdadcamera_save_views_meta_box($post_id) {
    if ( !isset($_POST['emdadcamera_views_nonce']) || !wp_verify_nonce($_POST['emdadcamera_views_nonce'], 'emdadcamera_save_views') )
        return;

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( !current_user_can('edit_post', $post_id) ) return;

    if ( isset($_POST['emdadcamera_views_field']) ) {
        $count = intval($_POST['emdadcamera_views_field']);
        update_post_meta($post_id, 'post_views_count', $count);
    }
}
add_action('save_post', 'emdadcamera_save_views_meta_box');


function emdadcamera_add_views_column($columns) {
    $columns['emdadcamera_views'] = 'بازدید';
    return $columns;
}
add_filter('manage_posts_columns', 'emdadcamera_add_views_column');

function emdadcamera_render_views_column($column, $post_id) {
    if ($column === 'emdadcamera_views') {
        $count = get_post_meta($post_id, 'post_views_count', true);
        echo '<strong>' . number_format_i18n((int)$count) . '</strong>';
    }
}
add_action('manage_posts_custom_column', 'emdadcamera_render_views_column', 10, 2);

function emdadcamera_make_views_column_sortable($columns) {
    $columns['emdadcamera_views'] = 'post_views_count';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'emdadcamera_make_views_column_sortable');

function emdadcamera_orderby_views_column($query) {
    if ( !is_admin() ) return;
    $orderby = $query->get('orderby');
    if ( 'post_views_count' === $orderby ) {
        $query->set('meta_key', 'post_views_count');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'emdadcamera_orderby_views_column');

/**
 * افزایش و ذخیره تعداد اشتراک‌گذاری از طریق AJAX
 */
add_action('wp_ajax_emdadcamera_share_count', 'emdadcamera_increment_share_count');
add_action('wp_ajax_nopriv_emdadcamera_share_count', 'emdadcamera_increment_share_count');

function emdadcamera_increment_share_count() {
    // بررسی وجود داده‌های لازم
    if ( !isset($_POST['post_id']) || !isset($_POST['platform']) ) {
        wp_send_json_error('اطلاعات ناقص است.');
    }

    $post_id  = intval($_POST['post_id']);
    $platform = sanitize_text_field($_POST['platform']);

    // پلتفرم‌های مجاز
    if ($post_id > 0 && in_array($platform, ['telegram', 'whatsapp'])) {
        $meta_key = 'share_count_' . $platform;
        
        // دریافت مقدار فعلی
        $current_count = (int) get_post_meta($post_id, $meta_key, true);
        $new_count = $current_count + 1;
        
        // به‌روزرسانی در دیتابیس
        $updated = update_post_meta($post_id, $meta_key, $new_count);

        if ($updated !== false || $current_count === 0) {
            wp_send_json_success(['count' => $new_count]);
        } else {
            wp_send_json_error('خطا در به‌روزرسانی دیتابیس.');
        }
    }

    wp_send_json_error('پلتفرم نامعتبر است.');
}