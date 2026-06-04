<?php
/**
 * Custom WooCommerce Archive Template
 * قالب کاستوم آرشیو محصولات ووکامرس - امداد کمرا
 */

defined('ABSPATH') || exit;

get_header();

// دریافت دسته‌بندی‌های محصولات برای فیلتر
$product_categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
]);

// دسته فعلی
$current_cat = get_queried_object();

?>

<main class="archive-product-page">

  <!-- هدر آرشیو -->
  <div class="archive-hero">
    <div class="container">
      <div class="archive-hero__inner">
        <div class="archive-hero__text">
          <nav class="emdad-wc-breadcrumb">
            <?php
            if (function_exists('rank_math_the_breadcrumbs')) {
                rank_math_the_breadcrumbs();
            } elseif (function_exists('woocommerce_breadcrumb')) {
                woocommerce_breadcrumb();
            }
            ?>
          </nav>

          <?php
          if (is_product_category()) {
              $cat_obj = get_queried_object();
              $cat_thumb_id = get_term_meta($cat_obj->term_id, 'thumbnail_id', true);
              echo '<h1 class="archive-hero__title">' . esc_html($cat_obj->name) . '</h1>';
              if (!empty($cat_obj->description)) {
                  echo '<p class="archive-hero__desc">' . esc_html($cat_obj->description) . '</p>';
              }
          } else {
              echo '<h1 class="archive-hero__title">فروشگاه <span class="title-highlight">امداد کمرا</span></h1>';
              echo '<p class="archive-hero__desc">بهترین دوربین‌های مداربسته و سیستم‌های امنیتی با کیفیت بالا</p>';
          }
          ?>
        </div>

        <div class="archive-hero__stats">
          <div class="archive-stat">
            <?php
            $total_products = wp_count_posts('product')->publish;
            ?>
            <strong><?php echo $total_products; ?>+</strong>
            <span>محصول</span>
          </div>
          <div class="archive-stat-divider"></div>
          <div class="archive-stat">
            <strong>۱۰ سال</strong>
            <span>تجربه</span>
          </div>
          <div class="archive-stat-divider"></div>
          <div class="archive-stat">
            <strong>۲۴/۷</strong>
            <span>پشتیبانی</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">

    <!-- فیلتر دسته‌ها -->
    <?php if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>
      <div class="archive-filters" id="archive-filters">
       
        

        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn  <?php echo (!is_product_category()) ? 'btn-reverse' : 'btn-primary'; ?>  ">
           
             <span class="btn__text" data-text="   همه محصولات">   همه محصولات</span>
          </a>


        <?php foreach ($product_categories as $cat) :
          // فیلتر دسته Uncategorized
          if ($cat->slug === 'uncategorized') continue;
          $is_active = (is_product_category() && $current_cat->term_id == $cat->term_id);
          ?>
          <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="btn  <?php echo $is_active ? 'btn-reverse' : 'btn-primary'; ?>">
           
             <span class="btn__text" data-text="<?php echo esc_html($cat->name); ?> "> <?php echo esc_html($cat->name); ?>   </span>
             <span class="filter-count icon"><?php echo $cat->count; ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- نوار بالای محصولات -->
    <div class="archive-topbar">
      <div class="archive-topbar__result">
        <?php if (woocommerce_product_loop()) : ?>
          <span>
            <?php
            global $wp_query;
            $total = $wp_query->found_posts;
            echo '<strong>' . $total . '</strong> محصول یافت شد';
            ?>
          </span>
        <?php endif; ?>
      </div>

      <div class="archive-topbar__right">
        <!-- سورت -->
        <?php
        $orderby_options = [
          'menu_order' => 'پیش‌فرض',
          'popularity' => 'پرفروش‌ترین',
          'date'       => 'جدیدترین',
          'price'      => 'ارزان‌ترین',
          'price-desc' => 'گران‌ترین',
          'rating'     => 'بهترین امتیاز',
        ];
        $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'menu_order';
        ?>
        <div class="archive-sort">
          <span class="archive-sort__label">مرتب‌سازی:</span>
          <div class="archive-sort__select-wrap">
            <select class="archive-sort__select" onchange="window.location.href=this.value">
              <?php foreach ($orderby_options as $val => $label) :
                $url = add_query_arg('orderby', $val, get_pagenum_link());
                ?>
                <option value="<?php echo esc_url($url); ?>" <?php selected($current_orderby, $val); ?>>
                  <?php echo esc_html($label); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <i class="sort-arrow">▾</i>
          </div>
        </div>

        <!-- تغییر نما -->
        <div class="archive-view-toggle">
          <button class="view-btn active" data-view="grid" title="نمای گرید">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
              <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
          </button>
          <button class="view-btn" data-view="list" title="نمای لیست">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <rect x="3" y="4" width="18" height="3" rx="1"/>
              <rect x="3" y="10.5" width="18" height="3" rx="1"/>
              <rect x="3" y="17" width="18" height="3" rx="1"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- گرید محصولات -->
    <?php if (woocommerce_product_loop()) : ?>

      <div class="archive-products-grid" id="archive-products-grid">
        <?php
        while (have_posts()) :
          the_post();
          global $product;

          if (!$product) continue;

          $regular_price = (float) $product->get_regular_price();
          $sale_price    = (float) $product->get_sale_price();
          $image_url     = get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail');
          $discount      = 0;

          if (!$image_url) {
              $image_url = wc_placeholder_img_src();
          }

          if ($product->is_on_sale() && $regular_price > 0) {
              $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
          }

          $avg_rating  = $product->get_average_rating();
          $review_count = $product->get_review_count();
          ?>

          <div class="archive-product-card" data-product-id="<?php echo get_the_ID(); ?>">

            <!-- تصویر -->
            <div class="apc__thumb">
              <a href="<?php echo esc_url(get_permalink()); ?>">
                <img src="<?php echo esc_url($image_url); ?>"
                     alt="<?php echo esc_attr($product->get_name()); ?>"
                     loading="lazy" />
              </a>

              <?php if ($discount > 0) : ?>
                <div class="thumb-notch">
                  <span class="notch-discount"><?php echo $discount; ?>٪</span>
                </div>
              <?php endif; ?>

              <?php if (!$product->is_in_stock()) : ?>
                <div class="apc__out-of-stock">ناموجود</div>
              <?php endif; ?>

              <!-- دکمه سبد خرید -->
              <?php if ($product->is_in_stock()) : ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                   class="thumb-cart-bar ajax_add_to_cart"
                   data-product_id="<?php echo esc_attr(get_the_ID()); ?>"
                   data-product_sku="<?php echo esc_attr($product->get_sku()); ?>">
                  <?php
                  if (function_exists('emdadcamera_Icon')) {
                      echo emdadcamera_Icon('basket-icon');
                  } else { ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                      <line x1="3" y1="6" x2="21" y2="6"/>
                      <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                  <?php } ?>
                  <span>افزودن به سبد</span>
                </a>
              <?php endif; ?>
            </div>

            <!-- اطلاعات -->
            <div class="apc__body">

              <!-- دسته‌بندی -->
              <?php
              $cats = get_the_terms(get_the_ID(), 'product_cat');
              if ($cats && !is_wp_error($cats)) {
                  $first_cat = $cats[0];
                  if ($first_cat->slug !== 'uncategorized') {
                      echo '<a href="' . esc_url(get_term_link($first_cat)) . '" class="apc__cat">' . esc_html($first_cat->name) . '</a>';
                  }
              }
              ?>

              <h3 class="apc__title">
                <a href="<?php echo esc_url(get_permalink()); ?>">
                  <?php the_title(); ?>
                </a>
              </h3>

              <!-- امتیاز -->
              <?php if ($avg_rating > 0) : ?>
                <div class="apc__rating">
                  <div class="apc__stars">
                    <?php for ($i = 1; $i <= 5; $i++) :
                      $filled = $i <= round($avg_rating) ? 'filled' : '';
                      ?>
                      <svg class="star-icon <?php echo $filled; ?>" width="14" height="14" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                      </svg>
                    <?php endfor; ?>
                  </div>
                  <span class="apc__rating-count">(<?php echo $review_count; ?>)</span>
                </div>
              <?php endif; ?>

              <!-- قیمت -->
              <div class="apc__price">
                <?php if ($product->is_on_sale()) : ?>
                  <span class="price--current"><?php echo wc_price($sale_price); ?></span>
                  <span class="price--old"><?php echo wc_price($regular_price); ?></span>
                <?php elseif ($product->get_price()) : ?>
                  <span class="price--current"><?php echo wc_price($product->get_price()); ?></span>
                <?php else : ?>
                  <span class="apc__price-contact">تماس بگیرید</span>
                <?php endif; ?>
              </div>

              <!-- لینک مشاهده -->
              <a href="<?php echo esc_url(get_permalink()); ?>" class="apc__view-link">
                مشاهده محصول
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
              </a>

            </div>
          </div>

        <?php endwhile; ?>
      </div>

      <!-- پیجینیشن -->
      <div class="archive-pagination">
        <?php
        echo paginate_links([
          'prev_text' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>',
          'next_text' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>',
          'type'      => 'list',
        ]);
        ?>
      </div>

    <?php else : ?>

      <!-- نتیجه‌ای پیدا نشد -->
      <div class="archive-empty">
        <div class="archive-empty__icon">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </div>
        <h3>محصولی یافت نشد</h3>
        <p>برای جستجوی بهتر دسته‌بندی دیگری را امتحان کنید.</p>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-reverse">
          <span class="btn__text" data-text="بازگشت به فروشگاه">بازگشت به فروشگاه</span>
        </a>
      </div>

    <?php endif; ?>

  </div><!-- /container -->

</main>



<?php
get_footer();
?>