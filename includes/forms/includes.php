<?php
defined( 'ABSPATH' ) || exit;

// استفاده از __DIR__ یعنی فایل‌های کلاس را از همین مسیری که این فایل هست لود کن
$dir = __DIR__ . '/'; 

require_once $dir . 'class-database.php';
require_once $dir . 'class-sms.php';
require_once $dir . 'class-ajax.php';
require_once $dir . 'class-admin.php';
require_once $dir . 'class-frontend.php';

// ساخت دیتابیس
add_action('after_setup_theme', function() { 
    $db = new Emdad_Forms_Database();
    $db->create_table(); 
});

// تابع گلوبال برای فراخوانی فرم در قالب
if ( ! function_exists( 'emdad_render_form' ) ) {
    function emdad_render_form( $form_id ) {
        echo Emdad_Forms_Frontend::render( $form_id );
    }
}

// راه‌اندازی ماژول‌ها
Emdad_Forms_Ajax::init();
Emdad_Forms_Admin::init();
Emdad_Forms_Frontend::init();