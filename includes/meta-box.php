<?php

// Add Meta Box
function add_page_meta_box() {
    add_meta_box(
        'faq',
        'پرسش پاسخ',
        'render_faq_fields',
        ['page', 'post'],
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'add_page_meta_box');


function render_faq_fields($post) {
    wp_nonce_field('faq_fields_nonce', 'faq_fields_nonce');
    $faq_fields = get_post_meta($post->ID, '_faq', true);
    echo '<div class="emdadcamera-accordion-wrap">';
    if ($faq_fields) {
        foreach ($faq_fields as $field) {
            echo '<p><strong>عنوان:</strong> <input type="text" name="faq_fields[q][]" value="' . esc_attr($field['q']) . '" /> ';
            echo '<strong>متن:</strong> <textarea name="faq_fields[a][]" rows="4" cols="50">' . esc_textarea($field['a']) . '</textarea> ';
            echo '<span class="remove_field button">حذف</span></p>';
        }
    } else {
        echo '<p><strong>عنوان:</strong> <input type="text" name="faq_fields[q][]" value="" /> ';
        echo '<strong>متن:</strong> <textarea name="faq_fields[a][]" rows="4" cols="50"></textarea> ';
        echo '<span class="remove_field button">حذف</span></p>';
    }

    echo '<span class="add_field button">افزودن سوال جدید</span>';
    echo '</div>';
}

// Save Meta Box
function save_page_meta_box($post_id) {
    $old = get_post_meta($post_id, '_faq', true);
    $new = array();
    if (isset($_POST['faq_fields']['q'])) {
        $new_q = $_POST['faq_fields']['q'];
        $new_a = $_POST['faq_fields']['a'];
    }
    if (!empty($new_q) && is_array($new_q) && count($new_q) == count($new_a)) {
        foreach ($new_q as $index => $q) {
            $new[] = array(
                'q' => sanitize_text_field($q),
                'a' => sanitize_text_field($new_a[$index]),
            );
        }
    }
    if ($new && $new != $old) {
        update_post_meta($post_id, '_faq', $new);
    } elseif ('' == $new && $old) {
        delete_post_meta($post_id, '_faq', $old);
    }
}

add_action('save_post', 'save_page_meta_box');