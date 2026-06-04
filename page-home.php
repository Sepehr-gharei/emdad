<?php
/* Template Name: Home */
get_header();

while ( have_posts() ) : the_post();
    $id = get_the_ID();


    $home = get_post_meta($id, '_home', true);

    // استخراج مقادیر تکی
    $hero_title    = isset($home['hero_title']) ? $home['hero_title'] : '';
    $hero_desc     = isset($home['hero_desc']) ? $home['hero_desc'] : '';
    $hero_subtitle = isset($home['hero_subtitle']) ? $home['hero_subtitle'] : '';
    $hero_bg_image = isset($home['hero_image']) ? $home['hero_image'] : '';
    $hero_buttons = isset($home['hero_buttons']) ? $home['hero_buttons'] : [];
    
    $about_title   = isset($home['about_title']) ? $home['about_title'] : '<h2 class="main-site-title">تیمی همیشه هوشیار برای <span class="title-highlight">امنیت هوشمند شما</span></h2>';
    $about_desc    = isset($home['about_desc']) ? $home['about_desc'] : '';
    $about_image   = isset($home['about_image']) ? $home['about_image'] : '';
    $about_buttons = isset($home['about_buttons']) ? $home['about_buttons'] : [];
     
?>
<main>

    <section class="hero-header" style="background-image: url('<?php echo esc_url($hero_bg_image); ?>');">
    <div class="container">
        <div class="text-field">
            <strong><?php echo esc_html($hero_subtitle); ?></strong>
            <?php echo $hero_title; // عنوان که حاوی span است ?>
            <p><?php echo esc_html($hero_desc); ?></p>
            
            <div class="btn-field">
                <?php 
                if (!empty($hero_buttons) && is_array($hero_buttons)) {
                    $i = 0;
                    foreach ($hero_buttons as $btn) {
                        $i++;
                        // دکمه اول reverse، دکمه‌های بعدی primary
                        $btn_class = ($i == 1) ? 'btn btn-reverse' : 'btn btn-primary pulse-glow';
                        $title = isset($btn['title']) ? $btn['title'] : 'بیشتر';
                         $url   = isset($btn['url']) ? esc_url($btn['url']) : '#';
                        
                        // آیکون بر اساس ترتیب (می‌توانید تغییر دهید)
                        $icon_id = ($i == 1) ? 'fulltime-icon' : 'arrow-left-icon';
                        ?>
                        <a class="<?php echo $btn_class; ?>" dir="ltr" href="<?php echo $url; ?>">
                            <div class="btn__text" data-text="<?php echo esc_attr($title); ?>">
                                <?php echo esc_html($title); ?>
                            </div>
                            <i class="icon">
                                <svg><use href="#<?php echo $icon_id; ?>"></use></svg>
                            </i>
                            <?php if ($i > 1): ?>
                                <span class="sweep sweep-white"></span>
                            <?php endif; ?>
                        </a>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    </section>
      <section class="our-service-section container" >
        <div class="title-main" >
          <p>خدمات ما</p>
          <strong>هر چیزی برای امنیت کامل</strong>
        </div>
        <div class="grid-items" >
          <div class="item" >
            <i class="icon" >
				<?php  echo emdadcamera_Icon('camera'); ?>
            </i>
            <div class="text-field" >
              <h3>فروش دوربین</h3>
              <p>عرضه انواع دوربین های روز دنیا با بهترین برند ها</p>
            </div>
          </div>
          <div class="item" >
            <i class="icon" >
			<?php  echo emdadcamera_Icon('build'); ?>
            </i>
            <div class="text-field" >
              <h3>نصب تخصصی </h3>
         <p>نصب حفه ای با تیم مجرب و تجهیزات پیشرفته </p>
            </div>
          </div>
          <div class="item" >
            <i class="icon" >
            <?php  echo emdadcamera_Icon('police'); ?>
            </i>
            <div class="text-field" >
              <h3>دزدگیر اماکن</h3>
<p>
	سیستم های اعلام سرقت هوشمند و بی سیم
</p>            </div>
          </div>
          <div class="item" >
            <i class="icon" >
            <?php  echo emdadcamera_Icon('cloud'); ?>
            </i>
            <div class="text-field" >
              <h3>انتقال تصویر</h3>
<p>
	مشاهده انلاین تصاویر روی موبایل و کامپیوتر
</p>            </div>
          </div>
          <div class="item" >
            <i class="icon" >
            <?php  echo emdadcamera_Icon('layers'); ?>
            </i>
            <div class="text-field" >
              <h3>پروژه های شرکتی</h3>
<p>
	راهکارهای امنیتی وِزه سازمان ها وشرکت ها 
</p>            </div>
          </div>
          <div class="item" >
            <i class="icon" >
			<?php  echo emdadcamera_Icon('setting'); ?>
            </i>
            <div class="text-field" >
              <h3>خدمات پس از فروش</h3>
<p>
	خدمات پس از فروش و تجهیزات سریع
</p>            </div>
          </div>
        </div>
      </section>
     
     
     <?php
// کوئری بسیار بهینه برای دریافت پرفروش‌ترین محصولات
$args = array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => 8,                  // تعداد محصول در اسلایدر
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,               // بوست شدید پرفورمنس با حذف محاسبه Pagination
    'meta_key'            => 'total_sales',      // استفاده از متای استاندارد ووکامرس برای پرفروش‌ترین‌ها
    'orderby'             => 'meta_value_num',
    'order'               => 'DESC',
    'meta_query'          => WC()->query->get_meta_query(), // رعایت تنظیمات مخفی نبودن محصولات
    'tax_query'           => WC()->query->get_tax_query()   // رعایت تنظیمات دسته‌بندی و موجودی
);

$best_selling_loop = new WP_Query( $args );

if ( $best_selling_loop->have_posts() ) :
?>
<section class="last-products-section">
    <div class="container">
        <div class="title-wrapper">
            <h2 class="main-site-title white">
                پرفروش‌ترین
                <span class="title-highlight">دوربین‌های مداربسته</span>
            </h2>
        </div>
        
        <!-- اسلایدر محصولات -->
        <div class="products-slider-wrap">
            <div id="last-products-slider" class="swiper slider last-products-slider" data-settings='{"columns":"5","columns_tablet":"4","columns_mobile_tablet":"3","columns_mobile":"2","columns_small_mobile":"1.5","autoplay":true,"infinite":false,"space":"20"}'>
                <div class="swiper-wrapper">
                    
                    <?php 
                    // شروع لوپ محصولات
                    while ( $best_selling_loop->have_posts() ) : $best_selling_loop->the_post(); 
                        global $product;
                        
                        $regular_price = (float) $product->get_regular_price();
                        $sale_price    = (float) $product->get_sale_price();
                        $image_url     = get_the_post_thumbnail_url( get_the_ID(), 'woocommerce_thumbnail' );
                        
                        if ( ! $image_url ) {
                            $image_url = wc_placeholder_img_src();
                        }
                    ?>
                    <!-- آیتم محصول -->
                    <div class="swiper-slide">
                        <div class="last-card">
                            <div class="last-card__thumb">
                                <a href="<?php the_permalink(); ?>" style="display:block;">
                                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" onerror="this.style.background='linear-gradient(135deg,#e8eaf6,#f3e5f5)';this.alt='تصویر محصول';" />
                                </a>
                                
                                <?php 
                                // محاسبه درصد تخفیف (بدون ایجاد ریکوئست اضافی)
                                if ( $product->is_on_sale() && $regular_price > 0 ) : 
                                    $discount = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
                                ?>
                                <div class="thumb-notch">
                                    <span class="notch-discount"><?php echo $discount; ?>٪</span>
                                </div>
                                <?php endif; ?>

                                <!-- دکمه افزودن به سبد خرید ایجکسی -->
                                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="thumb-cart-bar ajax_add_to_cart" data-product_id="<?php echo get_the_ID(); ?>" data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>">
                                    <?php 
                                    if ( function_exists( 'emdadcamera_Icon' ) ) {
                                        echo emdadcamera_Icon( 'basket-icon' );
                                    } 
                                    ?>
                                    <span>افزودن به سبد</span>
                                </a>
                            </div>
                            
                            <div class="last-card__body">
                                <h3 class="last-card__title">
                                    <a href="<?php the_permalink(); ?>" style="color:inherit; text-decoration:none;">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <div class="last-card__price">
                                    <?php if ( $product->is_on_sale() ) : ?>
                                        <span class="price--current"><?php echo wc_price( $sale_price ); ?></span>
                                        <span class="price--old"><?php echo wc_price( $regular_price ); ?></span>
                                    <?php else : ?>
                                        <span class="price--current"><?php echo wc_price( $product->get_price() ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    
                </div>
                <!-- پیجینیشن اسلایدر -->
                <div class="swiper-pagination"></div>
            </div>

            <!-- دکمه‌های نویگیشن -->
            <button class="swiper-nav-btn button-prev-last-products-slider" aria-label="قبلی">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
            <button class="swiper-nav-btn button-next-last-products-slider" aria-label="بعدی">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
        </div>
    </div>
</section>
<?php 
endif;
// ریست کردن پست‌دیتا حیاتی است تا قالب به هم نریزد
wp_reset_postdata(); 
?>

      <section class="brands-section container">
    <div class="title-main">
        <strong>برند هایی که به ما اعتماد کردند</strong>
    </div>
    <div class="brand-slider-wrap">
        <div id="brand-slider"
             class="swiper slider brand-slider"
             data-settings='{"columns":"5","columns_tablet":"4","columns_mobile_tablet":"3","columns_mobile":"2","columns_small_mobile":"2","autoplay":true,"infinite":false,"space":"20"}'>
            <div class="swiper-wrapper">
                <?php
                // آرگومان‌های بهینه شده برای اسلایدر (بدون نیاز به صفحه‌بندی)
                $slider_args = [
                    'post_type'              => 'brand',
                    'post_status'            => 'publish',
                    'posts_per_page'         => 12, // تعداد برندهای داخل اسلایدر
                    'orderby'                => 'date',
                    'order'                  => 'DESC',
                    'ignore_sticky_posts'    => true,
                    'no_found_rows'          => true,  // بهینه‌سازی مهم: توقف شمارش کل پست‌ها
                    'update_post_term_cache' => false, // بهینه‌سازی: عدم کش کردن تکسونومی‌ها
                ];
                
                $slider_query = new WP_Query($slider_args);
                
                if ($slider_query->have_posts()) :
                    while ($slider_query->have_posts()) : $slider_query->the_post();
                        // دریافت تصویر یا قرار دادن تصویر پیش‌فرض
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: get_template_directory_uri() . '/assets/img/default-brand.png';
                ?>
                        <div class="swiper-slide">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            </a>
                        </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <div class="swiper-slide">
                        <p>هیچ برندی یافت نشد.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <button class="swiper-nav-btn button-prev-brand-slider" aria-label="قبلی">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </button>
        <button class="swiper-nav-btn button-next-brand-slider" aria-label="بعدی">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6" />
            </svg>
        </button>
    </div>
</section>
      <section class="about-us-section">
    <div class="container">
        <div class="about-us-wrapper">
            
            <div class="about-us-content">
                <?php 
                // چون نوع فیلد editor است، خروجی را مستقیماً چاپ می‌کنیم تا تگ‌های HTML اعمال شوند
                // اگر عنوانی وارد نشده باشد، متن پیش‌فرض بالا نمایش داده می‌شود
                echo $about_title; 
                ?>
                
                <p class="about-us-desc">
                    <?php echo esc_html($about_desc); ?>
                </p>
                
                <div class="about-buttons">
                    <?php 
                    // لوپ برای رندر کردن دکمه‌های ریپیتر درباره ما
                    if (!empty($about_buttons) && is_array($about_buttons)) {
                        $j = 0;
                        foreach ($about_buttons as $btn) {
                            $j++;
                            // دکمه اول btn-reverse و دکمه دوم btn-primary
                            $btn_class = ($j == 1) ? 'btn btn-reverse' : 'btn btn-primary';
                            $title     = isset($btn['title']) ? $btn['title'] : 'مشاهده';
                            $url       = isset($btn['url']) ? esc_url($btn['url']) : '#';
                            
                            // تنظیم آیکون‌ها بر اساس ترتیب (اولی فلش، دومی تلفن)
                            $icon_id   = ($j == 1) ? 'arrow-left-icon' : 'telephone-icon';
                            ?>
                            <a class="<?php echo $btn_class; ?>" dir="ltr" href="<?php echo $url; ?>">
                                <div class="btn__text" data-text="<?php echo esc_attr($title); ?>">
                                    <?php echo esc_html($title); ?>
                                </div>
                                <i class="icon">
                                    <?php echo emdadcamera_icon($icon_id); ?>
                                </i>
                                <?php if ($j > 1): ?>
                                    <span class="sweep sweep-white"></span>
                                <?php endif; ?>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="about-us-visual">
                <div class="visual-main-image">
                    <?php if (!empty($about_image)): ?>
                        <img src="<?php echo esc_url($about_image); ?>" alt="درباره امداد دوربین">
                    <?php else: ?>
                        <p style="text-align: center; color: #ccc;">(لطفا تصویر را از پنل انتخاب کنید)</p>
                    <?php endif; ?>
                    <div class="image-overlay-glow"></div>
                </div>
                
                <div class="floating-card floating-card-2">
                    <div class="card-rating">
                        <span>۵.۰</span>
                        <div class="stars">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="gold" stroke="none"><polygon points="12 17.27 18.18 21 16.54 13.97 22 9.24 14.81 8.63 12 2 9.19 8.63 2 9.24 7.46 13.97 5.82 21 12 17.27" /></svg>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="gold" stroke="none"><polygon points="12 17.27 18.18 21 16.54 13.97 22 9.24 14.81 8.63 12 2 9.19 8.63 2 9.24 7.46 13.97 5.82 21 12 17.27" /></svg>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="gold" stroke="none"><polygon points="12 17.27 18.18 21 16.54 13.97 22 9.24 14.81 8.63 12 2 9.19 8.63 2 9.24 7.46 13.97 5.82 21 12 17.27" /></svg>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="gold" stroke="none"><polygon points="12 17.27 18.18 21 16.54 13.97 22 9.24 14.81 8.63 12 2 9.19 8.63 2 9.24 7.46 13.97 5.82 21 12 17.27" /></svg>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="gold" stroke="none"><polygon points="12 17.27 18.18 21 16.54 13.97 22 9.24 14.81 8.63 12 2 9.19 8.63 2 9.24 7.46 13.97 5.82 21 12 17.27" /></svg>
                        </div>
                    </div>
                    <p>رضایت بالای خریداران</p>
                </div>
                
                <div class="visual-dots"></div>
                <div class="visual-blur"></div>
            </div>
            
        </div>
    </div>
      </section>
      <section class="last-project-section container">
    <div class="title-main">
        <p>آخرین مقالات</p>
    </div>
    <div class="last-blog-slider-wrap">
        <?php
        // ========== آخرین مقالات (بهینه شده با اسلایدر) ==========
        $latest_posts_args = [
            'posts_per_page' => 10, // تعداد مقالات برای اسلایدر
            'post_type' => 'post',
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true, // بهینه‌سازی برای کاهش بار دیتابیس
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true
        ];
        
        $latest_posts_query = new WP_Query($latest_posts_args);
        
        if ($latest_posts_query->have_posts()) :
            // ایجاد شناسه یونیک برای جلوگیری از تداخل با اسلایدرهای دیگر
            $unique_id = uniqid('latest_articles_');
        ?>
        <div
            id="<?php echo esc_attr($unique_id); ?>"
            class="swiper slider last-project-slider"
            data-settings='{"columns":"4","columns_tablet":"3.5","columns_mobile_tablet":"3","columns_mobile":"2","columns_small_mobile":"1.5","autoplay":true,"infinite":false,"space":"20"}'
        >
            <div class="swiper-wrapper">
                <?php while ($latest_posts_query->have_posts()) : $latest_posts_query->the_post(); 
                    // دریافت اولین دسته‌بندی مقاله
                    $post_categories = get_the_terms(get_the_ID(), 'category');
                    $first_category = '';
                    $category_icon = 'location-icon'; // آیکون پیش‌فرض
                    
                    if ($post_categories && !is_wp_error($post_categories)) {
                        $first_category = $post_categories[0]->name;
                        // تنظیم آیکون بر اساس دسته‌بندی (اختیاری)
                        switch($post_categories[0]->slug) {
                            case 'آموزشی':
                                $category_icon = 'edu-icon';
                                break;
                            case 'نصب-و-راه‌اندازی':
                                $category_icon = 'install-icon';
                                break;
                            case 'اخبار':
                                $category_icon = 'news-icon';
                                break;
                            default:
                                $category_icon = 'location-icon';
                        }
                    }
                ?>
                <a href="<?php the_permalink(); ?>" class="swiper-slide">
                    <div class="image-wrapper">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php else : ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/default-article.jpg" alt="default image">
                        <?php endif; ?>
                    </div>
                    <div class="text-wrapper">
                        <strong><?php echo wp_trim_words(get_the_title(), 8, '...'); ?></strong>
                        <span>
                            <i class="icon">
                                <?php echo emdadcamera_Icon($category_icon); ?>
                            </i>
                            <p><?php echo esc_html($first_category); ?></p>
                        </span>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        <?php 
        wp_reset_postdata();
        else : ?>
        <div class="no-articles">
            <p>هیچ مقاله‌ای یافت نشد.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
      <?php emdadcamera_banner_before_footer(); ?>
     </main>
<?php 
endwhile;
get_footer(); 
?>