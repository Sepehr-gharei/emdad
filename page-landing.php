<?php
/**
 * Template Name: Landing
 */
get_header();

$id = get_the_ID();


$landing = get_post_meta($id, '_landing', true);
$landing_image  = isset($landing['landing_image']) ? $landing['landing_image'] : '';
$landing_process  = isset($landing['landing_process']) ? $landing['landing_process'] : '';
$landing_gallery_images  = isset($landing['landing_gallery_images']) ? $landing['landing_gallery_images'] : '';
$landing_faq = isset($landing['landing_faq']) ? $landing['landing_faq'] : '';
?>
 <main>
        <section class="install-page-hero" style="background-image: url('<?php echo esc_url($landing_image ); ?>');" >
            <div class="container">
              
                <h1><?php echo the_title()  ?></h1>
                <p><?php echo the_content()  ?></p>
                <a href="#" class="btn btn-reverse">
                    <span class="btn__text" data-text="درخواست مشاوره رایگان">درخواست مشاوره رایگان</span>
                </a>
            </div>
        </section>
    
    
    
        <section class="container" style="margin-bottom: 80px;">
            <div class="title-main">
                <strong>روند کار نصب</strong>
            </div>
            <div class="grid-items process-grid">
         


                 <?php
              
              if (!empty($landing_process) && is_array($landing_process)) {
                $j = 0;
                foreach ($landing_process as $process) {
                    $icon     = isset($process['icon']) ? $process['icon'] : '';
                    $title       = isset($process['title']) ? $process['title'] : '';
                    ?>
                        <div class="item">
                            <i class="icon">
                             <?php echo emdadcamera_Icon($icon) ?>
                            </i>
                            <div class="text-field"><h3><?php echo $title ?></h3></div>
                        </div>
                    <?php
                }
            }
            
            ?>

            </div>
        </section>
    
        <section class="container" style="margin-bottom: 80px;">
            <div class="title-main">
                <strong>نمونه کارهای نصب دوربین</strong>
            </div>
            <div class="gallery-grid">
              

                <?php

    $gallery_ids = explode( ',', $landing_gallery_images );
    foreach ( $gallery_ids as $image_id ) {
        $image_url = wp_get_attachment_image_url( $image_id, 'full' );
        $image_title = get_the_title( $image_id );
        if ( $image_url ) {
            echo '<a href="' . esc_url( $image_url ) . '" data-fancybox="gallery" data-caption="نصب سیستم نظارتی در برج مسکونی">
                    <img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_title ) . '" loading="lazy">
                </a>';
        }
    }

?>
            </div>
        </section>
    
        <section class="container" style="margin-bottom: 80px;">
            <div class="title-main">
                <strong>سوالات متداول نصب</strong>
            </div>
            <div class="faq-contact-wrapper">
                
                <div class="contact-side">
                    <?php emdad_render_form('service'); ?>
                    
                    <div class="support-box">
                        <div class="text-wrap">
                            <strong class="number" >021-9100 1234</strong>
                            <span>پشتیبانی ۲۴ ساعته</span>
                        </div>
                        <div class="icon-wrap">
                            <svg><use href="#telephone-icon"></use></svg>
                        </div>
                    </div>
                </div>
    
                <div class="faq-side">


                      <?php
              
              if (!empty($landing_faq) && is_array($landing_faq)) {
                $j = 0;
                foreach ($landing_faq as $faq) {
                    $text     = isset($faq['text']) ? $faq['text'] : '';
                    $title       = isset($faq['title']) ? $faq['title'] : '';
                    ?>
                            <details class="faq-item">
                                <summary><?php echo $title ?></summary>
                                <p><?php echo $text ?></p>
                            </details>
                    <?php
                }
            }
            
            ?>
                </div>
    
            </div>
        </section>

     <?php emdadcamera_banner_before_footer(true); ?>
    </main>

<?php get_footer(); ?>