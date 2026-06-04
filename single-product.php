<?php
defined('ABSPATH') || exit;
get_header();

while (have_posts()):
  the_post();
  global $product;

  $id = get_the_ID();

  $specs = get_post_meta($id, '_camera_specs', true);
  $product_features = isset($specs['product_features']) ? $specs['product_features'] : [];
  $camera_specs = get_post_meta( $id , '_camera_specs', true);
  $has_custom_specs = !empty($camera_specs) && is_array($camera_specs) && array_filter($camera_specs);
  ?>


  <main class="single-product-page custom-woo-layout">
    <div class="container">

      <div class="emdad-wc-breadcrumb mt-30">
        <?php
        if (function_exists('emdadcamera_breadcrumbs')) {
          emdadcamera_breadcrumbs();
        } else {
          woocommerce_breadcrumb();
        }
        ?>
      </div>

      <div class="sp-main-grid">

        <div class="sp-info">
          <h1 class="sp-title"><?php the_title(); ?></h1>

          <div class="sp-brand">
            <?php
            $brand_terms = get_the_terms( $product->get_id(), 'product_brand' );
            if (!empty($brand_terms) && !is_wp_error($brand_terms)):
              $brand = $brand_terms[0]; // برند اصلی
              $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
              $brand_logo = '';

              // اگر تصویر برند آپلود شده (شناسه پیوست)
              if ($brand_image_id) {
                $brand_logo = wp_get_attachment_image($brand_image_id, 'full', false, array(
                  'style' => 'max-width:130px; height:auto; display:block; margin-right:0; margin-left:auto;'
                ));
              } else {
                // اگر تصویر به صورت URL سفارشی ذخیره شده
                $custom_url = get_term_meta($brand->term_id, 'brand_image', true);
                if (!$custom_url) {
                  $custom_url = get_term_meta($brand->term_id, 'brand_logo', true);
                }
                if ($custom_url) {
                  $brand_logo = '<img src="' . esc_url($custom_url) . '" alt="' . esc_attr($brand->name) . '" style="max-width:130px; height:auto; display:block; margin-right:0; margin-left:auto;" />';
                } else {
                  // فقط نام برند (بدون تصویر)
                  $brand_logo = '<span style="font-weight:800; font-size:18px; color:var(--font-primary-color);">' . esc_html($brand->name) . '</span>';
                }
              }

              // نمایش مستقیم بدون لینک
              echo $brand_logo;

            else:
              // اگر برندی وجود ندارد
              echo '<img src="' . get_template_directory_uri() . '/assets/img/default-brand.png" alt="برند" style="max-width:130px;">';
            endif;
            ?>
          </div>

          <div class="sp-price-wrap">
            <span class="sp-price-value"><?php echo $product->get_price_html(); ?></span>
          </div>

          <div class="sp-short-desc">
            <?php echo apply_filters('woocommerce_short_description', $post->post_excerpt); ?>
          </div>

          <hr class="sp-divider">


          <div class="sp-cart-form">
            <?php woocommerce_template_single_add_to_cart(); ?>
          </div>
        </div>

        <div class="sp-gallery">
          <div class="gallery-wrapper-inner">
            <?php woocommerce_show_product_images(); ?>
          </div>
        </div>

      </div>

      <div class="sp-custom-tabs-container">
        <ul class="sp-tabs-header">
          <li class="sp-tab-btn active" data-tab="tab-desc">توضیحات</li>
          <li class="sp-tab-btn" data-tab="tab-attr">مشخصات فنی</li>
          <li class="sp-tab-btn" data-tab="tab-comments">دیدگاه کاربران</li>
        </ul>

        <div class="sp-tabs-content-box">
          <div id="tab-desc" class="sp-tab-panel active">
            <div class="sp-panel-inner">
              <?php the_content(); ?>
            </div>
          </div>

          <div id="tab-attr" class="sp-tab-panel">
            <div class="sp-panel-inner">
              <?php
           

              // نمایش مقادیر اختصاصی در صورت وجود
              if ($has_custom_specs) {
                echo '<table class="woocommerce-product-attributes shop_attributes sp-custom-attributes">';

                $spec_labels = [
                  'resolution' => 'کیفیت تصویر',
                  'lens_type' => 'نوع لنز',
                  'night_vision' => 'برد دید در شب',
                  'connectivity' => 'نوع اتصال',
                  'body_material' => 'جنس بدنه',
                  'water_resistance' => 'استاندارد مقاومت',
                  'storage' => 'کارت حافظه',
                  'warranty' => 'گارانتی'
                ];

                foreach ($spec_labels as $key => $label) {
                  if (!empty($camera_specs[$key])) {
                    echo '<tr class="woocommerce-product-attributes-item"><th class="woocommerce-product-attributes-item__label">' . esc_html($label) . '</th><td class="woocommerce-product-attributes-item__value"><p>' . esc_html($camera_specs[$key]) . '</p></td></tr>';
                  }
                }
                echo '</table>';
              }

              // ۲. نمایش ویژگی‌های پیش‌فرض ووکامرس
              $has_woo_attributes = $product->has_attributes() || $product->has_dimensions() || $product->has_weight();
              if ($has_woo_attributes) {
                wc_display_product_attributes($product);
              }

              // ۳. اگر هیچ مشخصاتی وجود نداشت
              if (!$has_custom_specs && !$has_woo_attributes) {
                echo '<p>مشخصات فنی برای این محصول ثبت نشده است.</p>';
              }
              ?>
            </div>
          </div>

          <div id="tab-comments" class="sp-tab-panel">
            <div class="sp-panel-inner">
              <?php
              // حذف فیلتر ووکامرس جهت اجرای مستقیم فایل comments.php قالب شما
              if (class_exists('WC_Template_Loader')) {
                remove_filter('comments_template', array('WC_Template_Loader', 'comments_template_loader'));
              }
              if (comments_open() || get_comments_number()) {
                comments_template();
              }
              ?>
            </div>
          </div>
        </div>
      </div>

      <?php
      // ۱. تعریف ۳ آیتم پیش‌فرض که همیشه وجود دارند
      $default_features = [
        0 => [
          'icon' => 'moon',
          'title' => 'دید در شب رنگی',
          'text' => 'تصویر با کیفیت بالا در تاریکی مطلق'
        ],
        1 => [
          'icon' => 'police',
          'title' => 'تشخیص هوشمند',
          'text' => 'تشخیص حرکت انسان و خودرو'
        ],
        2 => [
          'icon' => 'arrow',
          'title' => 'زوم دیجیتال',
          'text' => 'رزولوشن خیره کننده بدون افت کیفیت'
        ]
      ];

      // ۲. دریافت اطلاعات وارد شده توسط ادمین و فیلتر کردن ردیف‌های خالی
      $admin_features = [];
      if (!empty($product_features) && is_array($product_features)) {
        foreach ($product_features as $index => $feat) {
          // فقط اگر ادمین حداقل یکی از فیلدها را پر کرده بود، آن را بپذیر
          if (!empty($feat['icon']) || !empty($feat['title']) || !empty($feat['text'])) {
            $admin_features[$index] = $feat;
          }
        }
      }

      // ۳. ادغام هوشمند: آیتم‌های ادمین دقیقاً روی ایندکس‌های پیش‌فرض می‌نشینند
      $final_features = array_replace($default_features, $admin_features);

      // ۴. برش آرایه تا مطمئن شویم فقط ۳ آیتم در خروجی رندر می‌شود
      $final_features = array_slice($final_features, 0, 3);
      ?>

      <div class="sp-features">
        <?php
        foreach ($final_features as $feature) {
          $icon = isset($feature['icon']) ? $feature['icon'] : '';
          $title = isset($feature['title']) ? $feature['title'] : '';
          $text = isset($feature['text']) ? $feature['text'] : '';
          ?>
          <div class="sp-feat-item">
            <i class="icon">
                <?php 
                if (function_exists('emdadcamera_Icon') && !empty($icon)) {
                    echo emdadcamera_Icon($icon); 
                }
                ?>
            </i>
            <h4><?php echo esc_html($title); ?></h4>
            <p><?php echo esc_html($text); ?></p>
          </div>
          <?php
        }
        ?>
      </div>
      <div class="sp-related-section">
        <?php
        global $product;
        // دریافت آیدی ۸ محصول مرتبط (می‌توانید عدد ۸ را تغییر دهید)
        $related_products = wc_get_related_products($product->get_id(), 8);

        if (!empty($related_products)):
          ?>
          <section class="last-products-section">
            <div class="container">
              <div class="title-wrapper">
                <h2 class="main-site-title white">
                  محصولات
                  <span class="title-highlight">مرتبط</span>
                </h2>
              </div>

              <!-- اسلایدر محصولات -->
              <div class="products-slider-wrap">
                <div id="last-products-slider" class="swiper slider last-products-slider"
                  data-settings='{"columns":"5","columns_tablet":"4","columns_mobile_tablet":"3","columns_mobile":"2","columns_small_mobile":"1.5","autoplay":true,"infinite":false,"space":"20"}'>
                  <div class="swiper-wrapper">

                    <?php
                    // حلقه نمایش محصولات مرتبط
                    foreach ($related_products as $related_product_id):
                      $rel_product = wc_get_product($related_product_id);
                      if (!$rel_product)
                        continue; // اگر محصولی وجود نداشت رد شو
                
                      $regular_price = (float) $rel_product->get_regular_price();
                      $sale_price = (float) $rel_product->get_sale_price();
                      $image_url = get_the_post_thumbnail_url($related_product_id, 'woocommerce_thumbnail');

                      // اگر محصول تصویر نداشت، تصویر پیش‌فرض ووکامرس را قرار بده
                      if (!$image_url) {
                        $image_url = wc_placeholder_img_src();
                      }
                      ?>

                      <!-- محصول -->
                      <div class="swiper-slide">
                        <div class="last-card">
                          <div class="last-card__thumb">
                            <a href="<?php echo esc_url($rel_product->get_permalink()); ?>" style="display:block;">
                              <img src="<?php echo esc_url($image_url); ?>"
                                alt="<?php echo esc_attr($rel_product->get_name()); ?>" loading="lazy"
                                onerror="this.style.background='linear-gradient(135deg,#e8eaf6,#f3e5f5)';this.alt='تصویر محصول';" />
                            </a>

                            <?php
                            // محاسبه درصد تخفیف در صورت حراج بودن
                            if ($rel_product->is_on_sale() && $regular_price > 0):
                              $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
                              ?>
                              <div class="thumb-notch">
                                <span class="notch-discount"><?php echo $discount; ?>٪</span>
                              </div>
                            <?php endif; ?>

                            <!-- دکمه افزودن به سبد خرید ایجکسی -->
                            <a href="<?php echo esc_url($rel_product->add_to_cart_url()); ?>"
                              class="thumb-cart-bar ajax_add_to_cart"
                              data-product_id="<?php echo esc_attr($related_product_id); ?>"
                              data-product_sku="<?php echo esc_attr($rel_product->get_sku()); ?>">
                              <?php
                              if (function_exists('emdadcamera_Icon')) {
                                echo emdadcamera_Icon('basket-icon');
                              }
                              ?>
                              <span>افزودن به سبد</span>
                            </a>
                          </div>

                          <div class="last-card__body">
                            <h3 class="last-card__title">
                              <a href="<?php echo esc_url($rel_product->get_permalink()); ?>"
                                style="color:inherit; text-decoration:none;">
                                <?php echo esc_html($rel_product->get_name()); ?>
                              </a>
                            </h3>
                            <div class="last-card__price">
                              <?php if ($rel_product->is_on_sale()): ?>
                                <span class="price--current"><?php echo wc_price($sale_price); ?></span>
                                <span class="price--old"><?php echo wc_price($regular_price); ?></span>
                              <?php else: ?>
                                <span class="price--current"><?php echo wc_price($rel_product->get_price()); ?></span>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>

                  </div>
                  <!-- پیجینیشن اسلایدر -->
                  <div class="swiper-pagination"></div>
                </div>

                <!-- دکمه‌های نویگیشن -->
                <button class="swiper-nav-btn button-prev-last-products-slider" aria-label="قبلی">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                  </svg>
                </button>
                <button class="swiper-nav-btn button-next-last-products-slider" aria-label="بعدی">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                  </svg>
                </button>
              </div>
            </div>
          </section>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // تب‌ها
      const tabs = document.querySelectorAll('.sp-tab-btn');
      const panels = document.querySelectorAll('.sp-tab-panel');

      tabs.forEach(tab => {
        tab.addEventListener('click', function () {
          tabs.forEach(t => t.classList.remove('active'));
          panels.forEach(p => p.classList.remove('active'));
          this.classList.add('active');
          document.getElementById(this.getAttribute('data-tab')).classList.add('active');
        });
      });

      // استایل دکمه سبد خرید
      const addToCartBtn = document.querySelector('.single_add_to_cart_button');
      if (addToCartBtn) {
        addToCartBtn.classList.remove('button', 'alt');
        addToCartBtn.classList.add('btn', 'btn-reverse');

        if (!addToCartBtn.querySelector('.btn__text')) {
          const btnText = addToCartBtn.innerText || addToCartBtn.textContent;
          addToCartBtn.innerHTML = '<span class="btn__text" data-text="' + btnText + '">' + btnText + '</span>';
        }
      }
    });

    // ==========================================
    // هک جادویی برای مسدود کردن آلرت زشت مرورگر
    // ==========================================
    jQuery(document).ready(function ($) {
      // گرفتن تابع اصلی آلرت مرورگر
      var originalAlert = window.alert;

      // بازنویسی تابع آلرت
      window.alert = function (message) {
        // اگر پیام ووکامرس بود، آلرت نشان نده و پاپ‌آپ ما را بساز
        if (typeof wc_single_product_params !== 'undefined' && message === wc_single_product_params.i18n_required_rating_text) {

          $('#emdad-error-toast').remove(); // اگر از قبل بود پاکش کن

          // ساخت پاپ آپ شیک در پایین-چپ
          var toastHtml = '<div id="emdad-error-toast" style="position: fixed; bottom: 30px; left: 30px; background-color: #e41522; color: #fff; padding: 18px 24px; border-radius: 12px; box-shadow: 0 10px 30px rgba(228, 21, 34, 0.4); font-family: inherit; font-size: 15px; font-weight: 800; z-index: 999999; transform: translateX(-150%); opacity: 0; transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl; display: flex; align-items: center; gap: 10px;">❌ لطفاً ابتدا امتیاز (ستاره) خود را برای محصول انتخاب کنید.</div>';

          $('body').append(toastHtml);

          // انیمیشن ورود (نمایش)
          setTimeout(function () {
            $('#emdad-error-toast').css({
              'transform': 'translateX(0)',
              'opacity': '1'
            });
          }, 50);

          // انیمیشن خروج (مخفی شدن بعد از ۴ ثانیه)
          setTimeout(function () {
            $('#emdad-error-toast').css({
              'transform': 'translateX(-150%)',
              'opacity': '0'
            });
            setTimeout(function () {
              $('#emdad-error-toast').remove();
            }, 500);
          }, 4000);

        } else {
          // اگر ارور چیز دیگری بود، همون آلرت پیش‌فرض کار کند
          originalAlert(message);
        }
      };

      // با کلیک روی ستاره‌ها، اگر اروری هست ناپدید شود
      $('body').on('click', 'p.stars a', function () {
        $('#emdad-error-toast').css({ 'transform': 'translateX(-150%)', 'opacity': '0' });
      });
    });
  </script>

  <?php
  if (function_exists('emdadcamera_banner_before_footer')) {
    emdadcamera_banner_before_footer();
  }
endwhile;
get_footer();
?>