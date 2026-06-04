<?php
if (!defined('ABSPATH')) { exit; }

/**
 * EmdadCamera Login Form — Dual Portal

 */
class EmdadCamera_Login_Form {

    public function __construct() {
        add_action('wp_ajax_nopriv_register_user',              [$this, 'register_user']);
        add_action('wp_ajax_register_user',                     [$this, 'register_user']);
        add_action('wp_ajax_nopriv_check_username',             [$this, 'check_username']);
        add_action('wp_ajax_check_username',                    [$this, 'check_username']);
        add_action('wp_ajax_nopriv_traditional_login',          [$this, 'traditional_login']);
        add_action('wp_ajax_traditional_login',                 [$this, 'traditional_login']);
        add_action('wp_ajax_nopriv_staff_login',                [$this, 'staff_login']);
        add_action('wp_ajax_staff_login',                       [$this, 'staff_login']);
        add_action('wp_ajax_nopriv_send_password_reset_otp',    [$this, 'send_password_reset_otp']);
        add_action('wp_ajax_send_password_reset_otp',           [$this, 'send_password_reset_otp']);
        add_action('wp_ajax_nopriv_reset_password_with_otp',    [$this, 'reset_password_with_otp']);
        add_action('wp_ajax_reset_password_with_otp',           [$this, 'reset_password_with_otp']);
        /* بررسی ایمیل قبل از ارسال OTP */
        add_action('wp_ajax_nopriv_check_identifier',           [$this, 'check_identifier']);
        add_action('wp_ajax_check_identifier',                  [$this, 'check_identifier']);
        /* ارسال OTP از طریق پیامک (درخواست مجدد) */
        add_action('wp_ajax_nopriv_resend_otp_sms',             [$this, 'resend_otp_sms']);
        add_action('wp_ajax_resend_otp_sms',                    [$this, 'resend_otp_sms']);
        /* Shortcode ها برای صفحات وردپرس */
        add_shortcode('emdadcamera_customer_login',             [$this, 'shortcode_customer']);
        add_shortcode('emdadcamera_staff_login',                [$this, 'shortcode_staff']);
    }

    /* ────────────────────────────────────────────
       Shortcode ها
    ──────────────────────────────────────────── */
    public function shortcode_customer() {
        return $this->render_customer_form();
    }

    public function shortcode_staff() {
        return $this->render_staff_form();
    }

    /* ────────────────────────────────────────────
       فرم کارفرما / مشتری — ?login
    ──────────────────────────────────────────── */
    public function render_customer_form() {
        if (is_user_logged_in()) {
            return '<p>شما وارد شده‌اید.</p>';
        }
        wp_enqueue_style('emdadcamera-login-css');
        wp_enqueue_script('emdadcamera-login-js');

        $logo_url       = esc_url(get_option('emdadcamera_otp_login_logo_url', ''));
        $terms_url      = esc_url(get_option('emdadcamera_terms_url', home_url('/terms/')));
        $privacy_url    = esc_url(get_option('emdadcamera_privacy_url', home_url('/privacy/')));

        ob_start(); ?>
        <div class="ec-page">
        <div class="ec-customer-card" id="ec-customer-wrap">

            <?php /* لوگو */ ?>
            <div class="ec-logo">
                <?php if ($logo_url): ?>
                    <img src="<?php echo $logo_url; ?>" alt="" style="height:48px;width:auto;">
                <?php else: ?>
                    <div class="ec-logo-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 15.2a3.2 3.2 0 100-6.4 3.2 3.2 0 000 6.4z"/><path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                    </div>
                    <span class="ec-logo-name"><?php echo esc_html(get_bloginfo('name')); ?></span>
                <?php endif; ?>
            </div>

            <div class="ec-step active" id="ec-step-identifier">
                <p class="ec-title">ورود کارفرما</p>
                <p class="ec-sub">شماره موبایل یا ایمیل خود را وارد کنید</p>

                <form id="ec-form-identifier">
                    <div class="ec-field">
                        <label>شماره موبایل یا ایمیل</label>
                        <input type="text" id="ec-identifier"
                               placeholder="09123456789 یا example@email.com"
                               required autocomplete="username" dir="ltr">
                    </div>
                    <button type="submit" class="ec-btn ec-btn-primary">
                        ادامه
                    </button>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="ec-btn ec-btn-ghost">بازگشت</a>
                </form>

                <div id="ec-msg-identifier" class="ec-msg"></div>
            </div>

            <?php /* مرحله ۲ — کد OTP */ ?>
            <div class="ec-step" id="ec-step-otp">
                <p class="ec-title">کد تأیید</p>
                <p class="ec-sub" id="ec-otp-sub-text">کد ۴ رقمی ارسال‌شده را وارد کنید</p>

                <form id="ec-form-otp">
                    <div class="ec-otp-wrap">
                        <input type="text" class="ec-otp-digit af-otp-digit" inputmode="numeric" maxlength="1" autocomplete="one-time-code" placeholder="–">
                        <input type="text" class="ec-otp-digit af-otp-digit" inputmode="numeric" maxlength="1" placeholder="–">
                        <input type="text" class="ec-otp-digit af-otp-digit" inputmode="numeric" maxlength="1" placeholder="–">
                        <input type="text" class="ec-otp-digit af-otp-digit" inputmode="numeric" maxlength="1" placeholder="–">
                    </div>
                    <input type="hidden" id="otp-code">
                    <input type="hidden" id="ec-otp-via" value="mobile">
                    <button type="submit" class="ec-btn ec-btn-primary">تأیید و ورود</button>
                    <button type="button" class="ec-btn ec-btn-ghost" id="ec-btn-back-identifier">← تغییر شماره / ایمیل</button>
                </form>

                <!-- دریافت مجدد از طریق پیامک (فقط وقتی OTP با ایمیل رفته نشون داده می‌شه) -->
<div id="ec-resend-sms-wrap" style="display:none; text-align:center; margin-top:16px;">
    <div id="ec-timer-display" class="ec-timer-display"></div>
    <button type="button" id="ec-btn-resend-sms" class="ec-link-btn" disabled>
        ارسال مجدد کد
    </button>
</div>
                <div id="ec-msg-otp" class="ec-msg"></div>
            </div>

            <?php /* مرحله ۳ — ثبت‌نام خودکار */ ?>
            <div class="ec-step" id="ec-step-register">
                <p class="ec-title">در حال ثبت‌نام...</p>
                <p class="ec-sub">حساب کاربری شما به صورت خودکار ایجاد می‌شود</p>
                <div id="ec-msg-register" class="ec-msg"></div>
            </div>

            <div class="ec-card-footer ec-card-footer-terms">
                ورود شما به معنای پذیرش
                <a href="<?php echo $terms_url; ?>" target="_blank">شرایط امداد دوربین</a>
                و
                <a href="<?php echo $privacy_url; ?>" target="_blank">قوانین حریم خصوصی</a>
                است.
            </div>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /* ────────────────────────────────────────────
       فرم پرسنل — ?staff-login
    ──────────────────────────────────────────── */
    public function render_staff_form() {
        if (is_user_logged_in()) {
            return '<p>شما وارد شده‌اید.</p>';
        }
        wp_enqueue_style('emdadcamera-login-css');
        wp_enqueue_script('emdadcamera-login-js');

        $logo_url    = esc_url(get_option('emdadcamera_otp_login_logo_url', ''));
        $terms_url   = esc_url(get_option('emdadcamera_terms_url', home_url('/terms/')));
        $privacy_url = esc_url(get_option('emdadcamera_privacy_url', home_url('/privacy/')));

        ob_start(); ?>
        <div class="ec-page">
        <div class="ec-staff-card">

            <?php /* لوگو */ ?>
            <div class="ec-staff-logo">
                <?php if ($logo_url): ?>
                    <img src="<?php echo $logo_url; ?>" alt="" style="height:36px;width:auto;">
                <?php else: ?>
                    <div class="ec-staff-logo-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                    </div>
                    <div class="ec-staff-logo-text">
                        <span class="ec-staff-logo-name"><?php echo esc_html(get_bloginfo('name')); ?></span>
                        <span class="ec-staff-logo-tag">Staff Portal</span>
                    </div>
                <?php endif; ?>
            </div>

            <p class="ec-staff-title">پنل پرسنل</p>
            <p class="ec-staff-sub">فقط برای دسترسی مجاز</p>

            <form id="ec-form-staff">
                <div class="ec-staff-field">
                    <label>نام کاربری</label>
                    <input type="text" id="ec-staff-username"
                           placeholder="نام کاربری"
                           required autocomplete="username">
                </div>
                <div class="ec-staff-field ec-staff-field-pw">
                    <label>رمز عبور</label>
                    <input type="password" id="ec-staff-password"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="ec-staff-eye-btn" data-target="#ec-staff-password">
                        <?php echo $this->eye_svg(); ?>
                    </button>
                </div>
                <button type="submit" class="ec-staff-btn ec-staff-btn-primary">
                    ورود پرسنل
                </button>
            </form>

            <div id="ec-msg-staff" class="ec-staff-msg"></div>

            <div class="ec-staff-footer ec-staff-footer-terms">
                <a href="<?php echo $terms_url; ?>" target="_blank">شرایط امداد دوربین</a>
                <span>|</span>
                <a href="<?php echo $privacy_url; ?>" target="_blank">قوانین حریم خصوصی</a>
            </div>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /* ────────────────────────────────────────────
       توابع render عمومی
    ──────────────────────────────────────────── */
    public function render_form() {
        return $this->render_customer_form();
    }

    private function eye_svg() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }

    /* ────────────────────────────────────────────
       AJAX — بررسی identifier (موبایل یا ایمیل)
       - موبایل: همیشه قبول می‌شه (اگر ثبت نکرده، می‌ره ثبت‌نام)
       - ایمیل: اگر در سیستم نباشه → خطا با راهنمای موبایل
       - ایمیل ثبت‌شده → OTP به ایمیل
       - موبایل → OTP به پیامک
    ──────────────────────────────────────────── */
    public function check_identifier() {
        check_ajax_referer('otp_login_nonce', 'nonce');

        $identifier = trim(sanitize_text_field(wp_unslash($_POST['identifier'] ?? '')));
        if ($identifier === '') {
            wp_send_json_error('لطفاً شماره موبایل یا ایمیل را وارد کنید.');
        }

        $is_email  = is_email($identifier);
        $is_mobile = (bool) preg_match('/^[0-9\+]{8,20}$/', $identifier);

        if ($is_email) {
            /* بررسی اینکه ایمیل در سیستم وجود دارد */
            $user = get_user_by('email', $identifier);
            if (!$user) {
                wp_send_json_error([
                    'type'    => 'email_not_found',
                    'message' => 'کاربری با این ایمیل یافت نشد. لطفاً با شماره موبایل وارد شوید.',
                ]);
            }
            /* ایمیل وجود دارد — OTP به ایمیل بفرست */
            wp_send_json_success([
                'via'     => 'email',
                'message' => 'کد تأیید به ایمیل شما ارسال شد.',
            ]);
        } elseif ($is_mobile) {
            /* موبایل — OTP به پیامک */
            wp_send_json_success([
                'via'     => 'mobile',
                'message' => 'کد تأیید به شماره شما ارسال شد.',
            ]);
        } else {
            wp_send_json_error('لطفاً یک شماره موبایل یا ایمیل معتبر وارد کنید.');
        }
    }

    /* ────────────────────────────────────────────
       AJAX — ارسال مجدد OTP از طریق پیامک
    ──────────────────────────────────────────── */
    public function resend_otp_sms() {
        check_ajax_referer('otp_login_nonce', 'nonce');

        $identifier = trim(sanitize_text_field(wp_unslash($_POST['identifier'] ?? '')));
        if (!$identifier) {
            wp_send_json_error('شناسه کاربر مشخص نیست.');
        }

        $mobile = '';
        if (is_email($identifier)) {
            /* باید موبایل کاربر رو پیدا کنیم */
            $user = get_user_by('email', $identifier);
            if (!$user) {
                wp_send_json_error('کاربر یافت نشد.');
            }
            $meta_key = emdadcamera_otp_login_get_mobile_meta_key();
            $mobile   = trim((string) get_user_meta($user->ID, $meta_key, true));
            if (!$mobile) {
                $mobile = trim((string) get_user_meta($user->ID, 'billing_phone', true));
            }
            if (!$mobile) {
                wp_send_json_error('شماره موبایلی برای این حساب ثبت نشده.');
            }
        } else {
            $mobile = $identifier;
        }

        $_POST['mobile'] = $mobile;
        EmdadCamera_Login_Ajax::proxy_send_otp_static();
    }

    /* ────────────────────────────────────────────
       AJAX — ورود پرسنل
    ──────────────────────────────────────────── */
    public function staff_login() {
        check_ajax_referer('otp_login_nonce', 'nonce');

        $identifier = trim(sanitize_text_field(wp_unslash($_POST['username'] ?? '')));
        $password   = (string) wp_unslash($_POST['password'] ?? '');

        if ($identifier === '' || $password === '') {
            wp_send_json_error('نام کاربری و رمز عبور الزامی است.');
        }

        $user = $this->resolve_login_user($identifier);
        if (!$user) {
            wp_send_json_error('اطلاعات ورود نادرست است.');
        }

        $auth = wp_authenticate($user->user_login, $password);
        if (is_wp_error($auth)) {
            wp_send_json_error('اطلاعات ورود نادرست است.');
        }

        $allowed_roles = apply_filters('emdadcamera_staff_allowed_roles', ['administrator', 'editor', 'shop_manager', 'staff']);
        $has_role = false;
        foreach ($allowed_roles as $role) {
            if (in_array($role, (array) $auth->roles, true)) {
                $has_role = true;
                break;
            }
        }

        if (!$has_role) {
            wp_send_json_error('شما دسترسی به پنل پرسنل ندارید.');
        }

        wp_set_current_user($auth->ID);
        wp_set_auth_cookie($auth->ID);
        do_action('wp_login', $auth->user_login, $auth);

        $redirect = get_option('emdadcamera_staff_redirect_url', admin_url());

        wp_send_json_success([
            'message'  => 'خوش آمدید ' . esc_html($auth->display_name),
            'redirect' => $redirect,
        ]);
    }

    /* ────────────────────────────────────────────
       AJAX — ثبت مشتری
    ──────────────────────────────────────────── */
    public function register_user() {
        check_ajax_referer('otp_login_nonce', 'nonce');
        $mobile = trim(sanitize_text_field(wp_unslash($_POST['mobile'] ?? '')));

        if (!$mobile) {
            wp_send_json_error('شماره موبایل الزامی است.');
        }

        // ساخت خودکار نام کاربری از شماره موبایل (مثلاً user09123456789)
        $base_username = 'user' . preg_replace('/[^0-9]/', '', $mobile);
        $username = $base_username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $base_username . '_' . $counter;
            $counter++;
        }

        // ساخت خودکار نام نمایشی
        $name = 'کاربر ' . substr(preg_replace('/[^0-9]/', '', $mobile), -4);

        $user_id = wp_create_user($username, wp_generate_password(), $username . '@emdadcamera.ir');
        if (is_wp_error($user_id)) {
            wp_send_json_error('خطا در ایجاد حساب.');
        }

        wp_update_user(['ID' => $user_id, 'display_name' => $name, 'first_name' => $name]);
        emdadcamera_otp_login_sync_user_mobile($user_id, $mobile);
        (new WP_User($user_id))->set_role(get_option('otp_login_default_role', 'subscriber'));
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        do_action('wp_login', $username, new WP_User($user_id));

        wp_send_json_success([
            'message'  => 'ثبت‌نام موفق. خوش آمدید!',
            'redirect' => emdadcamera_otp_login_consume_redirect_target(),
        ]);
    }

    public function check_username() {
        check_ajax_referer('otp_login_nonce', 'nonce');
        $username = sanitize_user(wp_unslash($_POST['username'] ?? ''), true);
        if (username_exists($username)) {
            wp_send_json_error('قبلاً استفاده شده.');
        }
        wp_send_json_success('در دسترس است ✓');
    }

    public function traditional_login() {
        check_ajax_referer('otp_login_nonce', 'nonce');
        $identifier = trim(sanitize_text_field(wp_unslash($_POST['username'] ?? '')));
        $password   = (string) wp_unslash($_POST['password'] ?? '');

        if (!$identifier || !$password) {
            wp_send_json_error('شناسه و رمز الزامی است.');
        }
        $user = $this->resolve_login_user($identifier);
        if (!$user) { wp_send_json_error('اطلاعات نادرست است.'); }

        $auth = wp_authenticate($user->user_login, $password);
        if (is_wp_error($auth)) { wp_send_json_error('اطلاعات نادرست است.'); }

        wp_set_current_user($auth->ID);
        wp_set_auth_cookie($auth->ID);
        do_action('wp_login', $auth->user_login, $auth);
        emdadcamera_otp_login_sync_user_mobile($auth->ID, '');

        wp_send_json_success([
            'message'  => 'با موفقیت وارد شدید.',
            'redirect' => emdadcamera_otp_login_consume_redirect_target(),
        ]);
    }

    private function resolve_login_user($identifier) {
        $mode = emdadcamera_notify_get_password_login_mode();
        $identifier = trim((string) $identifier);
        if (!$identifier) return false;

        $is_email  = is_email($identifier);
        $is_mobile = (bool) preg_match('/^[0-9\+]{8,20}$/', $identifier);

        $allow_u = in_array($mode, ['username_email','mobile_username','all'], true);
        $allow_e = in_array($mode, ['username_email','email_mobile','all'], true);
        $allow_m = in_array($mode, ['email_mobile','mobile_username','mobile_only','all'], true);

        if ($allow_e && $is_email)  { $u = get_user_by('email', $identifier); if ($u) return $u; }
        if ($allow_m && $is_mobile) { $u = emdadcamera_notify_find_user_by_mobile($identifier); if ($u) return $u; }
        if ($allow_u)               { $u = get_user_by('login', $identifier); if ($u) return $u; }
        if ($allow_e && !$is_email) { $u = get_user_by('email', $identifier); if ($u) return $u; }
        return false;
    }

    public function send_password_reset_otp() {
        check_ajax_referer('otp_login_nonce', 'nonce');
        $mobile = trim(sanitize_text_field(wp_unslash($_POST['mobile'] ?? '')));
        if (!$mobile) { wp_send_json_error('شماره تلفن الزامی است.'); }
        $user = emdadcamera_notify_find_user_by_mobile($mobile);
        if (!$user) { wp_send_json_error('کاربری با این شماره پیدا نشد.'); }
        $_POST['mobile'] = $mobile;
        EmdadCamera_Login_Ajax::proxy_send_otp_static();
    }

    public function reset_password_with_otp() {
        check_ajax_referer('otp_login_nonce', 'nonce');
        $mobile = trim(sanitize_text_field(wp_unslash($_POST['mobile'] ?? '')));
        $otp    = trim(sanitize_text_field(wp_unslash($_POST['otp']    ?? '')));
        $pass   = (string) wp_unslash($_POST['password']        ?? '');
        $rep    = (string) wp_unslash($_POST['password_repeat'] ?? '');

        if (!$mobile || !$otp || !$pass || !$rep) { wp_send_json_error('همه فیلدها الزامی است.'); }
        if ($pass !== $rep) { wp_send_json_error('تکرار رمز مطابقت ندارد.'); }

        $verify = EmdadCamera_Login_DB::verify_otp($mobile, $otp);
        if (empty($verify['success'])) { wp_send_json_error('کد نامعتبر یا منقضی شده.'); }

        $user = emdadcamera_notify_find_user_by_mobile($mobile);
        if (!$user) { wp_send_json_error('کاربر پیدا نشد.'); }

        wp_set_password($pass, $user->ID);
        wp_send_json_success(['message' => 'رمز عبور تغییر کرد.']);
    }
}
