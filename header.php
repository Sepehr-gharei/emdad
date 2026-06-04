<?php

$contact_info = 'contact_info';
$call_text1 = emdadcamera_Get_Setting($contact_info, 'call_text1');
$call_url1  = emdadcamera_Get_Setting($contact_info, 'call_url1');
$call_text2 = emdadcamera_Get_Setting($contact_info, 'call_url2');
$call_url2  = emdadcamera_Get_Setting($contact_info, 'call_url2');
$telegram  = emdadcamera_Get_Setting($contact_info, 'telegram');
$whatsapp  = emdadcamera_Get_Setting($contact_info, 'whatsapp');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#e41522" />
  <?php wp_head(); ?>
  <?php echo emdadcamera_Get_Setting('codes', 'before_head'); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header">
  <div class="container">
    <div class="mobile-header-right">
      <div class="hamburger-icon ws-menu" id="hamburgerTrigger">
        <i class="icon"><?php echo emdadcamera_icon('hamburger-icon'); ?></i>
      </div>
    </div>

<button type="button" class="header-search-btn" id="mobileSearchToggle" aria-label="جستجو">
        <span class="icon"><?php echo emdadcamera_icon('search-icon'); ?></span>
      </button>
    <!-- ===== دسکتاپ: منو راست ===== -->
    <nav class="nav-field nav-wrapper desktop-nav">
      <?php
      wp_nav_menu(array(
        'theme_location' => 'header-flat',
        'container'   => false,
        'items_wrap'  => '%3$s',
        'walker'    => new class extends Walker_Nav_Menu {
          function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $output .= '<a href="'.esc_url($item->url).'" class="item"><p>'.esc_html($item->title).'</p></a>';
          }
        }
      ));
      ?>
      <div class="underline" style="right: 2.28e-05px; left: auto; width: 0px; visibility: hidden;"></div>
        <div class="emdad-search-container desktop-search">
    <div class="emdad-search-input-wrap">
      <input type="text" class="emdad-live-search-input" placeholder="جستجوی محصول یا مقاله..." autocomplete="off">
      <div class="emdad-search-spinner" style="display:none;"></div>
    </div>
   
    <button type="button" class="header-search-btn" id="desktopSearchToggle" aria-label="جستجو">
      <span class="icon"><?php echo emdadcamera_icon('search-icon'); ?></span>
    </button>
   
    <div class="emdad-search-results-dropdown"></div>
  </div>
    </nav>

    <!-- ===== لوگو وسط ===== -->
    <a href="<?php echo home_url(); ?>" class="logo-wrapper logo-center">
      <div class="icon"><?php echo emdadcamera_icon('logo-icon'); ?></div>
    </a>

<div class="header-left-actions desktop-actions">
 

<?php
// بررسی وضعیت ورود کاربر
$is_logged_in = is_user_logged_in();

// تعیین آدرس لینک و متن بر اساس وضعیت کاربر
$link_url  = $is_logged_in ? home_url( '/my-account' ) : home_url( '/login' );
$link_text = $is_logged_in ? 'حساب کاربری' : 'ورود / ثبت‌نام';
?>

<a href="<?php echo esc_url( $link_url ); ?>" class="btn-login">
  <span class="icon"><?php echo emdadcamera_icon('user-icon'); ?></span>
  <p><?php echo esc_html( $link_text ); ?></p>
</a>

  <a href="<?php echo esc_url($call_url1); ?>" class="btn-phone-header">
    <span class="icon"><?php echo emdadcamera_icon('telephone-icon'); ?></span>
    <p><?php echo esc_html($call_text1); ?></p>
  </a>
</div>


  </div>

  <!-- باکس جستجو (مشترک دسکتاپ و موبایل) -->
<!-- باکس جستجوی موبایل -->
<!-- باکس جستجوی موبایل -->
<div class="header-search-box" id="headerSearchBox">
  <div class="search-box-inner mobile-search-container">
    <div class="emdad-mobile-search-form">
      <div class="input-with-icon">
        <span class="search-icon-inside"><?php echo emdadcamera_icon('search-icon'); ?></span>
        <input type="text" class="emdad-live-search-input" placeholder="جستجو در سایت..." autocomplete="off" />
        <div class="emdad-search-spinner" style="display:none;"></div>
      </div>
      <button type="button" class="search-close-btn" id="searchCloseBtn">✕</button>
    </div>
    <div class="emdad-search-results-dropdown mobile-results"></div>
  </div>
</div>
  <div id="overlay"></div>

  <nav class="navbar">
    <div class="offcanvas offcanvas--wrapper" id="offcanvas-navbar">
      <div class="offcanvas--main">
        <div class="offcanvas-header">
          <div class="offcanvas-header-left">
            <div class="logo-wrapper">
              <a href="<?php echo home_url(); ?>">
                <div class="icon"><?php echo emdadcamera_icon('logo-icon-white'); ?></div>
              </a>
            </div>
          </div>
          <button type="button" class="ws-menu offcanvas__close">✕</button>
        </div>

        <div class="offcanvas-body">
          <?php
          if(has_nav_menu('header-main')) {
            wp_nav_menu(array(
              'theme_location' => 'header-main',
              'menu_class'  => 'navbar-nav level--menu',
              'container'   => false,
              'walker'    => new Emdadcamera_Menu_Walker()
            ));
          }

          if(has_nav_menu('header-flat')) {
            wp_nav_menu(array(
              'theme_location' => 'header-flat',
              'menu_class'  => 'navbar-nav flat--menu',
              'container'   => false,
              'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
              'walker'    => new class extends Walker_Nav_Menu {
                function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                  $url = !empty($item->url) ? esc_url($item->url) : '#';
                  $title = apply_filters('the_title', $item->title, $item->ID);
                  $output .= '<li><a href="' . $url . '">' . $title . '<i class="icon" >'. emdadcamera_icon('arrow-left-icon') .'</i>'.'</a></li>';
                }
              }
            ));
          }
          ?>

          <div class="mobile-menu-socials-wrapper">
            <span class="mobile-social-title">ارتباط در پیام‌رسان‌ها:</span>
            <div class="mobile-social-icons">
              <?php if(!empty($whatsapp)): ?>
                <a href="<?php echo esc_url($whatsapp); ?>" target="_blank"><?php echo emdadcamera_icon('whatsapp-icon') ?></a>
              <?php endif; ?>
              <?php if(!empty($telegram)): ?>
                <a href="<?php echo esc_url($telegram); ?>" target="_blank"><?php echo emdadcamera_icon('telegram-icon') ?></a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="hamburger-menu--footer">
          <div class="hamburger-btns">
            <a class="btn btn-reverse" dir="ltr" href="<?php echo esc_url($call_url2); ?>">
              <div class="btn__text" data-text="درخواست نصب">درخواست نصب</div>
            </a>
            <a class="btn btn-reverse" dir="ltr" href="<?php echo esc_url($call_url1); ?>">
              <div class="btn__text" data-text="<?php echo esc_html($call_text1); ?>"><?php echo esc_html($call_text1); ?></div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
