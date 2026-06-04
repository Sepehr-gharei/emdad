<?php
get_header();

// دریافت اطلاعات دسته‌بندی فعلی
$current_cat_id = get_queried_object_id();
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
if (isset($_GET['paged']))
    $paged = intval($_GET['paged']);

// تنظیمات برای AJAX
$ajax_data_query = array(
    'cat' => $current_cat_id,
    'paged' => $paged
);
?>

<main class="main blog category">

    <!-- هدر دسته‌بندی و مسیر راهنما (Breadcrumbs) -->
    <section class="page-header" style="margin-bottom: 30px;">
        <div class="container">
            <div class="page-header-entry text-center">
                <h1 class="title"><?php single_cat_title('', true); ?></h1>
                <?php if (function_exists('emdadcamera_breadcrumbs'))
                    emdadcamera_breadcrumbs(); ?>
            </div>
        </div>
    </section>

    <?php
    // توضیحات دسته‌بندی (اگر در پنل وردپرس وارد شده باشد)
    $page_content = category_description();
    if (!empty($page_content)) {
        echo '<section class="glassy-content mb-40">'
            . '<div class="container">'
            . '<article class="content-entry content">'
            . apply_filters('the_content', $page_content)
            . '</article>'
            . '</div>'
            . '</section>';
    }
    ?>

    <section class="container">

        <?php
        // ایجاد دکمه‌های فیلتر به صورت داینامیک (لیست زیردسته‌های همین دسته)
        $child_categories = get_categories(array('child_of' => $current_cat_id, 'hide_empty' => true));
        if (!empty($child_categories)) {
            echo '<div class="archive-filters">';
            echo '<button class="filter-btn active" data-cat="' . esc_attr($current_cat_id) . '">همه</button>';
            foreach ($child_categories as $child) {
                echo '<button class="filter-btn" data-cat="' . esc_attr($child->term_id) . '">' . esc_html($child->name) . '</button>';
            }
            echo '</div>';
        }
        ?>

        <!-- کانتینر اصلی شبکه‌بندی مقالات (متصل به AJAX) -->
        <div id="ajax-category-content" class="archive-grid emdadcamera-ajax-container" data-context="category"
            data-query='<?php echo json_encode($ajax_data_query); ?>'>

            <?php
            if (have_posts()) {
                while (have_posts()) {
                    the_post();
                    $id = get_the_ID();

                    // دریافت اولین دسته‌بندی برای بج (Badge) روی کارت
                    $cats = get_the_category();
                    $badge_name = !empty($cats) ? $cats[0]->name : 'مقاله';

                    // تصویر شاخص داینامیک با تصویر جایگزین در صورت نبود عکس
                    $thumbnail_url = has_post_thumbnail() ? get_the_post_thumbnail_url($id, 'large') : get_template_directory_uri() . '/assets/img/default-thumbnail.jpg';
                    ?>

                    <div class="archive-card">
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"
                                loading="lazy">
                        </a>
                        <div class="text-wrapper">
                            <span class="badge"><?php echo esc_html($badge_name); ?></span>
                            <h3 style="margin: 10px 0;" ><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <a class="btn btn-primary" dir="ltr" href="<?php the_permalink(); ?>">
                                <div class="btn__text" data-text=" خواندن ادامه مطلب"> خواندن ادامه مطلب </div>
                            </a>
                        </div>
                    </div>

                    <?php
                }
            } else {
                echo '<div class="no-posts-found" style="grid-column: 1 / -1; text-align: center; padding: 50px;">هیچ مقاله‌ای در این دسته یافت نشد.</div>';
            }
            ?>

        </div>

        <?php
        // بخش صفحه‌بندی (Pagination) اختصاصی
        global $wp_query;
        if ($wp_query->max_num_pages > 1) {
            $base_link = get_category_link($current_cat_id);

            echo '<div class="pagination mt-30 text-center flex align-items-center justify-content-center" style="grid-column: 1 / -1; margin-top: 40px;">';
            echo paginate_links(array(
                'base' => $base_link . '%_%',
                'format' => '?paged=%#%',
                'total' => $wp_query->max_num_pages,
                'current' => $paged,
                'prev_text' => '&lt;', // علامت قبلی
                'next_text' => '&gt;', // علامت بعدی
                'type' => 'plain',
                'add_args' => false
            ));
            echo '</div>';
        }
        ?>

    </section>
</main>

<?php get_footer(); ?>