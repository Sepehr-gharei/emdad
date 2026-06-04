<?php
if ( post_password_required() ) {
    return;
}
?>
<div id="comments" class="comments-area content container" style="padding:0; width:100%;">
    <?php if ( have_comments() ) : ?>
        <div class="comments-title underline">
            <h3 class="section-title text-right mb-30">دیدگاه کاربران</h3>
        </div>
        
        <ul class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'li',
                'avatar_size' => 40,
                'short_ping'  => true,
                'reply_text'  => 'پاسخ',
                'callback'    => 'emdadcamera_comments'
            ) );
            ?>
        </ul>
        
        <?php
        the_comments_pagination( array(
            'prev_text' => '<span class="comment-reader-text">قبلی</span>',
            'next_text' => '<span class="comment-reader-text">بعدی</span>',
        ) );
    endif;
    
    if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="no-comments">نظرات بسته شد.</p>
    <?php endif;

    $commenter = wp_get_current_commenter();
    if ( ! isset( $args['format'] ) )
        $args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
    
    $req = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );
    $html_req = ( $req ? " required='required'" : '' );

    // ----- فرم امتیازدهی استاندارد ووکامرس -----
    $rating_html = '';
    if ( function_exists('is_product') && is_product() && wc_review_ratings_enabled() ) {
        $rating_html = '<div class="comment-form-rating form-group w-100 mb-20">
            <label for="rating" id="comment-form-rating-label" style="display:block; margin-bottom:10px; font-weight:800; text-align:right;">امتیاز شما&nbsp;<span class="required">*</span></label>
            <select name="rating" id="rating" required="">
                <option value="">امتیاز دهید…</option>
                <option value="5">عالی</option>
                <option value="4">خوب</option>
                <option value="3">متوسط</option>
                <option value="2">نه خیلی بد</option>
                <option value="1">خیلی ضعیف</option>
            </select>
        </div>';
    }

    $comments_args = array(
        'comment_field' => $rating_html . '<div class="comment-form-comment form-group w-100"><textarea id="comment" name="comment" class="form-control" aria-required="true" required placeholder="متن پیام " rows="8" cols="45"></textarea></div>',
        'label_submit' => 'ارسال نظر',
        'class_submit' => 'submit submit-btn_ph',
        'submit_button' => '<button name="%1$s" type="submit" id="%2$s" class="btn btn-reverse %3$s" style="width:auto;"><span class="btn__text" data-text="%4$s">%4$s</span></button>',
        'title_reply' => '<span id="reply-title-text" style="font-size:18px; font-weight:900;">دیدگاه خود را بنویسید</span>', 
        'title_reply_before' => '<div id="reply-title" class="comment-reply-title underline">',
        'title_reply_after' => '<button type="button" id="cancel-comment-reply-link" class="btn-link" style="display:none; margin-right:10px; color:red; background:none; border:none; cursor:pointer; font-size:14px;">(لغو پاسخ)</button></div>',
        'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">ایمیل شما محرمانه است و منتشر نخواهد شد.</span></p>',
        'fields' => apply_filters( 'comment_form_default_fields', array(
            'author' => '<div class="comment-form-author form-group"><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" placeholder="نام شما " ' . $aria_req . $html_req . ' /></div>',
            'email' => '<div class="comment-form-email form-group"><input id="email" name="email" type="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" placeholder="ایمیل شما " ' . $aria_req . $html_req . ' /></div>',
            'website' => '<div class="comment-form-website form-group"><input id="website" name="website" type="text" value="" size="30" placeholder=" آدرس وب‌سایت" /></div>',
            'cookies' => '',
        ))
    );

    comment_form($comments_args);
    ?>
</div>

<script>
// جلوگیری از نمایش آلرت زشت ووکامرس و نمایش ارور زیبا
jQuery(document).ready(function($) {
    var $form = $('#commentform');
    $form.on('submit', function(e) {
        var $rating = $(this).find('#rating');
        if ($rating.length > 0 && !$rating.val() && typeof wc_single_product_params !== 'undefined' && wc_single_product_params.review_rating_required === 'yes') {
            e.preventDefault();
            e.stopImmediatePropagation(); // متوقف کردن اسکریپت ووکامرس
            
               if(!$('#custom-rating-alert').length) {
                $form.prepend('<div id="custom-rating-alert" style="color:#e41522; font-weight:bold; margin-bottom:15px; border:1px dashed #e41522; padding:10px; border-radius:8px; text-align:right;">لطفاً ابتدا امتیاز خود راانتخاب کنید.</div>');
            }

            return false;
        }
    });
});
</script>