<?php
require_once get_template_directory() . "/admin/includes.php";

class Theme_Settings extends Main_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_theme_settings_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action( 'admin_head', [ $this, 'admin_font' ] );
    }

    public function add_theme_settings_page() {
        add_menu_page(
            'تنظیمات قالب',
            'تنظیمات قالب',
            'manage_options',
            'emdadcamera_theme_settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-generic',
            30
        );
    }

    public function render_settings_page() {
        $this->Save_Change();
        echo FlashMessage::get();
        $settings = $this->All_Settings();
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'contact_info';
        echo "<div class='wrap'>"
            . "<nav class='nav-tab-wrapper'>";
            foreach($settings as $id => $menu) {
                $menu_title = $menu['menu'];
                $tab_url = admin_url('admin.php?page=emdadcamera_theme_settings&tab=' . $id);
                $active_class = ($active_tab == $id) ? ' nav-tab-active' : '';
                echo "<a href='$tab_url' class='nav-tab$active_class'>$menu_title</a>";
            }
            echo "</nav>";

            echo "<main class='emdadcamera-main-content-options'>"
                . "<form action='' method='POST' id='setting-form'>";

                    echo "<input type='hidden' name='current_tab' value='$active_tab'>";
                    $this->General_Settings($active_tab);
                    echo "<div class='form-row'>"
                        . "<button type='submit' class='button emdadcamera-submit' name='SaveSetting'>ذخیره تغییرات</button>"
                    . "</div>"
                . "</form>"
            . "</main>"
        . "</div>";
    }

    public function Save_Change() {
        if (isset($_POST['SaveSetting'])) {
            $current_tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'contact_info';
            if ($current_tab) {
                $main_options = get_option('emdadcamera_main_option', []);
    
                $all_settings = $this->All_Settings();
                foreach ($all_settings[$current_tab]['settings'] as $id => $setting) {
                    $type = $setting['type'];
                    $field_value = isset($_POST[$id]) ? $_POST[$id] : '';
    
                    switch ($type) {
                        case 'text':
                        case 'select':
                        case 'color':
                        case 'image-gallery':
                            $value = wp_unslash($field_value);
                            break;
                        
                        case 'file': 
                        case 'image':
                        case 'url':
                            $value = esc_url_raw($field_value);
                            break;
                            
                        case 'textarea':
                        case 'editor':
                            $value = wp_unslash($field_value); 
                            break;
                            
                        case 'repeater':
                        case 'select2':
                            $value = wp_unslash($field_value);
                            break;
                            
                        case 'checkbox':
                            $value = !empty($field_value) ? '1' : '0';
                            break;
                            
                        default:
                            $value = wp_unslash($field_value);
                            break;
                    }

                    $main_options[$current_tab][$id] = $value;
                }
    
                update_option('emdadcamera_main_option', $main_options);

                $this->start_session();
                FlashMessage::add('تنظیمات با موفقیت ذخیره شد.');
                $this->end_session();
            }
        }
    }

    public function enqueue_admin_scripts() {
        $version = get_option('emdadcamera_files_version');
        wp_enqueue_style('style', wp_directory_uri . '/admin/assets/css/style.css', [], $version);
        wp_enqueue_style('select2', wp_directory_uri . '/admin/assets/css/select2.min.css', [], '4.0.13');

        wp_enqueue_media();
        wp_enqueue_script('theme-settings-script', wp_directory_uri . '/admin/assets/js/admin.js', array('jquery'), $version, true);
        wp_localize_script('theme-settings-script', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        wp_enqueue_script('select2', wp_directory_uri . '/admin/assets/js/select2.min.js', array('jquery'), '4.0.13', true);
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script('wp-color-picker-alpha', wp_directory_uri . '/admin/assets/js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), '4.1.0', true);
    }

    private function Code_Validator($code) {
        return str_replace( array('\'', '\"'), '"', $code);
    }

    public function start_session() {
        if(!session_id()) {
            session_start();
        }
    }

    public function end_session() {
        session_write_close();
    }

    public function admin_font() {
        echo '<style type="text/css">@font-face {font-family: "YekanBakh";src: url("'. wp_directory_uri .'/assets/fonts/YekanBakh-VF.woff") format("woff2-variations");font-weight: 100 950;font-style: normal;font-display: swap;}body.rtl, #wpadminbar *:not([class="ab-icon"]), .wp-core-ui, .media-menu, .media-frame *, .media-modal *,.rtl h1, .rtl h2, .rtl h3, .rtl h4, .rtl h5, .rtl h6 {font-family:"YekanBakh" !important;}.php-error #adminmenuback, .php-error #adminmenuwrap {margin-top: 0 !important;}#sub-accordion-section-custom_codes textarea {direction: ltr;}
}</style>'. PHP_EOL;
    }
}

new Theme_Settings;
?>