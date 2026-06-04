<?php

$contact_info = 'contact_info';
$global_info = 'global_info';
$address = emdadcamera_Get_Setting($contact_info, 'address');
$footer_copyright = emdadcamera_Get_Setting($global_info, 'footer_copyright');
$footer_text = emdadcamera_Get_Setting($global_info, 'footer_text');


$address = emdadcamera_Get_Setting($contact_info, 'address');
$call_text1 = emdadcamera_Get_Setting($contact_info, 'call_text1');
$call_url1 = emdadcamera_Get_Setting($contact_info, 'call_url1');
$call_text2 = emdadcamera_Get_Setting($contact_info, 'call_text2');
$call_url2 = emdadcamera_Get_Setting($contact_info, 'call_url2');
$email_text = emdadcamera_Get_Setting($contact_info, 'email_text');

$whatsapp = emdadcamera_Get_Setting($contact_info, 'whatsapp');
$telegram = emdadcamera_Get_Setting($contact_info, 'telegram');
$instagram = emdadcamera_Get_Setting($contact_info, 'instagram');
$bale = emdadcamera_Get_Setting($contact_info, 'bale');

?>
<footer class="footer"> 
    <div class="container"> 
        <div class="footer-grid"> 
            
            <div class="footer-col"> 
                    <a href="<?php echo home_url(); ?>" class="logo-wrapper"> 
            <div class="icon"><?php echo emdadcamera_icon('logo-icon'); ?></div> 
        </a> 
                <p><?php echo esc_html($footer_text); ?></p> 
                <div class="socials"> 
                    <?php if($whatsapp): ?><a href="<?php echo esc_url($whatsapp); ?>" class="icon"><?php echo emdadcamera_icon('whatsapp-icon'); ?></a><?php endif; ?>
                    <?php if($bale): ?><a href="<?php echo esc_url($bale); ?>" class="icon"><?php echo emdadcamera_icon('bale-icon'); ?></a><?php endif; ?>
                    <?php if($telegram): ?><a href="<?php echo esc_url($telegram); ?>" class="icon"><?php echo emdadcamera_icon('telegram-icon'); ?></a><?php endif; ?>
                    <?php if($instagram): ?><a href="<?php echo esc_url($instagram); ?>" class="icon"><?php echo emdadcamera_icon('instagram-icon'); ?></a><?php endif; ?>
                </div> 
            </div> 

            <div class="footer-col menu-item"> 
                <?php 
                if (has_nav_menu('footer-links')) {
                    wp_nav_menu(array(
                        'theme_location' => 'footer-links',
                        'container'      => false,
                        'menu_class'     => 'footer-links'
                    ));
                } else {
                    echo '<ul class="footer-links"><li><a href="#">منو تنظیم نشده است</a></li></ul>';
                }
                ?>
            </div> 
            
            <div class="footer-col address-more"> 
                <ul class="footer-contact"> 
                    <li> 
                        <i class="icon"><?php echo emdadcamera_icon('location-icon'); ?></i> 
                        <?php echo esc_html($address); ?>
                    </li> 
                    <li> 
                        <i class="icon"><?php echo emdadcamera_icon('telephone-icon'); ?></i> 
                        <a href="<?php echo esc_url($call_url1); ?>" class="number"><?php echo esc_html($call_text1); ?></a> 
                        <span>|</span>
                        <a href="<?php echo esc_url($call_url2); ?>" class="number"><?php echo esc_html($call_text2); ?></a> 
                    </li> 
                    <li> 
                        <i class="icon"><?php echo emdadcamera_icon('email-icon'); ?></i> 
                        <?php echo esc_html($email_text); ?>
                    </li> 
                    <li> 
                        <i class="icon"><?php echo emdadcamera_icon('fulltime-icon'); ?></i> پشتیبانی ۲۴ ساعته
                    </li> 
                </ul> 
            </div> 
            <div class="footer-col namad" >
                <div class="enamad" >
                    <div class="item" >
                      <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=737571&Code=zLXRTHI08MeIQS30LhwKygxVStPJ5g1V'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=737571&Code=zLXRTHI08MeIQS30LhwKygxVStPJ5g1V' alt='' style='cursor:pointer' code='zLXRTHI08MeIQS30LhwKygxVStPJ5g1V'></a>
                    </div>
                 <div class="item" >
                            <div id="zarinpal"><script src="https://www.zarinpal.com/webservice/TrustCode" type="text/javascript"></script></div></div>
                </div> 
            </div>
        </div> 

        <div class="footer-copyright"> 
            <p><?php echo esc_html($footer_copyright); ?></p> 
        </div> 
    </div> 
</footer> 
<?php if ( function_exists('WC') ) :
    $cart_count = WC()->cart ? intval(WC()->cart->get_cart_contents_count()) : 0;
    $cart_url   = wc_get_cart_url();
?>
<a href="<?php echo esc_url($cart_url); ?>"
   class="fc-wrap<?php echo $cart_count > 0 ? ' visible' : ''; ?>"
   id="floatingCart"
   aria-label="سبد خرید">

    <span class="fc-inner">
        <svg viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
    </span>

    <span class="fc-badge" id="fcBadge"
          <?php if($cart_count > 0) echo 'data-visible="1"'; ?>>
        <?php echo $cart_count ?: ''; ?>
    </span>

</a>
<?php endif; ?>

<button class="scroll-top-wrap" id="scrollTopBtn" aria-label="برو به بالای صفحه">
    <svg class="st-svg" viewBox="0 0 60 60">
        <circle class="st-svg__track" cx="30" cy="30" r="27"/>
        <circle class="st-svg__bar"   cx="30" cy="30" r="27"/>
    </svg>
    
    <span class="st-inner">
        <svg viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round"
             width="22" height="22">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </span>
</button>
<?php 
wp_footer(); 


echo emdadcamera_Get_Setting('codes', 'before_body'); 
?> 

</body> 
</html>