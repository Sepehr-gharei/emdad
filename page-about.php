<?php
/* Template Name: About */
get_header();

$id = get_the_ID();


$about = get_post_meta($id, '_about', true);
$about_image  = isset($about['about_image']) ? $about['about_image'] : '';
$about_feather  = isset($about['about_feather']) ? $about['about_feather'] : '';
$about_why = isset($about['about_why']) ? $about['about_why'] : '';
$about_timeline = isset($about['about_timeline']) ? $about['about_timeline'] : '';

?>


<main>
    <section class="about-us-new-section container">
        <div class="about-top-row">
          <div class="about-content-col">
          <?php emdadcamera_breadcrumbs(); ?>

            <h2 class="main-title">
              <?php echo the_title()  ?>
            </h2>
            <p class="desc">
              <?php echo the_content()  ?>
           </p>
            <div class="stats-row ">
              <?php
              
              if (!empty($about_feather) && is_array($about_feather)) {
                $j = 0;
                foreach ($about_feather as $feather) {
                    $icon     = isset($feather['icon']) ? $feather['icon'] : '';
                    $title       = isset($feather['title']) ? $feather['title'] : '';
                    $text       = isset($feather['text']) ? $feather['text'] : '';
                    ?>
                        <div class="stat-card">
                          <i class="icon"><?php echo emdadcamera_Icon($icon) ?></i>
                          <div class="text-field" >
                              <strong><?php echo $title ?></strong>
                          <span><?php echo $text ?></span>
                          </div>
                        </div>
                    <?php
                }
            }
            
            ?>
            </div>
          </div>
      
          <div class="about-image-col">
         <img src="<?php echo $about_image   ?>" alt="">
          </div>
        </div>
      
        <div class="why-us-row our-service-section ">
          <h3 class="section-title">چرا امدادکمرا؟</h3>
          <div class="features-grid grid-items">

            <?php 
              if (!empty($about_why) && is_array($about_why)) {
                $j = 0;
                foreach ($about_why as $item) {
                    $icon     = isset($item['icon']) ? $item['icon'] : '';
                    $title       = isset($item['title']) ? $item['title'] : '';
                    $text       = isset($item['text']) ? $item['text'] : '';
                    ?>
                        <div class="feature-card item">
                            <i class="icon">
                              <?php echo emdadcamera_Icon($icon) ?>
                            </i>
                            <h4><?php echo $title ?></h4>
                            <p><?php echo $text ?></p>
                        </div>
                    <?php
                }
            }
            ?>
          </div>
        </div>
      </section>
      <!-- ===== تایم‌لاین مدرن ===== -->
      <section class="timeline-section">
          <div class="container">
              <div class="title-wrapper">
                  <h2 class="main-site-title currentColor">مسیر همکاری با <span class="title-highlight">امداد دوربین</span></h2>
                  <p class="timeline-subtitle">از ایده تا اجرا، همیشه همراه شما</p>
              </div>

              <div class="timeline">
                  <div class="timeline-line">
                      <div class="timeline-dot-mover"></div>
                  </div>

                  <?php 
                  if (!empty($about_timeline) && is_array($about_timeline)) {
                      $j = 0;
                      foreach ($about_timeline as $timeline_item) {
                          $icon = isset($timeline_item['icon']) ? $timeline_item['icon'] : '';
                          $title = isset($timeline_item['title']) ? $timeline_item['title'] : '';
                          $text = isset($timeline_item['text']) ? $timeline_item['text'] : '';
                          $feather1 = isset($timeline_item['feather1']) ? $timeline_item['feather1'] : '';
                          $feather2 = isset($timeline_item['feather2']) ? $timeline_item['feather2'] : '';
                          $feather3 = isset($timeline_item['feather3']) ? $timeline_item['feather3'] : '';
                          $tag1 = isset($timeline_item['tag1']) ? $timeline_item['tag1'] : '';
                          $tag2 = isset($timeline_item['tag2']) ? $timeline_item['tag2'] : '';

                          // تعیین کلاس right یا left بر اساس زوج یا فرد بودن
                          $position_class = ($j % 2 == 0) ? 'right' : 'left';

                          // فرمت شماره با صدر جلویی (01, 02, 03, ...)
                          $card_number = str_pad($j + 1, 2, '0', STR_PAD_LEFT);
                          ?>

                          <div class="timeline-item <?php echo $position_class; ?>">
                              <div class="timeline-card">
                                  <span class="card-number"><?php echo $card_number; ?></span>
                                  <div class="card-icon icon">
                                      <?php echo emdadcamera_Icon($icon); ?>
                                  </div>
                                  <h3 class="card-title"><?php echo esc_html($title); ?></h3>
                                  <p class="card-desc"><?php echo esc_html($text); ?></p>
                                  <div class="card-meta">
                                      <?php if(!empty($feather1)): ?>
                                          <span><?php echo esc_html($feather1); ?></span>
                                      <?php endif; ?>
                                      <?php if(!empty($feather2)): ?>
                                          <span><?php echo esc_html($feather2); ?></span>
                                      <?php endif; ?>
                                      <?php if(!empty($feather3)): ?>
                                          <span><?php echo esc_html($feather3); ?></span>
                                      <?php endif; ?>
                                  </div>
                                  <div class="card-tags">
                                      <?php if(!empty($tag1)): ?>
                                          <span><?php echo esc_html($tag1); ?></span>
                                      <?php endif; ?>
                                      <?php if(!empty($tag2)): ?>
                                          <span><?php echo esc_html($tag2); ?></span>
                                      <?php endif; ?>
                                  </div>
                              </div>
                          </div>
                                      
                      <?php
                          $j++;
                      }
                  }
                  ?>
              </div>
          </div>
      </section>
   <?php emdadcamera_banner_before_footer(); ?>
 </main>

<?php 
get_footer();
?>