<?php
/* Template Name: Terms & Conditions */
defined('ABSPATH') || exit;
get_header();

while (have_posts()) : the_post();
?>
<main class="policy-page" style="direction:rtl; padding: 60px 0 100px;">
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

        <div class="policy-header" style="margin: 40px 0 50px;">
            <h1 style="font-size: 32px; font-weight: 900; color: var(--font-primary-color); margin-bottom: 10px;">
                <?php the_title(); ?>
            </h1>
            <p style="font-size: 13px; color: #999; font-weight: 600;">
                آخرین بروزرسانی: <?php echo get_the_modified_date('j F Y'); ?>
            </p>
            <hr style="border: 0; border-bottom: 2px solid var(--normal-primary-color); width: 60px; margin: 20px 0 0;">
        </div>

        <div class="policy-body" style="max-width: 860px;">
            <?php the_content(); ?>
        </div>

    </div>
</main>



<?php
    if (function_exists('emdadcamera_banner_before_footer')) {
        emdadcamera_banner_before_footer();
    }
endwhile;
get_footer();
?>