<?php
get_header();
$id = get_the_ID();
?>
  <main class="single-blog">
        <div class="container">
        <?php emdadcamera_breadcrumbs(); ?>
            <div class="blog-hero-header">
               <?php echo the_post_thumbnail('full', ['alt' => $title, 'title' => $title]); ?>
                <div class="hero-title-card">
                    <h1><?php echo get_the_title(); ?></h1>
                    <div class="meta">
                        <span> <i class="icon" ><?php echo emdadcamera_Icon('calendar-icon') .'</i>'.  get_the_date()  ?></span>
                        <?php
                        $terms = get_the_terms(get_the_ID(), 'category'); // اگر محصول است، 'category' را به 'product_cat' تغییر بده
                        if ($terms && !is_wp_error($terms)) {
                            $term_names = array_slice(wp_list_pluck($terms, 'name'), 0, 3);
                            $category_string = implode('، ', $term_names);
                            ?>
                            <span> 
                                <i class="icon"><?php echo emdadcamera_icon('skill-icon') ?></i> 
                                <?php echo esc_html($category_string); ?>
                            </span>
                        <?php } ?>
                        <span> <i class="icon"><?php echo emdadcamera_Icon('user-icon') .'</i>'. get_the_author() ?></span>
                        <span> <i class="icon"><?php echo emdadcamera_Icon('eye-icon') .'</i>'.  emdadcamera_get_post_views($id)  ?></span>
                    </div>
                </div>
            </div>
    
            <div class="blog-wrapper">
                
                <article class="blog-content">
                <?php echo the_content() ?>
    
                    <?php
                    // ========== پست‌های مرتبط (بهینه شده) ==========
                    $categories = wp_get_post_categories($id);
                    if (!empty($categories)) {
                        $related_args = [
                            'posts_per_page' => 3,
                            'category__in' => $categories,
                            'post__not_in' => [$id],
                            'orderby' => 'rand',
                            'ignore_sticky_posts' => true,
                            'no_found_rows' => true, // بهینه‌سازی برای کاهش بار دیتابیس
                            'update_post_meta_cache' => true,
                            'update_post_term_cache' => true
                        ];
                        
                        $related_query = new WP_Query($related_args);
                        
                        if ($related_query->have_posts()) : ?>
                        <div class="inline-related-posts">
                            <h3>پست‌های مرتبط</h3>
                            <div class="inline-related-grid">
                                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                <a href="<?php the_permalink(); ?>" class="inline-card">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php else : ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/default-thumb.jpg" alt="default image">
                                    <?php endif; ?>
                                    <h4><?php echo wp_trim_words(get_the_title(), 8, '...'); ?></h4>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php 
                        wp_reset_postdata();
                        endif;
                    }
                    ?>
    
                    <div class="share-tags">
                        <span>اشتراک گذاری مقاله</span>
                        <div class="share-socials">
                            <?php 
                            $contact_info = 'contact_info';
                            $whatsapp = emdadcamera_Get_Setting($contact_info, 'whatsapp');
                            $telegram = emdadcamera_Get_Setting($contact_info, 'telegram');
                            $instagram = emdadcamera_Get_Setting($contact_info, 'instagram');
                            $bale = emdadcamera_Get_Setting($contact_info, 'bale');
                            ?>
                            <a href="<?php echo esc_url($whatsapp); ?>" target="_blank"><i class="icon"><?php echo emdadcamera_Icon('whatsapp-icon'); ?></i></a>
                            <a href="<?php echo esc_url($telegram); ?>" target="_blank"><i class="icon"><?php echo emdadcamera_Icon('telegram-icon'); ?></i></a>
                            <a href="<?php echo esc_url($instagram); ?>" target="_blank"><i class="icon"><?php echo emdadcamera_Icon('instagram-icon'); ?></i></a>
                            <a href="<?php echo esc_url($bale); ?>" target="_blank"><i class="icon"><?php echo emdadcamera_Icon('bale-icon'); ?></i></a>
                        </div>
                    </div>
                </article>
    
                <aside class="blog-sidebar">
                    <?php
                    // ========== پست‌های اخیر (بهینه شده) ==========
                    $recent_args = [
                        'posts_per_page' => 3,
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'ignore_sticky_posts' => true,
                        'no_found_rows' => true, // بهینه‌سازی
                        'update_post_meta_cache' => true,
                        'update_post_term_cache' => true
                    ];
                    
                    $recent_query = new WP_Query($recent_args);
                    
                    if ($recent_query->have_posts()) : ?>
                    <div class="blog-widget">
                        <h3>پست‌های اخیر</h3>
                        <div class="recent-posts">
                            <?php while ($recent_query->have_posts()) : $recent_query->the_post(); ?>
                            <a href="<?php the_permalink(); ?>" class="recent-item">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); ?>" alt="<?php the_title_attribute(); ?>">
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/default-thumb.jpg" alt="default image">
                                <?php endif; ?>
                                <div class="text">
                                    <h4><?php echo wp_trim_words(get_the_title(), 6, '...'); ?></h4>
                                    <p><?php echo wp_trim_words(get_the_excerpt(), 10, '...'); ?></p>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php 
                    wp_reset_postdata();
                    endif;
                    ?>
    
                    <?php
                    // ========== دسته‌بندی‌ها (بهینه شده) ==========
                    $categories_args = [
                        'taxonomy' => 'category',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 10, // حداکثر 10 دسته
                        'hide_empty' => true
                    ];
                    
                    $categories_list = get_terms($categories_args);
                    
                    if (!empty($categories_list) && !is_wp_error($categories_list)) : ?>
                    <div class="blog-widget">
                        <h3>دسته‌بندی‌ها</h3>
                        <div class="cat-list">
                            <?php foreach ($categories_list as $category) : ?>
                            <a href="<?php echo get_term_link($category); ?>">
                                <?php echo esc_html($category->name); ?> 
                                <span class="cat-count">(<?php echo $category->count; ?>)</span>
                                
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>
                
            </div>
            <?php if (comments_open() || get_comments_number()) { comments_template(); } ?>
        </div>
    </main>
     
<?php 
get_footer();
?>