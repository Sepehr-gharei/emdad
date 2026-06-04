<?php
/**
 * Template Name: Brands Taxonomy Archive
 */
get_header();

// روش استاندارد و امن‌تر برای دریافت شماره صفحه در وردپرس
$paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : ((get_query_var('page')) ? absint(get_query_var('page')) : 1);
$brands_per_page = 12; // تعداد برندها در هر صفحه
$offset = ($paged - 1) * $brands_per_page;

// شمارش کل برندها برای محاسبه دقیق تعداد صفحات (استفاده از کش داخلی وردپرس)
$total_brands = wp_count_terms([
    'taxonomy'   => 'product_brand',
    'hide_empty' => false, // اگر می‌خواهید فقط برندهای دارای محصول نمایش داده شوند، این را true کنید
]);

// محاسبه بیشترین تعداد صفحه برای صفحه‌بندی
$max_num_pages = ceil($total_brands / $brands_per_page);

// آرگومان‌های دریافت ترم‌های (برندهای) ووکامرس
$args = [
    'taxonomy'   => 'product_brand',
    'hide_empty' => false,
    'number'     => $brands_per_page,
    'offset'     => $offset,
    'orderby'    => 'name',
    'order'      => 'ASC',
];

$brands = get_terms($args);
?>

<main class="brands-archive-page">
    <div class="container">
        <div class="title-main">
            <strong>برندهای همکار و تجهیزات</strong>
            <?php if (!is_wp_error($total_brands) && $total_brands > 0): ?>
                <span class="brand-count"><?php echo esc_html($total_brands); ?> برند</span>
            <?php endif; ?>
        </div>
        
        <div class="brands-grid">
            <?php if (!empty($brands) && !is_wp_error($brands)): ?>
                <?php foreach ($brands as $brand): 
                    
                    // دریافت لینک آرشیو محصولات آن برند
                    $brand_link = get_term_link($brand);
                    
                    // دریافت تصویر برند
                    // در اکثر قالب‌ها و افزونه‌های استاندارد ووکامرس، شناسه عکسِ دسته‌بندی یا برند در متای thumbnail_id ذخیره می‌شود
                    $thumbnail_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
                    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : get_template_directory_uri() . '/assets/img/default-brand.png';
                ?>
                    <div class="brand-item">
                        <div href="<?php echo esc_url($brand_link); ?>" class="brand-link">
                            <div class="brand-image">
                                <img src="<?php echo esc_url($thumbnail_url); ?>" 
                                     alt="<?php echo esc_attr($brand->name); ?>"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($max_num_pages > 1): ?>
                    <div class="brand-pagination">
                        <?php
                        $big = 999999999; // یک عدد بزرگ برای جایگزینی
                        echo paginate_links([
                            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                            'format'    => '?paged=%#%',
                            'current'   => $paged,
                            'total'     => $max_num_pages,
                            'prev_text' => '« قبلی',
                            'next_text' => 'بعدی »',
                            'type'      => 'plain',
                            'mid_size'  => 2,
                            'end_size'  => 1
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-brands-found">
                    <p>هیچ برندی یافت نشد.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>