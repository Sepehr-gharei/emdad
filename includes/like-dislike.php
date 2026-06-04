<?php

// 1. نمایش HTML دکمه‌های لایک و دیسلایک فقط برای دیدگاه‌ها
function emdadcamera_like_dislike_html($id, $type = 'comment') {
    
    // اگر نوع چیزی غیر از کامنت بود، کلاً متوقف شود
    if ($type !== 'comment') {
        return;
    }

    $likes = (int) get_comment_meta($id, '_cyber_likes_count', true);
    $dislikes = (int) get_comment_meta($id, '_cyber_dislikes_count', true);

    // بررسی وضعیت فعلی کاربر از روی کوکی
    $cookie_key = 'ld_' . $type . '_' . $id;
    $user_action = isset($_COOKIE[$cookie_key]) ? sanitize_text_field($_COOKIE[$cookie_key]) : '';

    $l_active = $user_action === 'like' ? ' active' : '';
    $d_active = $user_action === 'dislike' ? ' active' : '';

    echo '<div class="like-dislike flex-column-reverse flex align-items-center" data-type="'.esc_attr($type).'" data-post-id="'.esc_attr($id).'">'
        . '<div class="action-box dislike-box">'
            . '<button class="dislike-button'.$d_active.'" data-action="dislike" aria-label="Dislike">' . emdadcamera_icon('like-icon') . '</button>'
            . '<span class="count-num num-dislike">'.$dislikes.'</span>'
        . '</div>'
        . '<div class="action-box like-box">'
            . '<span class="count-num num-like">'.$likes.'</span>'
            . '<button class="like-button'.$l_active.'" data-action="like" aria-label="Like">' . emdadcamera_icon('like-icon') . '</button>'
        . '</div>'
    . '</div>';
}

// 2. اضافه کردن ستون به بخش دیدگاه‌ها در پیشخوان
function emdadcamera_add_comment_columns($columns) {
    $columns['cyber_feedback'] = 'بازخوردها';
    return $columns;
}
add_filter('manage_edit-comments_columns', 'emdadcamera_add_comment_columns');

// 3. نمایش مقادیر در ستون دیدگاه‌ها
function emdadcamera_show_comment_columns_content($column, $comment_id) {
    if ($column === 'cyber_feedback') {
        $likes = (int) get_comment_meta($comment_id, '_cyber_likes_count', true);
        $dislikes = (int) get_comment_meta($comment_id, '_cyber_dislikes_count', true);

        echo '<div style="display:flex; flex-direction:column; gap:5px; font-size:12px;">';
        echo '<span style="color:#2cce51; font-weight:bold;">👍 ' . $likes . '</span>';
        echo '<span style="color:#F30919; font-weight:bold;">👎 ' . $dislikes . '</span>';
        echo '</div>';
    }
}
add_action('manage_comments_custom_column', 'emdadcamera_show_comment_columns_content', 10, 2);

// 4. فراخوانی اعداد واقعی برای حل مشکل کش (AJAX) - فقط کامنت
add_action('wp_ajax_emdadcamera_fetch_real_counts', 'emdadcamera_fetch_real_counts');
add_action('wp_ajax_nopriv_emdadcamera_fetch_real_counts', 'emdadcamera_fetch_real_counts');

function emdadcamera_fetch_real_counts() {
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        wp_send_json_error();
    }

    $results = [];

    foreach ($_POST['items'] as $item) {
        $id = intval($item['id']);
        $type = sanitize_text_field($item['type']);
        $unique_key = $type . '_' . $id;

        if ($type === 'comment') {
            $likes = (int) get_comment_meta($id, '_cyber_likes_count', true);
            $dislikes = (int) get_comment_meta($id, '_cyber_dislikes_count', true);
            $results[$unique_key] = [
                'likes' => $likes,
                'dislikes' => $dislikes
            ];
        }
    }

    wp_send_json_success($results);
}

// 5. هندل کردن درخواست ایجکس (ذخیره و محاسبه ریاضی لایک/دیسلایک) - فقط کامنت
add_action('wp_ajax_emdadcamera_like_dislike', 'emdadcamera_like_dislike');
add_action('wp_ajax_nopriv_emdadcamera_like_dislike', 'emdadcamera_like_dislike');

function emdadcamera_like_dislike() {
    if (empty($_POST['id']) || empty($_POST['type']) || empty($_POST['do_action'])) {
        wp_send_json_error(['message' => 'Invalid data']);
    }

    $id = intval($_POST['id']);
    $type = sanitize_text_field($_POST['type']); 
    $action = sanitize_text_field($_POST['do_action']); 
    
    // فقط درخواست‌های کامنت را پردازش کن
    if ($type !== 'comment') {
        wp_send_json_error(['message' => 'Only comments are supported']);
    }

    $cookie_key = 'ld_' . $type . '_' . $id;
    $previous_action = isset($_COOKIE[$cookie_key]) ? sanitize_text_field($_COOKIE[$cookie_key]) : '';

    $likes = (int) get_comment_meta($id, '_cyber_likes_count', true);
    $dislikes = (int) get_comment_meta($id, '_cyber_dislikes_count', true);
    $new_cookie = '';

    if (empty($previous_action)) {
        if ($action === 'like') {
            $likes++;
            $new_cookie = 'like';
        } else {
            $dislikes++;
            $new_cookie = 'dislike';
        }
    } elseif ($previous_action === $action) {
        if ($action === 'like') {
            $likes = max(0, $likes - 1);
        } else {
            $dislikes = max(0, $dislikes - 1);
        }
        $new_cookie = '';
    } else {
        if ($action === 'like') {
            $dislikes = max(0, $dislikes - 1);
            $likes++;
            $new_cookie = 'like';
        } else {
            $likes = max(0, $likes - 1);
            $dislikes++;
            $new_cookie = 'dislike';
        }
    }

    update_comment_meta($id, '_cyber_likes_count', $likes);
    update_comment_meta($id, '_cyber_dislikes_count', $dislikes);

    wp_send_json_success([
        'new_like_count' => $likes,
        'new_dislike_count' => $dislikes,
        'set_cookie' => $new_cookie
    ]);
}