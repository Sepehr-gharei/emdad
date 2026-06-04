<?php

function emdadcamera_title() {
    if(is_singular()){
        $title = get_the_title();
    } elseif (is_category()) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $title = single_term_title( '', false );
    } elseif ( is_404() ) {
        $title = '404';
    } else {
        $title = wp_title( '' );
    }
    return $title;
}




function emdadcamera_comments($comment, $args, $depth) {
    if ( 'div' === $args['style'] ) {
        $tag       = 'div';
        $add_below = 'comment';
    } else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }
    ?>
    <<?php echo $tag; ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID() ?>">
    <?php if ( 'div' != $args['style'] ) { ?>
        <article id="div-comment-<?php comment_ID() ?>" class="comment-body">
    <?php } ?>
        
        <header class="comment-meta">
            <div class="comment-author vcard">
                <i class="icon avatar">
                <?php 
                if ( $args['avatar_size'] != 0 ) {
                    echo get_avatar( $comment, $args['avatar_size'] ); 
                }
                ?>
                </i>
                <div class="fn">
                    <div class="name"><?php echo get_comment_author_link(); ?></div>
                    <time><?php echo get_comment_date(); ?> در <?php echo get_comment_time('H:i'); ?></time>
                </div>
            </div>
            
            <div class="reply">
                <a rel="nofollow" class="comment-reply-link custom-reply-btn" 
                   href="#respond" 
                   data-commentid="<?php echo get_comment_ID(); ?>" 
                   data-author="<?php echo get_comment_author(); ?>"
                   aria-label="پاسخ به <?php echo get_comment_author(); ?>">
                   پاسخ
                </a>
            </div>

            <?php 
            // ----- شرط قطعی: اگر پست مربوط به این کامنت "محصول" نبود، لایک را نشان بده -----
            if ( get_post_type( $comment->comment_post_ID ) !== 'product' ) : 
            ?>
                <div class="comment-like-dislike">
                    <?php if(function_exists('emdadcamera_like_dislike_html')) { emdadcamera_like_dislike_html(get_comment_ID(), 'comment'); } ?>
                </div>
            <?php endif; ?>

        </header>

        <div class="comment-content">
            <?php comment_text(); ?>
            <?php edit_comment_link( __( '(ویرایش)' ), ' ', '' ); ?>
        </div>
        
        <?php if ($comment->comment_approved == '0') : ?>
            <em><?php _e('دیدگاه شما در انتظار تایید است.'); ?></em><br/>
        <?php endif; ?>
        
    <?php if ( 'div' != $args['style'] ) : ?>
        </article>
    <?php endif;
}

function emdadcamera_check_honeypot_field() {
    if (!empty($_POST['website'])) {
        wp_die('درخواست نامعتبر!');
    }
}
add_action('pre_comment_on_post', 'emdadcamera_check_honeypot_field');



/**
 * نمایش بنر قبل از فوتر
 *
 * @param bool $is_dark_mode اگر true باشد، کلاس dark به بخش اضافه می‌شود
 */
function emdadcamera_banner_before_footer($is_dark_mode = false) {
    // دریافت مقادیر از تنظیمات
    $global_info = 'global_info';
    $banner_before_footer_img = emdadcamera_Get_Setting($global_info, 'banner_before_footer_img');
    $banner_before_footer_title = emdadcamera_Get_Setting($global_info, 'banner_before_footer_title');
    $banner_before_footer_text = emdadcamera_Get_Setting($global_info, 'banner_before_footer_text');
    $contact_info = 'contact_info'; 
    $call_text1   = emdadcamera_Get_Setting($contact_info, 'call_text1'); 
    $call_url1    = emdadcamera_Get_Setting($contact_info, 'call_url1'); 


    // اگر هیچ داده‌ای وجود نداشت، تابع را متوقف کن
    if (empty($banner_before_footer_img) && empty($banner_before_footer_title)) {
        return;
    }
    
    // تعیین کلاس اضافی برای حالت دارک
    $dark_class = $is_dark_mode ? ' dark' : '';
    ?>
    
    <section class="secure-banner-section container<?php echo esc_attr($dark_class); ?>">
        <div class="inside">
            <?php if (!empty($banner_before_footer_img)) : ?>
                <div class="image-wrapper">
                    <img src="<?php echo esc_url($banner_before_footer_img); ?>" alt="<?php echo esc_attr($banner_before_footer_title); ?>">
                </div>
            <?php endif; ?>
            
            <div class="text-wrapper">
                <?php if (!empty($banner_before_footer_title)) : ?>
                    <h2><?php echo wp_kses_post($banner_before_footer_title); ?></h2>
                <?php endif; ?>
                
                <?php if (!empty($banner_before_footer_text)) : ?>
                    <p><?php echo wp_kses_post($banner_before_footer_text); ?></p>
                <?php endif; ?>
            </div>
            
            <a href="<?php echo  $call_url1 ?>" class="phone-wrapper">
                <div class="text-field">
                    <strong><?php echo  $call_text1 ?></strong>
                    <p>خط ویژه 24 ساعته</p>
                </div>
                <i class="icon">
                    <?php echo emdadcamera_Icon('telephone-icon'); ?>
                </i>
            </a>
        </div>
        <div class="visual-dots"></div>
    </section>
    
    <?php
}
