<?php
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
    the_post();
    global $product;
?>
<main class="single-product-page">
    <div class="container">
        <!-- مسیر راهنما (Breadcrumb) -->
        <div class="emdad-wc-breadcrumb">
            <?php woocommerce_breadcrumb(); ?>
        </div>

        <div class="sp-main-grid">
            <!-- گالری تصاویر محصول -->
            <div class="sp-gallery">
                <?php 
                // فراخوانی اختصاصی فقط برای گالری تصاویر
                woocommerce_show_product_images(); 
                ?>
            </div>

            <!-- اطلاعات محصول -->
            <div class="sp-info">
                <h1 class="sp-title"><?php the_title(); ?></h1>
                
                <!-- لوگوی برند (استاتیک برای تست، بعداً داینامیک میشود) -->
                <div class="sp-brand">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/hikvision.png" alt="برند">
                </div>

                <div class="sp-price-wrap">
                    <span class="sp-price-label">قیمت :</span>
                    <span class="sp-price-value"><?php echo $product->get_price_html(); ?></span>
                </div>

                <div class="sp-short-desc">
                    <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); ?>
                </div>

                <div class="sp-stock">
                    <?php echo wc_get_stock_html( $product ); ?>
                </div>

                <!-- فرم افزودن به سبد خرید (بدون عنوان و قیمت تکراری) -->
                <div class="sp-cart-form">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- شناسه و دسته‌بندی‌ها -->
                <div class="sp-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </div>
            </div>
        </div>

        <!-- تب‌های توضیحات و مشخصات -->
        <div class="sp-tabs-section">
            <?php 
            // فراخوانی اختصاصی تب‌ها برای جلوگیری از تداخل
            woocommerce_output_product_data_tabs(); 
            ?>
        </div>

        <!-- ویژگی‌های تصویری (همان 3 آیکون) -->
        <div class="sp-features">
            <div class="sp-feat-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/night-vision.png" alt="دید در شب رنگی">
                <h4>دید در شب رنگی</h4>
                <p>تصویر با کیفیت بالا در تاریکی مطلق</p>
            </div>
            <div class="sp-feat-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/smart-detect.png" alt="تشخیص هوشمند">
                <h4>تشخیص هوشمند</h4>
                <p>تشخیص حرکت انسان و خودرو</p>
            </div>
            <div class="sp-feat-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/4k-quality.png" alt="کیفیت بالا">
                <h4>کیفیت 4K</h4>
                <p>رزولوشن تصویر خیره کننده</p>
            </div>
        </div>

        <!-- محصولات مرتبط -->
        <div class="sp-related-section">
            <div class="title-main"><strong>محصولات مرتبط</strong></div>
            <?php woocommerce_output_related_products(); ?>
        </div>
    </div>
</main>
<?php
endwhile;
get_footer();
?>