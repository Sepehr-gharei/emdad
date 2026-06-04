<?php
/**
 * EmdadCamera Login & Notify
 * این فایل را در functions.php قالب خود include کنید:
 *   require_once get_template_directory() . '/emdadcamera-login/emdadcamera-login.php';

 * تنظیمات لینک‌های شرایط و حریم خصوصی:
 *   Options: emdadcamera_terms_url / emdadcamera_privacy_url
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EMDADCAMERA_LOGIN_VERSION', '2.0.0');
define('EMDADCAMERA_LOGIN_DIR', get_template_directory() . '/includes/emdadcamera-login/');
define('EMDADCAMERA_LOGIN_URL', get_template_directory_uri() . '/includes/emdadcamera-login/');
define('EMDADCAMERA_LOGIN_REDIRECT_COOKIE', 'emdadcamera_otp_login_redirect_to');

// ==================== توابع لاگ ====================

function emdadcamera_notify_is_file_logging_enabled() {
    return (int) get_option('emdadcamera_notify_enable_file_logs', 0) === 1;
}

function emdadcamera_notify_get_log_dir() {
    $upload = wp_upload_dir();
    $dir = trailingslashit($upload['basedir']) . 'emdadcamera/logs/';
    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }
    return $dir;
}

function emdadcamera_notify_get_log_file() {
    return emdadcamera_notify_get_log_dir() . 'emdadcamera-notify.log';
}

function emdadcamera_notify_write_log($channel, $recipient, $message, $event_key, $order_id, $status, $provider_response = '') {
    if (!emdadcamera_notify_is_file_logging_enabled()) {
        return false;
    }

    $file = emdadcamera_notify_get_log_file();
    $record = array(
        'time' => current_time('mysql'),
        'channel' => (string) $channel,
        'recipient' => (string) $recipient,
        'event_key' => (string) $event_key,
        'order_id' => (int) $order_id,
        'status' => (string) $status,
        'message' => (string) $message,
        'provider_response' => is_scalar($provider_response) ? (string) $provider_response : wp_json_encode($provider_response, JSON_UNESCAPED_UNICODE),
    );
    $line = wp_json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!$line) {
        $line = print_r($record, true);
    }
    file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    return true;
}

function emdadcamera_notify_read_logs($limit = 100) {
    $file = emdadcamera_notify_get_log_file();
    if (!file_exists($file)) {
        return array();
    }
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines) || empty($lines)) {
        return array();
    }
    $lines = array_slice($lines, -1 * max(1, (int) $limit));
    $lines = array_reverse($lines);
    $logs = array();
    foreach ($lines as $line) {
        $item = json_decode($line, true);
        if (!is_array($item)) {
            $item = array(
                'time' => '',
                'channel' => '',
                'recipient' => '',
                'event_key' => '',
                'order_id' => 0,
                'status' => '',
                'message' => (string) $line,
                'provider_response' => '',
            );
        }
        $logs[] = (object) $item;
    }
    return $logs;
}

function emdadcamera_notify_clear_logs() {
    $file = emdadcamera_notify_get_log_file();
    if (file_exists($file)) {
        file_put_contents($file, '');
    }
}

// ==================== توابع کاربری ====================

function emdadcamera_otp_login_is_login_query() {
    return isset($_GET['login']);
}

function emdadcamera_otp_login_get_myaccount_url() {
    if (function_exists('wc_get_page_permalink')) {
        $url = wc_get_page_permalink('myaccount');
        if (!empty($url)) {
            return $url;
        }
    }
    return home_url('/my-account/');
}

function emdadcamera_otp_login_get_mobile_meta_key() {
    $key = trim((string) get_option('emdadcamera_otp_login_digits_mobile_meta', 'digits_phone'));
    return $key !== '' ? $key : 'digits_phone';
}

function emdadcamera_notify_mobile_variants($mobile) {
    $mobile = trim((string) $mobile);
    if ($mobile === '') {
        return array();
    }

    $clean = preg_replace('/[^\d\+]/', '', $mobile);
    $variants = array($mobile, $clean);

    $numeric = ltrim($clean, '+');
    if ($numeric !== '') {
        $variants[] = $numeric;
    }

    if (strpos($numeric, '0098') === 0) {
        $tail = substr($numeric, 4);
        if ($tail !== '') {
            $variants[] = '98' . $tail;
            $variants[] = '+98' . $tail;
            $variants[] = '0' . $tail;
            $variants[] = $tail;
        }
    }

    if (strpos($numeric, '98') === 0 && strlen($numeric) >= 12) {
        $tail = substr($numeric, 2);
        if ($tail !== '') {
            $variants[] = '98' . $tail;
            $variants[] = '+98' . $tail;
            $variants[] = '0' . $tail;
            $variants[] = $tail;
        }
    }

    if (strpos($numeric, '0') === 0 && strlen($numeric) >= 11) {
        $tail = substr($numeric, 1);
        if ($tail !== '') {
            $variants[] = '0' . $tail;
            $variants[] = $tail;
            $variants[] = '98' . $tail;
            $variants[] = '+98' . $tail;
        }
    }

    if (preg_match('/^9\d{9}$/', $numeric)) {
        $variants[] = $numeric;
        $variants[] = '0' . $numeric;
        $variants[] = '98' . $numeric;
        $variants[] = '+98' . $numeric;
    }

    $variants = array_values(array_unique(array_filter(array_map('trim', $variants))));
    return $variants;
}

function emdadcamera_notify_find_user_by_mobile($mobile) {
    $variants = emdadcamera_notify_mobile_variants($mobile);
    if (empty($variants)) {
        return false;
    }

    $meta_key = emdadcamera_otp_login_get_mobile_meta_key();
    $meta_query = array('relation' => 'OR');
    foreach ($variants as $variant) {
        $meta_query[] = array(
            'key' => $meta_key,
            'value' => $variant,
            'compare' => '=',
        );
        $meta_query[] = array(
            'key' => 'billing_phone',
            'value' => $variant,
            'compare' => '=',
        );
    }

    $users = get_users(array(
        'number' => 1,
        'meta_query' => $meta_query,
        'fields' => 'all',
    ));

    if (!empty($users) && $users[0] instanceof WP_User) {
        return $users[0];
    }

    return false;
}

function emdadcamera_notify_get_password_login_mode() {
    $mode = sanitize_key((string) get_option('emdadcamera_notify_password_login_mode', 'all'));
    $allowed = array('username_email','email_mobile','mobile_username','mobile_only','all');
    return in_array($mode, $allowed, true) ? $mode : 'all';
}

function emdadcamera_notify_get_password_login_placeholder() {
    switch (emdadcamera_notify_get_password_login_mode()) {
        case 'username_email': return 'نام کاربری یا ایمیل';
        case 'email_mobile': return 'ایمیل یا شماره تلفن';
        case 'mobile_username': return 'شماره تلفن یا نام کاربری';
        case 'mobile_only': return 'شماره تلفن';
        default: return 'نام کاربری';
    }
}

function emdadcamera_otp_login_set_cookie($name, $value, $expires) {
    $path = (defined('COOKIEPATH') && COOKIEPATH) ? COOKIEPATH : '/';
    $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
    $secure = is_ssl();
    $httponly = true;

    setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);

    if ($expires > time()) {
        $_COOKIE[$name] = $value;
    } else {
        unset($_COOKIE[$name]);
    }
}

function emdadcamera_otp_login_consume_redirect_target() {
    $fallback = emdadcamera_otp_login_get_myaccount_url();

    if (!empty($_COOKIE[EMDADCAMERA_LOGIN_REDIRECT_COOKIE])) {
        $raw = wp_unslash($_COOKIE[EMDADCAMERA_LOGIN_REDIRECT_COOKIE]);
        $target = is_string($raw) ? esc_url_raw($raw) : '';
        emdadcamera_otp_login_set_cookie(EMDADCAMERA_LOGIN_REDIRECT_COOKIE, '', time() - 3600);

        $home = home_url('/');
        if ($target && strpos($target, $home) === 0) {
            return $target;
        }
    }

    return $fallback;
}

function emdadcamera_otp_login_sync_user_mobile($user_id, $mobile = '') {
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return;
    }

    $meta_key = emdadcamera_otp_login_get_mobile_meta_key();
    $current_meta = trim((string) get_user_meta($user_id, $meta_key, true));
    $billing_phone = trim((string) get_user_meta($user_id, 'billing_phone', true));
    $mobile_in = trim((string) $mobile);

    if ($current_meta !== '' && $billing_phone !== '') {
        return;
    }

    if ($current_meta !== '' && $billing_phone === '') {
        update_user_meta($user_id, 'billing_phone', $current_meta);
        return;
    }

    if ($current_meta === '' && $billing_phone !== '') {
        update_user_meta($user_id, $meta_key, $billing_phone);
        return;
    }

    if ($mobile_in === '') {
        return;
    }

    update_user_meta($user_id, $meta_key, $mobile_in);
    update_user_meta($user_id, 'billing_phone', $mobile_in);
}

function emdadcamera_notify_get_admin_mobiles() {
    $raw = (string) get_option('emdadcamera_notify_admin_mobiles', '');
    if ($raw === '') {
        return array();
    }

    $parts = preg_split('/[\n,]+/', $raw);
    $mobiles = array();

    foreach ((array) $parts as $part) {
        $mobile = trim((string) $part);
        if ($mobile !== '') {
            $mobiles[] = $mobile;
        }
    }

    return array_values(array_unique($mobiles));
}

function emdadcamera_notify_get_customer_mobile($order) {
    if (!$order || !is_a($order, 'WC_Order')) {
        return '';
    }

    $billing_phone = trim((string) $order->get_billing_phone());
    if ($billing_phone !== '') {
        return $billing_phone;
    }

    $user_id = (int) $order->get_user_id();
    if ($user_id > 0) {
        $meta_key = emdadcamera_otp_login_get_mobile_meta_key();
        $meta_mobile = trim((string) get_user_meta($user_id, $meta_key, true));
        if ($meta_mobile !== '') {
            return $meta_mobile;
        }
    }

    return '';
}

function emdadcamera_notify_get_order_placeholders($order) {
    if (!$order || !is_a($order, 'WC_Order')) {
        return array();
    }

    $status = method_exists($order, 'get_status') ? wc_get_order_status_name($order->get_status()) : '';
    $name = trim($order->get_formatted_billing_full_name());
    if ($name === '') {
        $name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    }

    return array(
        '{order_id}' => $order->get_order_number(),
        '{order_status}' => $status,
        '{customer_name}' => $name,
        '{customer_phone}' => emdadcamera_notify_get_customer_mobile($order),
        '{order_total}' => wp_strip_all_tags($order->get_formatted_order_total()),
        '{payment_method}' => (string) $order->get_payment_method_title(),
        '{site_name}' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    );
}

function emdadcamera_notify_render_message($template, $order) {
    $message = (string) $template;
    $placeholders = emdadcamera_notify_get_order_placeholders($order);
    if (!empty($placeholders)) {
        $message = strtr($message, $placeholders);
    }
    return trim(wp_strip_all_tags($message));
}

function emdadcamera_notify_send_sms_message($mobile, $message, $event_key = '', $order_id = 0, $channel = 'sms') {
    $mobile = trim((string) $mobile);
    $message = trim((string) $message);
    $api_key = trim((string) get_option('otp_login_sms_api_key', ''));
    $line_number = trim((string) get_option('emdadcamera_notify_sms_line_number', ''));

    if ($mobile === '' || $message === '') {
        emdadcamera_notify_write_log($channel, $mobile, $message, $event_key, $order_id, 'failed', 'شماره یا متن پیام خالی است.');
        return array('success' => false, 'message' => 'شماره یا متن پیام خالی است.');
    }

    if ($api_key === '') {
        emdadcamera_notify_write_log($channel, $mobile, $message, $event_key, $order_id, 'failed', 'کلید API پیامک تنظیم نشده است.');
        return array('success' => false, 'message' => 'کلید API پیامک تنظیم نشده است.');
    }

    $payload = array(
        'messageText' => $message,
        'mobiles' => array($mobile),
        'sendDateTime' => null,
    );

    if ($line_number !== '') {
        $payload['lineNumber'] = is_numeric($line_number) ? (int) $line_number : $line_number;
    }

    $response = wp_remote_post('https://api.sms.ir/v1/send/bulk', array(
        'headers' => array(
            'X-API-KEY' => $api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ),
        'body' => wp_json_encode($payload),
        'timeout' => 20,
    ));

    if (is_wp_error($response)) {
        $error = $response->get_error_message();
        emdadcamera_notify_write_log($channel, $mobile, $message, $event_key, $order_id, 'failed', $error);
        return array('success' => false, 'message' => $error);
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($code !== 200) {
        $error_message = !empty($data['message']) ? $data['message'] : ('HTTP ' . $code);
        emdadcamera_notify_write_log($channel, $mobile, $message, $event_key, $order_id, 'failed', $body);
        return array('success' => false, 'message' => $error_message);
    }

    if (!is_array($data) || (int) ($data['status'] ?? 0) !== 1) {
        $error_message = is_array($data) && !empty($data['message']) ? $data['message'] : 'ارسال پیام ناموفق بود.';
        emdadcamera_notify_write_log($channel, $mobile, $message, $event_key, $order_id, 'failed', $body);
        return array('success' => false, 'message' => $error_message);
    }

    emdadcamera_notify_write_log($channel, $mobile, $message, $event_key, $order_id, 'success', $body);
    return array('success' => true, 'message' => (string) ($data['message'] ?? 'موفق'), 'data' => $data['data'] ?? array());
}

// ==================== Redirect — دو پورتال لاگین ====================

/**
 * تابع کمکی برای رندر صفحه لاگین کامل
 */
function emdadcamera_render_full_page($body_class, $content) {
    nocache_headers();
    status_header(200);

    $charset = esc_attr(get_bloginfo('charset'));
    $lang    = get_language_attributes();

    echo "<!doctype html><html {$lang}><head>";
    echo "<meta charset=\"{$charset}\">";
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="robots" content="noindex,nofollow">';
    wp_head();
    echo "</head><body class=\"{$body_class} ec-login-body\">";
    echo $content;
    wp_footer();
    echo '</body></html>';
    exit;
}

/**
 * ?login → پورتال کارفرما / مشتریان
 */
add_action('template_redirect', function () {
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) return;

    if (!isset($_GET['login'])) return;

    if (is_user_logged_in()) {
        wp_safe_redirect(emdadcamera_otp_login_get_myaccount_url());
        exit;
    }

    if (empty($_COOKIE[EMDADCAMERA_LOGIN_REDIRECT_COOKIE])) {
        emdadcamera_otp_login_set_cookie(
            EMDADCAMERA_LOGIN_REDIRECT_COOKIE,
            emdadcamera_otp_login_get_myaccount_url(),
            time() + 600
        );
    }

    $form = new EmdadCamera_Login_Form();
    emdadcamera_render_full_page(
        'ec-customer-page',
        $form->render_customer_form()
    );
}, 0);

/**
 * ?staff-login → پورتال پرسنل
 */
add_action('template_redirect', function () {
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) return;

    if (!isset($_GET['staff-login'])) return;

    if (is_user_logged_in()) {
        wp_safe_redirect(admin_url());
        exit;
    }

    $form = new EmdadCamera_Login_Form();
    emdadcamera_render_full_page(
        'ec-staff-page',
        $form->render_staff_form()
    );
}, 0);

/**
 * ریدایرکت my-account به پورتال کارفرما
 */
add_action('template_redirect', function () {
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) return;

    if (function_exists('is_account_page') && is_account_page() && !is_user_logged_in()) {
        emdadcamera_otp_login_set_cookie(
            EMDADCAMERA_LOGIN_REDIRECT_COOKIE,
            emdadcamera_otp_login_get_myaccount_url(),
            time() + 600
        );
        wp_safe_redirect(home_url('/?login'));
        exit;
    }
}, 1);

// ==================== بارگذاری فایل‌های دیگر ====================

require_once EMDADCAMERA_LOGIN_DIR . 'includes/class-emdadcamera-login-db.php';
require_once EMDADCAMERA_LOGIN_DIR . 'includes/class-emdadcamera-login-ajax.php';
require_once EMDADCAMERA_LOGIN_DIR . 'includes/class-emdadcamera-login-form.php';
require_once EMDADCAMERA_LOGIN_DIR . 'includes/class-emdadcamera-notify-admin.php';
require_once EMDADCAMERA_LOGIN_DIR . 'includes/class-emdadcamera-notify-woo-alerts.php';

// ==================== راه‌اندازی ====================

add_action('after_setup_theme', 'emdadcamera_login_init');
function emdadcamera_login_init() {
    EmdadCamera_Login_DB::ensure_table();
    EmdadCamera_Login_DB::cleanup_expired_otps();

    new EmdadCamera_Login_Ajax();
    new EmdadCamera_Login_Form();
    new EmdadCamera_Notify_Woo_Alerts();

    if (is_admin()) {
        new EmdadCamera_Notify_Admin();
    }
}

// ==================== Enqueue Scripts ====================

function emdadcamera_login_enqueue_scripts() {
    wp_enqueue_script(
        'emdadcamera-login-js',
        EMDADCAMERA_LOGIN_URL . 'assets/js/emdadcamera-login.js',
        array('jquery'),
        EMDADCAMERA_LOGIN_VERSION,
        true
    );
    wp_enqueue_style(
        'emdadcamera-login-css',
        EMDADCAMERA_LOGIN_URL . 'assets/css/emdadcamera-login.css',
        array(),
        EMDADCAMERA_LOGIN_VERSION
    );

    wp_localize_script('emdadcamera-login-js', 'otp_login_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('otp_login_nonce'),
        'auto_verify' => (bool) get_option('emdadcamera_otp_login_auto_verify_otp', 1),
    ));
}
add_action('wp_enqueue_scripts', 'emdadcamera_login_enqueue_scripts');

// ==================== پروفایل کاربر ====================

add_action('show_user_profile', 'emdadcamera_otp_login_show_extra_profile_fields');
add_action('edit_user_profile', 'emdadcamera_otp_login_show_extra_profile_fields');
function emdadcamera_otp_login_show_extra_profile_fields($user) {
    $meta_key = emdadcamera_otp_login_get_mobile_meta_key();
    ?>
    <h3>اطلاعات EmdadCamera Login / Digits</h3>
    <table class="form-table">
        <tr>
            <th><label for="emdadcamera_otp_login_mobile_meta">شماره موبایل</label></th>
            <td>
                <input type="tel" name="emdadcamera_otp_login_mobile_meta" id="emdadcamera_otp_login_mobile_meta" value="<?php echo esc_attr(get_user_meta($user->ID, $meta_key, true)); ?>" class="regular-text" />
                <p class="description">روی متای انتخاب‌شده و billing_phone ذخیره می‌شود.</p>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'emdadcamera_otp_login_save_extra_profile_fields');
add_action('edit_user_profile_update', 'emdadcamera_otp_login_save_extra_profile_fields');
function emdadcamera_otp_login_save_extra_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    $raw_mobile = isset($_POST['emdadcamera_otp_login_mobile_meta']) ? sanitize_text_field(wp_unslash($_POST['emdadcamera_otp_login_mobile_meta'])) : '';
    emdadcamera_otp_login_sync_user_mobile($user_id, $raw_mobile);
}

// ==================== توابع عمومی رندر فرم‌ها ====================

/** فرم کارفرما (پیش‌فرض) */
function emdadcamera_render_login_form() {
    $form = new EmdadCamera_Login_Form();
    return $form->render_customer_form();
}

/** فرم پرسنل */
function emdadcamera_render_staff_login_form() {
    $form = new EmdadCamera_Login_Form();
    return $form->render_staff_form();
}

// ==================== ایجاد صفحات وردپرس (اولین بار) ====================

/**
 * این تابع صفحات «ورود پرسنل» و «ورود کارفرما» را در وردپرس می‌سازد
 * اگر صفحات موجود نباشند. فقط یک‌بار اجرا می‌شود.
 * برای اجرای دستی: add_action('init', 'emdadcamera_create_login_pages');
 */
function emdadcamera_create_login_pages() {
    /* صفحه ورود پرسنل */
    $staff_page_option = 'emdadcamera_staff_page_id';
    $existing_staff    = (int) get_option($staff_page_option, 0);

    if (!$existing_staff || !get_post($existing_staff)) {
        $staff_id = wp_insert_post([
            'post_title'   => 'ورود پرسنل',
            'post_name'    => 'staff-login',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '[emdadcamera_staff_login]',
        ]);
        if ($staff_id && !is_wp_error($staff_id)) {
            update_option($staff_page_option, $staff_id);
        }
    }

    /* صفحه ورود کارفرما */
    $customer_page_option = 'emdadcamera_customer_page_id';
    $existing_customer    = (int) get_option($customer_page_option, 0);

    if (!$existing_customer || !get_post($existing_customer)) {
        $customer_id = wp_insert_post([
            'post_title'   => 'ورود کارفرما',
            'post_name'    => 'employer-login',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '[emdadcamera_customer_login]',
        ]);
        if ($customer_id && !is_wp_error($customer_id)) {
            update_option($customer_page_option, $customer_id);
        }
    }
}

/* فقط یک‌بار اجرا شود — پس از فعال‌سازی قالب */
if (!get_option('emdadcamera_pages_created', false)) {
    add_action('after_setup_theme', function () {
        emdadcamera_create_login_pages();
        update_option('emdadcamera_pages_created', true);
    }, 99);
}
