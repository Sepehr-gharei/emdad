<?php
// جلوگیری از دسترسی مستقیم به فایل
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Emdadcamera_Menu_Walker extends Walker_Nav_Menu {
    
    // ایجاد ساختار دایوهای زیرمنو
// اصلاح متد start_lvl برای اضافه کردن لینک والد به داخل زیرمنو
function start_lvl( &$output, $depth = 0, $args = null ) {
    if ($depth == 0) {
        // پیدا کردن عنوان و لینک آیتم والد (در این سطح عمق، والد مشخص است)
        // نکته: برای دسترسی آسان‌تر، می‌توانیم از فیلتر استفاده کنیم یا 
        // در متد start_el یک متغیر سراسری تعریف کنیم. 
        // اما یک راه ساده‌تر این است که در همین جا از $args استفاده کنیم:
        
        $output .= '<div class="nav-submenu"><div class="submenu-links"><ul class="submenu-list">';
        
        // اینجا آیتم والد را به صورت دستی به لیست زیرمنو اضافه می‌کنیم
        // دقت کن که در Walker متغیر $args->menu_item نمایش‌دهنده والد است
        if (isset($args->menu_item)) {
            $item = $args->menu_item;
            $output .= '<li><a href="'.esc_url($item->url).'" class="submenu-link">'.$item->title.'</a></li>';
        }
    } else {
        $output .= '<ul class="sub-menu">';
    }
}

    function end_lvl( &$output, $depth = 0, $args = null ) {
        if ($depth == 0) {
            $output .= '</ul></div></div>';
        } else {
            $output .= '</ul>';
        }
    }

    // ساخت آیتم‌های منو
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $args->menu_item = $item;
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $has_children = in_array('menu-item-has-children', $classes);

        // گرفتن آیدی آیکون از کلاس‌های CSS (مثلاً کلاس icon-camera-icon)
        $icon_id = '';
        foreach ($classes as $class) {
            if (strpos($class, 'icon-') === 0) {
                $icon_id = str_replace('icon-', '', $class); // حذف پیشوند icon-
                break;
            }
        }

        // تولید متغیرهای مورد نیاز
        $icon_html = $icon_id ? '<i class="icon">'. emdadcamera_icon($icon_id) .'</i>' : '';
        $en_text   = !empty($item->description) ? '<b class="en">'. esc_html($item->description) .'</b>' : '';
        $url       = !empty($item->url) ? esc_url($item->url) : '#';
        $title     = apply_filters('the_title', $item->title, $item->ID);

        $output .= '<li class="nav-item ' . implode(' ', $classes) . '">';

        // اگر لول اول است
        if ($depth == 0) {
            if ($has_children) {
                // دکمه کشویی
                $output .= '<button class="nav-link" type="button"><div class="nav-link__title">' . $icon_html . '<a href="'.$url.'" class="title">' . $title . ' ' . $en_text . '</a></div><i class="icon-plus"></i></button>';
            } else {
                // لینک ساده لول اول
                $output .= '<a class="nav-link" href="'.$url.'"><div class="nav-link__title">' . $icon_html . '<span class="title">' . $title . ' ' . $en_text . '</span></div></a>';
            }
        } 
        // اگر زیرمنو است
        else {
            $output .= '<a href="'.$url.'" class="submenu-link">' . $title . '</a>';
        }
    }
}