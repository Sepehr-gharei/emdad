<?php
/**
 * این کدها را در آخر functions.php اضافه کن
 */

function emdadcamera_render_card(int $id): void {
    $title   = get_the_title($id);
    $link    = get_permalink($id);
    $thumb   = get_the_post_thumbnail($id, 'large');
    
    // بهینه‌سازی: خواندن مستقیم از دیتابیس برای جلوگیری از پردازش سنگین get_the_content
    $raw_content = get_the_excerpt($id) ?: strip_tags(get_post_field('post_content', $id));
    $excerpt     = wp_trim_words($raw_content, 20, '...');
    
    $cats  = get_the_category($id);
    $badge = $cats ? esc_html($cats[0]->name) : '';
    ?>
    <div class="archive-card">
      <?php if ($thumb): ?>
        <a href="<?= esc_url($link) ?>"><?= $thumb ?></a>
      <?php endif; ?>
      <div class="text-wrapper">
        <?php if ($badge): ?><span class="badge"><?= $badge ?></span><?php endif; ?>
        <h3><?= esc_html($title) ?></h3>
        <p><?= esc_html($excerpt) ?></p>
        <a class="btn btn-primary" href="<?= esc_url($link) ?>">
          <div class="btn__text" data-text="خواندن ادامه مطلب">خواندن ادامه مطلب</div>
        </a>
      </div>
    </div>
    <?php
}

add_action('wp_ajax_blog_filter',        'emdadcamera_blog_filter_handler');
add_action('wp_ajax_nopriv_blog_filter', 'emdadcamera_blog_filter_handler');

function emdadcamera_blog_filter_handler(): void {
    check_ajax_referer('ajax-nonce', 'nonce');

    // دریافت مستقیم ID به جای slug برای حذف یک کوئری اضافی
    $cat_id = absint($_POST['cat_id'] ?? 0);
    $paged  = max(1, absint($_POST['paged'] ?? 1));

    $args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 9,
        'paged'               => $paged,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        // فعال نگه داشتن کش داخلی وردپرس برای کوئری
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
    ];

    if ($cat_id > 0) $args['cat'] = $cat_id;

    $q = new WP_Query($args);
    ob_start();

    if ($q->have_posts()) {
        echo '<div class="archive-grid">';
        while ($q->have_posts()) {
            $q->the_post();
            emdadcamera_render_card(get_the_ID());
        }
        echo '</div>';

        if ($q->max_num_pages > 1) {
            echo '<div class="blog-pagination">';
            echo paginate_links([
                'total'     => $q->max_num_pages,
                'current'   => $paged,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type'      => 'plain',
            ]);
            echo '</div>';
        }
    } else {
        echo '<p class="no-posts-found">هیچ موردی یافت نشد.</p>';
    }

    wp_reset_postdata();
    wp_send_json_success(['html' => ob_get_clean()]);
}

add_filter('redirect_canonical', function($url) {
    return isset($_GET['paged']) || isset($_GET['cat']) ? false : $url;
});