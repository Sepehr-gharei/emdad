<?php
/* Template Name: Contact */
get_header();
$id = get_the_ID();

$contact_info = 'contact_info'; 
$call_text1   = emdadcamera_Get_Setting($contact_info, 'call_text1'); 
$call_url1    = emdadcamera_Get_Setting($contact_info, 'call_url1'); 
$call_text2   = emdadcamera_Get_Setting($contact_info, 'call_text2'); 
$call_url2    = emdadcamera_Get_Setting($contact_info, 'call_url2');
$call_text3  = emdadcamera_Get_Setting($contact_info, 'call_text3'); 
$call_url3    = emdadcamera_Get_Setting($contact_info, 'call_url3');  
$email_text    = emdadcamera_Get_Setting($contact_info, 'email_text');  
$email_url    = emdadcamera_Get_Setting($contact_info, 'email_url');  
$telegram     = emdadcamera_Get_Setting($contact_info, 'telegram'); 
$whatsapp     = emdadcamera_Get_Setting($contact_info, 'whatsapp'); 
$bale     = emdadcamera_Get_Setting($contact_info, 'bale'); 
$address = emdadcamera_Get_Setting($contact_info, 'address');

$contact = get_post_meta($id, '_contact', true);
$contact_image  = isset($contact['contact_image']) ? $contact['contact_image'] : '';
$contact_map   = isset($contact['contact_map']) ? $contact['contact_map'] : '';
?>


<main>
        <section class="contact-hero">
          <div class="container" >
          <?php emdadcamera_breadcrumbs(); ?>
            <div class="contact-header-section">
                <div class="hero-content">
                <h1><?php echo the_title()  ?></h1>
                <p>
                <?php echo the_content()  ?>
                </p>                
            </div>
                <div class="hero-illustration">
                        <img src="<?php echo $contact_image  ?>" alt="">
                </div>
            </div>
          </div>
        </section>
        <section class="contact-main">
            <div class="container">
            <div class="contact-wrapper">
                    
                    <div class="contact-form-wrapper">
                    <?php emdad_render_form('contact'); ?>
                    </div>
    
                    <div class="contact-info-grid">
                        <div class="contact-info-card">
                            <div class="icon-wrapper icon">
                            <?php echo emdadcamera_Icon('location') ?>


                                </div>
                            <h3>آدرس دفتر مرکزی</h3>
                           <p><?php echo $address ?></p>
                        </div>
    
                        <div class="contact-info-card">
                            <div class="icon-wrapper icon">
                            <?php echo emdadcamera_Icon('phone') ?>


                                </div>
                            <h3>خط ویژه </h3>
                            <a href="<?php echo $call_url1  ?>" class="number" ><?php echo $call_text1 ?></a>
                        </div>
    
                        <div class="contact-info-card">
                            <div class="icon-wrapper icon">
                            <?php echo emdadcamera_Icon('headphone') ?>


                                </div>
                            <h3>پشتیبانی آنلاین و فوری</h3>
                            <a href="<?php echo $call_url2  ?>"><?php echo $call_text2 ?></a>
                        </div>
    
                        <div class="contact-info-card">
                            <div class="icon-wrapper icon">
                            <?php echo emdadcamera_Icon('email') ?>

                                </div>
                            <h3>ایمیل</h3>
                            <a href="<?php echo $email_url ?>"><?php echo $email_text ?></a>
                        </div>
                    </div>
    
                </div>
            </div>
        </section>
        <section class="contact-map-section">
            <div class="container">
                <div class="contact-map-wrapper">
                <?php echo $contact_map  ?>
                    </div>
                <p>برای پیدا کردن دقیق‌ترین مسیر روی نقشه کلیک کنید.</p>
            </div>
        </section>
        <section class="contact-social-section">
            <div class="container">
                <h3>ما را در شبکه های اجتماعی دنبال کنید</h3>
                <div class="contact-social-icons">
                    <a href="<?php echo $whatsapp ?>" class="social-icon">
                      <i class="icon">
                        <?php echo emdadcamera_Icon('whatsapp-icon') ?>
                      </i>
                        </a>
                          <a href="<?php echo $bale ?>" class="social-icon">
                      <i class="icon">
                        <?php echo emdadcamera_Icon('bale-icon') ?>
                      </i>
                        </a>
                    <a href="<?php echo $instagram ?>" class="social-icon">
                      <i class="icon">
                        <?php echo emdadcamera_Icon('instagram-icon') ?>
                      </i>
                        </a>
                    <a href="<?php echo $telegram ?>" class="social-icon">
                      <i class="icon">
                        <?php echo emdadcamera_Icon('telegram-icon') ?>
                      </i>
                        </a>
                </div>
            </div>
        </section>
    </main>
<?php 
get_footer();