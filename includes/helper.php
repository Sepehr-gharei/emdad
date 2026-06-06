<?php

function emdadcamera_Get_Setting($section, $id) {
	$theme_options = get_option('emdadcamera_main_option', [] );
	$section = $theme_options[$section];
	$id = !empty($section[$id]) ? $section[$id] : '';
	return $id;
}

function emdadcamera_breadcrumbs() {
	if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs();
}


function emdadcamera_optimized_search_filter($query) {
    // فقط در سمت کاربر (نه ادمین) + کوئری اصلی + صفحه جستجو اجرا شود
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        
        // 1. محدود کردن جستجو فقط به پست‌ها و برگه‌ها (حذف محصولات و...)
        $query->set('post_type', array('post', 'page'));

        // 2. کوئری متا برای فیلتر کردن قالب‌های اختصاصی
        // این شرط هم پست‌ها را شامل می‌شود (چون قالب ندارند) و هم برگه‌های پیش‌فرض را
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key'     => '_wp_page_template',
                'compare' => 'NOT EXISTS', // اگر اصلا قالبی ست نشده بود (پست‌ها و برگه‌های ساده)
            ),
            array(
                'key'     => '_wp_page_template',
                'value'   => 'default', // اگر صراحتا روی default بود
            )
        );

        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'emdadcamera_optimized_search_filter');

function emdadcamera_search_posts_per_page($query) {
    if ( $query->is_search() && $query->is_main_query() && !is_admin() ) {
        $query->set( 'posts_per_page', 30 );
    }
}
add_action( 'pre_get_posts', 'emdadcamera_search_posts_per_page' );


// ==================== امنیت لاگین: غیرفعال‌سازی wp-login.php ====================

/**
 * ریدایرکت wp-login.php به صفحه لاگین پرسنل
 * - GET: نمایش فرم لاگین پرسنل
 * - POST (لاگین): بعد از ارسال فرم وردپرس، ریدایرکت
 * - action=logout: بعد از خروج، برگشت به صفحه پرسنل
 */
add_action('login_init', function () {
    $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'login';

    // بعد از logout → به صفحه لاگین پرسنل بفرست
    if ($action === 'loggedout') {
        wp_safe_redirect(home_url('/staff-login/'));
        exit;
    }

    // POST (ارسال فرم وردپرس پیش‌فرض) → اجازه بده پردازش بشه (برای جلوگیری از شکست AJAX)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }

    // هر GET روی wp-login.php → به صفحه پرسنل ریدایرکت کن
    wp_safe_redirect(home_url('/staff-login/'));
    exit;
});

/**
 * بعد از logout موفق، به صفحه لاگین پرسنل بفرست
 */
add_filter('logout_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    return home_url('/staff-login/');
}, 10, 3);

/**
 * اگر کسی مستقیم آدرس wp-login.php تایپ کنه (قبل از init)
 * این hook زودتر اجرا می‌شه
 */
add_action('init', function () {
    if (
        isset($_SERVER['REQUEST_URI']) &&
        strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false &&
        $_SERVER['REQUEST_METHOD'] === 'GET' &&
        !isset($_GET['action'])
    ) {
        wp_safe_redirect(home_url('/staff-login/'));
        exit;
    }
});

/**
 * لینک login را در همه جای وردپرس به صفحه پرسنل تغییر بده
 */
add_filter('login_url', function ($login_url, $redirect, $force_reauth) {
    return home_url('/staff-login/');
}, 10, 3);

/**
 * لینک logout را درست نگه دار (مشکل خروج از ادمین)
 * وردپرس بعد از logout به login_url ریدایرکت می‌کنه — این فیلتر مطمئن می‌شه
 * که logout URL همچنان کار می‌کنه و بعدش به صفحه پرسنل می‌ره
 */
add_filter('logout_url', function ($logout_url, $redirect) {
    // اگر redirect خاصی تعریف نشده، بعد از logout به staff-login بفرست
    if (empty($redirect)) {
        $logout_url = add_query_arg('redirect_to', urlencode(home_url('/staff-login/')), $logout_url);
    }
    return $logout_url;
}, 10, 2);

/**
 * بعد از logout در wp-admin، به صفحه لاگین پرسنل بفرست
 */
add_action('wp_logout', function () {
    if (!headers_sent()) {
        wp_safe_redirect(home_url('/staff-login/'));
        exit;
    }
});

// اضافه کردن پشتیبانی از فایل APK در وردپرس
function add_apk_mime_type( $mimes ) {
    $mimes['apk'] = 'application/vnd.android.package-archive';
    return $mimes;
}
add_filter( 'upload_mimes', 'add_apk_mime_type' );

// بررسی نوع فایل واقعی برای APK
function fix_apk_upload_check( $data, $file, $filename, $mimes ) {
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
    if ( $ext === 'apk' ) {
        $data['ext']  = 'apk';
        $data['type'] = 'application/vnd.android.package-archive';
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'fix_apk_upload_check', 10, 4 );








// ۱. اضافه کردن قابلیت آپلود فایل به فرم تسویه حساب ووکامرس
add_action('wp_footer', 'emdad_checkout_form_enctype');
function emdad_checkout_form_enctype() {
    if (is_checkout()) {
        echo "<script>jQuery(document).ready(function($){ $('form.checkout').attr('enctype', 'multipart/form-data'); });</script>";
    }
}

// ۲. ثبت درگاه‌های پرداخت جدید در ووکامرس
add_filter('woocommerce_payment_gateways', 'emdad_register_custom_gateways');
function emdad_register_custom_gateways($gateways) {
    $gateways[] = 'WC_Gateway_Emdad_Cheque';
    $gateways[] = 'WC_Gateway_Emdad_Amani';
    return $gateways;
}

// ۳. تعریف کلاس‌های درگاه پرداخت (بارگذاری پس از لود شدن پلاگین‌ها)
add_action('plugins_loaded', 'emdad_init_custom_gateways');
function emdad_init_custom_gateways() {
    if (!class_exists('WC_Payment_Gateway')) return;

    // --- کلاس درگاه چک ---
    class WC_Gateway_Emdad_Cheque extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'emdad_cheque';
            $this->has_fields = true;
            $this->method_title = 'خرید با چک';
            $this->method_description = 'ثبت سفارش با دریافت تصویر چک از مشتری.';
            $this->title = 'پرداخت با چک';
            $this->description = 'لطفاً شماره صیادی و تصویر چک خود را بارگذاری کنید. پس از بررسی با شما تماس خواهیم گرفت.';
            $this->init_form_fields();
            $this->init_settings();
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        // فرمی که در صفحه پرداخت باز می‌شود
        public function payment_fields() {
            if ($this->description) echo wpautop(wptexturize($this->description));
            echo '<div class="emdad-gateway-form">';
            echo '<p class="form-row form-row-wide"><label>شماره صیادی چک <abbr class="required" title="ضروری">*</abbr></label><input type="text" class="input-text" name="emdad_cheque_number" id="emdad_cheque_number" required></p>';
            echo '<p class="form-row form-row-wide"><label>تصویر چک <abbr class="required" title="ضروری">*</abbr></label><input type="file" name="emdad_cheque_image" id="emdad_cheque_image" accept="image/*" required style="padding:10px 0;"></p>';
            echo '</div>';
        }

        // پردازش هنگام کلیک روی دکمه ثبت سفارش
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            $order->update_status('on-hold', 'در انتظار بررسی تصویر چک ثبت شده.');
            WC()->cart->empty_cart();
            return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
        }
    }

    // --- کلاس درگاه امانی ---
    class WC_Gateway_Emdad_Amani extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'emdad_amani';
            $this->has_fields = true;
            $this->method_title = 'خرید امانی';
            $this->method_description = 'ثبت سفارش به صورت امانی.';
            $this->title = 'خرید امانی';
            $this->description = 'با انتخاب این گزینه، درخواست خرید امانی شما ثبت شده و کارشناسان ما با شما تماس خواهند گرفت.';
            $this->init_form_fields();
            $this->init_settings();
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function payment_fields() {
            if ($this->description) echo wpautop(wptexturize($this->description));
            echo '<div class="emdad-gateway-form">';
            echo '<p class="form-row form-row-wide"><label>توضیحات تکمیلی (اختیاری)</label><textarea name="emdad_amani_notes" class="input-text" rows="2"></textarea></p>';
            echo '</div>';
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            $order->update_status('on-hold', 'درخواست خرید امانی ثبت شد.');
            WC()->cart->empty_cart();
            return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
        }
    }
}

// ۴. اعتبارسنجی آپلود فایل چک (جلوگیری از ثبت سفارش بدون آپلود عکس)
add_action('woocommerce_checkout_process', 'emdad_validate_custom_gateways');
function emdad_validate_custom_gateways() {
    if ($_POST['payment_method'] === 'emdad_cheque') {
        if (empty($_POST['emdad_cheque_number'])) {
            wc_add_notice('لطفاً شماره صیادی چک را وارد کنید.', 'error');
        }
        if (empty($_FILES['emdad_cheque_image']['name'])) {
            wc_add_notice('لطفاً تصویر چک را بارگذاری کنید.', 'error');
        } else {
            $file_type = wp_check_filetype($_FILES['emdad_cheque_image']['name']);
            if (!in_array(strtolower($file_type['ext']), ['jpg', 'jpeg', 'png', 'pdf'])) {
                wc_add_notice('فرمت تصویر چک معتبر نیست. لطفاً فایل JPG, PNG یا PDF آپلود کنید.', 'error');
            }
        }
    }
}

// ۵. ذخیره فایل آپلود شده و اطلاعات در دیتابیس سفارش ووکامرس
add_action('woocommerce_checkout_update_order_meta', 'emdad_save_custom_gateways_data', 10, 2);
function emdad_save_custom_gateways_data($order_id, $data) {
    if ($_POST['payment_method'] === 'emdad_cheque') {
        if (!empty($_POST['emdad_cheque_number'])) {
            update_post_meta($order_id, '_emdad_cheque_number', sanitize_text_field($_POST['emdad_cheque_number']));
        }
        if (!empty($_FILES['emdad_cheque_image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $upload = wp_handle_upload($_FILES['emdad_cheque_image'], ['test_form' => false]);
            if (isset($upload['url'])) {
                update_post_meta($order_id, '_emdad_cheque_image_url', esc_url($upload['url']));
            }
        }
    }
    if ($_POST['payment_method'] === 'emdad_amani' && !empty($_POST['emdad_amani_notes'])) {
        update_post_meta($order_id, '_emdad_amani_notes', sanitize_textarea_field($_POST['emdad_amani_notes']));
    }
}

// ۶. ادغام با سیستم پیامک شما (CFB_SMS_Notifier) پس از ثبت سفارش
add_action('woocommerce_checkout_order_processed', 'emdad_send_sms_for_custom_gateways', 10, 3);
function emdad_send_sms_for_custom_gateways($order_id, $posted_data, $order) {
    $method = $order->get_payment_method();
    if (in_array($method, ['emdad_cheque', 'emdad_amani'])) {
        if (class_exists('CFB_SMS_Notifier')) {
            $data = [
                'full_name'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'phone'         => $order->get_billing_phone(),
                'tracking_code' => $order->get_order_number(),
                'date'          => function_exists('jdate') ? jdate('Y/m/d H:i') : current_time('Y/m/d H:i')
            ];
            // استفاده از کلاس شما برای ارسال پیامک
            (new CFB_SMS_Notifier())->send_notifications('order', $data);
        }
    }
}

// ۷. نمایش تصویر چک و اطلاعات در صفحه مدیریت سفارش در پنل ادمین
add_action('woocommerce_admin_order_data_after_billing_address', 'emdad_display_custom_data_in_admin', 10, 1);
function emdad_display_custom_data_in_admin($order) {
    $cheque_number = get_post_meta($order->get_id(), '_emdad_cheque_number', true);
    $cheque_image = get_post_meta($order->get_id(), '_emdad_cheque_image_url', true);
    $amani_notes = get_post_meta($order->get_id(), '_emdad_amani_notes', true);

    if ($cheque_number || $cheque_image || $amani_notes) {
        echo '<h3>اطلاعات اختصاصی پرداخت</h3>';
        if ($cheque_number) echo '<p><strong>شماره صیادی چک:</strong> ' . esc_html($cheque_number) . '</p>';
        if ($cheque_image) echo '<p><strong>تصویر چک:</strong> <br><a href="'.esc_url($cheque_image).'" target="_blank" class="button button-small">مشاهده و دانلود تصویر چک</a></p>';
        if ($amani_notes) echo '<p><strong>توضیحات امانی:</strong> ' . esc_html($amani_notes) . '</p>';
    }
}
?>