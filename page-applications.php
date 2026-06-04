<?php
/**
 * Template Name: page-applications
 */
get_header();

// تنظیمات کوئری برای دریافت پست‌های applications
$args = array(
    'post_type'      => 'applications',
    'posts_per_page' => -1, // -1 برای نمایش تمام پست‌ها
);

$query = new WP_Query($args);
?>

<main>
    <section class="container">
        <div class="archive-grid service-arhcive">
            <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); 
                
                // دریافت اطلاعات متا برای هر پست در حلقه
                $applications = get_post_meta(get_the_ID(), '_applications', true);
                $appstore = isset($applications['appstore']) ? $applications['appstore'] : '';
                $download = isset($applications['download']) ? $applications['download'] : '';
                 $size = isset($applications['size']) ? $applications['size'] : '';
                $icon = isset($applications['icon']) ? $applications['icon'] : '';
            ?>
                <div class="archive-card" >
            <img class="download-img" src="<?php echo $icon   ?>" alt="<?php echo the_title()  ?>">
            <div class="text-wrapper" >
<h3> <?php echo the_title()  ?> </h3>
<p><?php echo $size   ?></p>
            
            <a class="btn btn-reverse" dir="ltr" href="<?php echo $download ?>">
                            <div class="btn__text" data-text="دانلود مستقیم">
                                دانلود مستقیم                             </div>
                            <i class="icon">
                               	<?php  echo emdadcamera_Icon('download-icon'); ?>
                            </i>
            </a>
             <a class="btn btn-primary" dir="ltr" href="<?php echo $appstore ?>">
                            <div class="btn__text" data-text="دانلود اپ استور  ">
                                دانلود اپ استور                             </div>
                            <i class="icon">
                               	<?php  echo emdadcamera_Icon('appstore-icon'); ?>
                            </i>
            </a>
            </div>
            
        </div>
            <?php endwhile; 
                wp_reset_postdata(); // بازگرداندن دیتای پست اصلی
            else : 
                echo '<p>پستی یافت نشد.</p>';
            endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>