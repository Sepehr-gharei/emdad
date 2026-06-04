<?php
if (!defined('ABSPATH')) {
    exit;
}

class EmdadCamera_Notify_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('manage_users_columns', [$this, 'add_mobile_column_header']);
        add_filter('manage_users_custom_column', [$this, 'show_mobile_column_content'], 10, 3);
        add_filter('manage_users_sortable_columns', [$this, 'make_mobile_column_sortable']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_post_emdadcamera_notify_send_test_sms', [$this, 'handle_test_sms']);
        add_action('admin_post_emdadcamera_notify_clear_logs', [$this, 'handle_clear_logs']);
        add_action('admin_post_emdadcamera_notify_download_logs', [$this, 'handle_download_logs']);
        add_action('admin_post_emdadcamera_notify_clear_otp_records', [$this, 'handle_clear_otp_records']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'تنظیمات EmdadCamera Login',
            'EmdadCamera Login',
            'manage_options',
            'emdadcamera-login-settings',
            [$this, 'settings_page'],
            'dashicons-smartphone',
            56
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_emdadcamera-login-settings') {
            return;
        }

        wp_enqueue_media();
        $script = <<<'JS'
jQuery(function($){
    $(document).on('click', '.emdadcamera-upload-logo', function(e){
        e.preventDefault();
        var frame = wp.media({
            title: 'انتخاب تصویر',
            button: { text: 'استفاده از این تصویر' },
            multiple: false
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#emdadcamera_otp_login_logo_url').val(attachment.url);
            $('.emdadcamera-logo-preview').attr('src', attachment.url).show();
        });
        frame.open();
    });
});
JS;
        wp_add_inline_script('jquery-core', $script);
    }

    public function register_settings() {
        register_setting('emdadcamera_login_settings', 'otp_login_enable_traditional_login');
        register_setting('emdadcamera_login_settings', 'otp_login_default_role');
        register_setting('emdadcamera_login_settings', 'emdadcamera_otp_login_digits_mobile_meta', ['default' => 'digits_phone']);
        register_setting('emdadcamera_login_settings', 'emdadcamera_otp_login_auto_verify_otp', ['default' => 1]);
        register_setting('emdadcamera_login_settings', 'emdadcamera_notify_password_login_mode', ['default' => 'all']);
        register_setting('emdadcamera_login_settings', 'emdadcamera_notify_enable_file_logs', ['default' => 0]);
        register_setting('emdadcamera_login_settings', 'emdadcamera_notify_keep_otp_records', ['default' => 0]);
        register_setting('emdadcamera_login_settings', 'emdadcamera_notify_otp_record_retention_days', ['default' => 1]);

        register_setting('emdadcamera_login_appearance_settings', 'emdadcamera_otp_login_logo_url');
        register_setting('emdadcamera_login_appearance_settings', 'emdadcamera_otp_login_button_gradient_right', ['default' => '#6D5DF6']);
        register_setting('emdadcamera_login_appearance_settings', 'emdadcamera_otp_login_button_gradient_left', ['default' => '#C445F2']);
        register_setting('emdadcamera_login_appearance_settings', 'emdadcamera_otp_login_font_family', ['default' => '']);
        register_setting('emdadcamera_login_appearance_settings', 'emdadcamera_otp_login_theme_scheme', ['default' => 'light']);

        register_setting('emdadcamera_login_sms_settings', 'otp_login_sms_api_key');
        register_setting('emdadcamera_login_sms_settings', 'otp_login_sms_template_id');
        register_setting('emdadcamera_login_sms_settings', 'emdadcamera_notify_sms_line_number');

        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_admin_mobiles', ['sanitize_callback' => [$this, 'sanitize_textarea']]);
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_customer_new_order_enabled');
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_admin_new_order_enabled');
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_customer_new_order_message', ['sanitize_callback' => [$this, 'sanitize_template']]);
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_admin_new_order_message', ['sanitize_callback' => [$this, 'sanitize_template']]);
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_customer_statuses', ['sanitize_callback' => [$this, 'sanitize_statuses'], 'default' => []]);
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_admin_statuses', ['sanitize_callback' => [$this, 'sanitize_statuses'], 'default' => []]);
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_customer_status_message', ['sanitize_callback' => [$this, 'sanitize_template']]);
        register_setting('emdadcamera_notify_woo_alerts_settings', 'emdadcamera_notify_admin_status_message', ['sanitize_callback' => [$this, 'sanitize_template']]);
    }

    public function sanitize_textarea($value) {
        return trim((string) wp_kses_post($value));
    }

    public function sanitize_template($value) {
        return trim((string) wp_kses_post($value));
    }

    public function sanitize_statuses($value) {
        if (!is_array($value)) {
            return array();
        }
        $sanitized = array();
        foreach ($value as $item) {
            $key = sanitize_key($item);
            if ($key !== '') {
                $sanitized[] = $key;
            }
        }
        return array_values(array_unique($sanitized));
    }

    public function add_mobile_column_header($columns) {
        $columns['emdadcamera_otp_login_mobile'] = 'شماره موبایل';
        return $columns;
    }

    public function show_mobile_column_content($value, $column_name, $user_id) {
        if ('emdadcamera_otp_login_mobile' !== $column_name) {
            return $value;
        }

        $meta_key = emdadcamera_otp_login_get_mobile_meta_key();
        $mobile = get_user_meta($user_id, $meta_key, true);
        if (!$mobile) {
            $mobile = get_user_meta($user_id, 'billing_phone', true);
        }

        return !empty($mobile) ? '<a href="tel:' . esc_attr($mobile) . '">' . esc_html($mobile) . '</a>' : '—';
    }

    public function make_mobile_column_sortable($sortable_columns) {
        $sortable_columns['emdadcamera_otp_login_mobile'] = 'emdadcamera_otp_login_mobile';
        return $sortable_columns;
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>تنظیمات EmdadCamera Login</h1>
            <?php if (!empty($_GET['emdadcamera_notice'])) : ?>
                <div class="notice notice-<?php echo esc_attr(sanitize_key($_GET['emdadcamera_notice'])); ?> is-dismissible"><p><?php echo esc_html(wp_unslash($_GET['emdadcamera_message'] ?? '')); ?></p></div>
            <?php endif; ?>
            <nav class="nav-tab-wrapper">
                <a href="?page=emdadcamera-login-settings" class="nav-tab nav-tab-active">پیامک</a>
            </nav>
            <form method="post" action="options.php">
                <?php settings_fields('emdadcamera_login_sms_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th>کلید API SMS.IR</th>
                        <td><input type="text" name="otp_login_sms_api_key" value="<?php echo esc_attr(get_option('otp_login_sms_api_key')); ?>" class="regular-text ltr"></td>
                    </tr>
                    <tr>
                        <th>کد قالب پیامک OTP</th>
                        <td><input type="text" name="otp_login_sms_template_id" value="<?php echo esc_attr(get_option('otp_login_sms_template_id')); ?>" class="regular-text ltr"></td>
                    </tr>
                    <tr>
                        <th>شماره خط برای اعلان‌ها</th>
                        <td>
                            <input type="text" name="emdadcamera_notify_sms_line_number" value="<?php echo esc_attr(get_option('emdadcamera_notify_sms_line_number')); ?>" class="regular-text ltr">
                            <p class="description">اختیاری</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('ذخیره تنظیمات پیامک'); ?>
            </form>
        </div>
        <?php
    }

    public function handle_test_sms() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        check_admin_referer('emdadcamera_notify_send_test_sms');
        $mobile = isset($_POST['test_mobile']) ? sanitize_text_field(wp_unslash($_POST['test_mobile'])) : '';
        $message = isset($_POST['test_message']) ? sanitize_textarea_field(wp_unslash($_POST['test_message'])) : '';
        $result = emdadcamera_notify_send_sms_message($mobile, $message, 'admin_test', 0);

        $args = array(
            'page' => 'emdadcamera-login-settings',
            'tab' => 'woo-alerts',
            'emdadcamera_notice' => $result['success'] ? 'success' : 'error',
            'emdadcamera_message' => $result['message'],
        );

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }

    public function handle_clear_logs() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        check_admin_referer('emdadcamera_notify_clear_logs');
        emdadcamera_notify_clear_logs();
        wp_safe_redirect(add_query_arg(array('page' => 'emdadcamera-login-settings', 'tab' => 'logs', 'emdadcamera_notice' => 'success', 'emdadcamera_message' => 'لاگ پاک شد.'), admin_url('admin.php')));
        exit;
    }

    public function handle_download_logs() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        check_admin_referer('emdadcamera_notify_download_logs');
        $file = emdadcamera_notify_get_log_file();
        if (!file_exists($file)) {
            wp_die('لاگ پیدا نشد.');
        }
        nocache_headers();
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="emdadcamera-notify.log"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function handle_clear_otp_records() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        check_admin_referer('emdadcamera_notify_clear_otp_records');
        EmdadCamera_Login_DB::clear_all_records();
        wp_safe_redirect(add_query_arg(array('page' => 'emdadcamera-login-settings', 'tab' => 'logs', 'emdadcamera_notice' => 'success', 'emdadcamera_message' => 'رکوردهای OTP حذف شدند.'), admin_url('admin.php')));
        exit;
    }
}
