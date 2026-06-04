<?php
if (!defined('ABSPATH')) {
    exit;
}

class EmdadCamera_Login_Ajax {
    public static function proxy_send_otp_static() {
        $instance = new self();
        $instance->send_otp();
    }

    public function __construct() {
        add_action('wp_ajax_nopriv_send_otp', [$this, 'send_otp']);
        add_action('wp_ajax_send_otp', [$this, 'send_otp']);
        add_action('wp_ajax_nopriv_send_otp_email', [$this, 'send_otp_email']);
        add_action('wp_ajax_send_otp_email', [$this, 'send_otp_email']);
        add_action('wp_ajax_nopriv_verify_otp', [$this, 'verify_otp']);
        add_action('wp_ajax_verify_otp', [$this, 'verify_otp']);
    }

    public function send_otp() {
        check_ajax_referer('otp_login_nonce', 'nonce');

        $mobile = isset($_POST['mobile']) ? sanitize_text_field(wp_unslash($_POST['mobile'])) : '';
        $mobile = trim($mobile);

        if ($mobile === '') {
            wp_send_json_error('شماره موبایل الزامی است.');
        }

        $api_key = trim((string) get_option('otp_login_sms_api_key'));
        $template_id = trim((string) get_option('otp_login_sms_template_id'));

        if ($api_key === '' || $template_id === '') {
            emdadcamera_notify_write_log('otp', $mobile, 'OTP config incomplete', 'otp_send', 0, 'failed', 'api_key/template_id missing');
            wp_send_json_error('تنظیمات SMS کامل نیست. لطفا با مدیر تماس بگیرید.');
        }

        $otp = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $body = [
            'mobile' => $mobile,
            'templateId' => is_numeric($template_id) ? (int) $template_id : $template_id,
            'parameters' => [
                ['name' => 'Code', 'value' => $otp],
                ['name' => 'OTP', 'value' => $otp],
            ],
        ];

        emdadcamera_notify_write_log('otp', $mobile, 'OTP request', 'otp_send', 0, 'pending', wp_json_encode($body, JSON_UNESCAPED_UNICODE));

        $response = wp_remote_post('https://api.sms.ir/v1/send/verify', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-API-KEY' => $api_key,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            emdadcamera_notify_write_log('otp', $mobile, 'OTP transport error', 'otp_send', 0, 'failed', $response->get_error_message());
            wp_send_json_error('خطا در ارسال پیامک: ' . $response->get_error_message());
        }

        $http_code = (int) wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($http_code === 200 && isset($data['status']) && (int) $data['status'] === 1) {
            $store_result = EmdadCamera_Login_DB::insert_otp($mobile, $otp);
            emdadcamera_notify_write_log('otp', $mobile, 'OTP sent', 'otp_send', 0, 'success', $response_body);
            emdadcamera_notify_write_log('otp', $mobile, 'OTP store result', 'otp_store', 0, !empty($store_result['success']) ? 'success' : 'failed', $store_result);

            if (empty($store_result['success'])) {
                wp_send_json_error('کد ارسال شد ولی در ذخیره‌سازی داخلی مشکل به وجود آمد.');
            }
            wp_send_json_success('کد OTP با موفقیت ارسال شد.');
        }

        $message = 'خطا در ارسال پیامک.';
        if (!empty($data['message'])) {
            $message = $data['message'];
        } elseif ($response_body !== '') {
            $message = $response_body;
        }

        emdadcamera_notify_write_log('otp', $mobile, 'OTP failed', 'otp_send', 0, 'failed', $response_body);
        wp_send_json_error('خطا در ارسال پیامک: ' . $message . ' (HTTP ' . $http_code . ')');
    }

    /* ────────────────────────────────────────────
       ارسال OTP از طریق ایمیل
    ──────────────────────────────────────────── */
    public function send_otp_email() {
        check_ajax_referer('otp_login_nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $email = trim($email);

        if ($email === '' || !is_email($email)) {
            wp_send_json_error('آدرس ایمیل نامعتبر است.');
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            wp_send_json_error('کاربری با این ایمیل یافت نشد.');
        }

        $otp = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // ذخیره OTP با کلید ایمیل در دیتابیس
        $store_result = EmdadCamera_Login_DB::insert_otp($email, $otp);

        if (empty($store_result['success'])) {
            emdadcamera_notify_write_log('otp', $email, 'OTP email store failed', 'otp_store', 0, 'failed', $store_result);
            wp_send_json_error('خطا در ذخیره‌سازی کد تأیید.');
        }

        $site_name = get_bloginfo('name');
        $subject   = 'کد تأیید ورود به ' . $site_name;
        $body      = sprintf(
            "سلام %s،\n\nکد تأیید ورود شما: %s\n\nاین کد ۵ دقیقه اعتبار دارد.\n\n%s",
            esc_html($user->display_name),
            $otp,
            $site_name
        );

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        $sent    = wp_mail($email, $subject, $body, $headers);

        if (!$sent) {
            emdadcamera_notify_write_log('otp', $email, 'OTP email send failed', 'otp_send', 0, 'failed', 'wp_mail returned false');
            wp_send_json_error('خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.');
        }

        emdadcamera_notify_write_log('otp', $email, 'OTP email sent', 'otp_send', 0, 'success', 'wp_mail success');
        wp_send_json_success('کد OTP به ایمیل شما ارسال شد.');
    }

    public function verify_otp() {
        check_ajax_referer('otp_login_nonce', 'nonce');

        $mobile = isset($_POST['mobile']) ? sanitize_text_field(wp_unslash($_POST['mobile'])) : '';
        $mobile = trim($mobile);
        $otp = isset($_POST['otp']) ? sanitize_text_field(wp_unslash($_POST['otp'])) : '';

        if ($mobile === '' || $otp === '') {
            wp_send_json_error('شناسه و کد OTP الزامی هستند.');
        }

        $is_email = is_email($mobile); // ممکن است identifier ایمیل باشد

        emdadcamera_notify_write_log('otp', $mobile, 'OTP verify request', 'otp_verify_request', 0, 'pending', array(
            'identifier' => $mobile,
            'via'        => $is_email ? 'email' : 'mobile',
            'submitted_code_length' => strlen((string) $otp),
        ));

        $verify = EmdadCamera_Login_DB::verify_otp($mobile, $otp);
        emdadcamera_notify_write_log('otp', $mobile, 'OTP verify result', 'otp_verify', 0, !empty($verify['success']) ? 'success' : 'failed', $verify);

        if (empty($verify['success'])) {
            wp_send_json_error('کد OTP نامعتبر یا منقضی شده است.');
        }

        // پیدا کردن کاربر — هم با موبایل هم با ایمیل
        $user = $is_email
            ? get_user_by('email', $mobile)
            : emdadcamera_notify_find_user_by_mobile($mobile);

        if ($user) {
            emdadcamera_notify_write_log('otp', $mobile, 'OTP login user resolved', 'otp_login', 0, 'success', array(
                'user_id'    => (int) $user->ID,
                'user_login' => (string) $user->user_login,
            ));
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            if (!$is_email) {
                emdadcamera_otp_login_sync_user_mobile($user->ID, $mobile);
            }
            do_action('wp_login', $user->user_login, $user);

            wp_send_json_success([
                'action'   => 'login',
                'message'  => 'با موفقیت وارد شدید.',
                'redirect' => emdadcamera_otp_login_consume_redirect_target(),
            ]);
        }

        emdadcamera_notify_write_log('otp', $mobile, 'OTP verified but user not found', 'otp_register_needed', 0, 'failed', array('identifier' => $mobile));

        wp_send_json_success([
            'action'  => 'register',
            'message' => 'لطفا ثبت نام خود را تکمیل کنید.',
        ]);
    }

    public function send_login_otp_code() {
    // شماره موبایل یا ایمیل دریافت شده را ایمن‌سازی کنید
    $user_contact = sanitize_text_field($_POST['contact_info']);
    
    // یک نام یکتا برای کشِ زمان‌دار این کاربر می‌سازیم
    $transient_name = 'emdad_otp_lock_' . md5($user_contact);

    // بررسی اینکه آیا هنوز تایمر این کاربر فعال است یا خیر
    if ( get_transient( $transient_name ) ) {
        wp_send_json_error( array( 
            'message' => 'لطفاً تا پایان زمان دو دقیقه‌ای صبر کنید.' 
        ) );
        return;
    }

    // --- کدهای مربوط به وب‌سرویس پیامک خود را اینجا قرار دهید ---
    // $sms_result = ...

    // پس از ارسال موفق کد، محدودیت ۱۲۰ ثانیه‌ای را در دیتابیس ثبت می‌کنیم
    set_transient( $transient_name, true, 120 );

    wp_send_json_success( array( 
        'message' => 'کد با موفقیت ارسال شد. لطفاً ۲ دقیقه صبر کنید.' 
    ) );
}
}
