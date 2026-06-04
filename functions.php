<?php
class emdadcamera_Setup_Setup {
    public function __construct() {
        add_action('after_setup_theme', [$this, 'Add_Theme_Support']);
        add_action('wp_enqueue_scripts', [$this, 'Register_Script'], 9999);
        $this->Init();
    }

    public function Init() {
        $this->Defines();
        $this->Includes();
    }

    public function Defines() {
        define("wp_directory", get_template_directory());
        define("wp_directory_uri", get_template_directory_uri());
    }

    public function Includes() {
        require_once wp_directory . '/includes/includes.php';
    }

    public function Add_Theme_Support() {
        // ثبت تمام جایگاه‌های منو برای هدر و فوتر
        register_nav_menus( array(
            'header-main'    => 'مگامنو سربرگ (همبرگری)',
            'header-flat'    => 'منو ساده سربرگ (دسکتاپ)',
            'footer-links'   => 'منو لینک‌های سریع فوتر'
        ) );

        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
    }

    public function Register_Script() {
        $version = get_option('emdadcamera_files_version', '1.0.0'); 
        wp_enqueue_style( 'emdadcamera-custom', get_stylesheet_directory_uri() . '/style.min.css', array(), $version );
        wp_enqueue_style( 'emdadcamera-fancybox', get_stylesheet_directory_uri() .'/assets/css/fancybox.css', array(), $version );
        wp_enqueue_style( 'emdadcamera-swipercss', get_stylesheet_directory_uri() .'/assets/css/swiper.min.css', array(), $version );
        if ( function_exists( 'is_account_page' ) && is_account_page() ) {
            wp_enqueue_style( 'emdadcamera-account', get_stylesheet_directory_uri() . '/assets/css/account.css', array(), $version );
        }
        wp_enqueue_script('swiper', wp_directory_uri . '/assets/js/swiper-bundle.min.js', array('jquery'), $version, true);
        wp_register_script('emdadcamera-lordicon', wp_directory_uri . '/assets/js/lordicon.js', array(), $version, true);
        wp_enqueue_script('emdadcamera-lordicon');
        wp_register_script('emdadcamera-fancybox', wp_directory_uri . '/assets/js/fancybox.umd.js', array(), $version, true);
        wp_enqueue_script('emdadcamera-fancybox');
        wp_register_script('emdadcamera-inc', wp_directory_uri . '/assets/js/inc.js', array('jquery', 'swiper'), $version, true);
        wp_enqueue_script('emdadcamera-inc');
        wp_localize_script('emdadcamera-inc', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'), 
            'nonce'    => wp_create_nonce('ajax-nonce')
        ));

        wp_register_script('search-ajax', wp_directory_uri . '/assets/js/search.js', array('jquery'), $version, true);
        wp_enqueue_script('search-ajax');
        wp_localize_script('search-ajax', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'), 
            'nonce'    => wp_create_nonce('ajax-nonce')
        ));
        
        
        if(is_singular('post')) {
                wp_register_script('like-dislike', wp_directory_uri . '/assets/js/like-dislike.js', array('jquery'), $version, true);
                wp_enqueue_script('like-dislike');
                wp_localize_script('like-dislike', 'ajax_object', array(
                    'ajax_url' => admin_url('admin-ajax.php'), 
                    'nonce'    => wp_create_nonce('ajax-nonce')
                ));
        }
        if(is_page_template('page-blog.php')) {
            wp_register_script('main-ajax', wp_directory_uri . '/assets/js/ajax.js', array('jquery'), $version, true);
            wp_enqueue_script('main-ajax');
            wp_localize_script('main-ajax', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'), 
                'nonce'    => wp_create_nonce('ajax-nonce')
            ));
        }
    }
}
new emdadcamera_Setup_Setup;
