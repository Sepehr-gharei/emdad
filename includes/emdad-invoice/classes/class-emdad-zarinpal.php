<?php
if (!defined('ABSPATH')) exit;

class EmdadInvoice_Zarinpal {

    public function __construct() {
        add_action('admin_post_emdad_pay_invoice',        [$this, 'request_payment']);
        add_action('admin_post_nopriv_emdad_pay_invoice', [$this, 'request_payment']);
        add_action('init', [$this, 'verify_payment']);
    }

    private function get_merchant() {
        $settings = get_option('emdad_invoice_settings', []);
        return $settings['zarrinpal_merchant'] ?? '';
    }

    private function is_sandbox() {
        $settings = get_option('emdad_invoice_settings', []);
        return !empty($settings['zarrinpal_sandbox']) && $settings['zarrinpal_sandbox'] == '1';
    }

    private function api_url($endpoint) {
        $base = $this->is_sandbox()
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/'
            : 'https://payment.zarinpal.com/pg/v4/payment/';
        return $base . $endpoint;
    }

    private function gateway_url($authority) {
        if ($this->is_sandbox()) {
            return 'https://sandbox.zarinpal.com/pg/StartPay/' . $authority;
        }
        return 'https://payment.zarinpal.com/pg/StartPay/' . $authority;
    }

    public function request_payment() {
        if (!isset($_POST['invoice_id'])) return;

        $merchant = $this->get_merchant();
        if (empty($merchant)) {
            wp_die('❌ کد مرچنت زرین‌پال تنظیم نشده است. لطفاً از <a href="' . admin_url('admin.php?page=emdad-settings') . '">تنظیمات فاکتور</a> آن را وارد کنید.');
        }

        $invoice_id = intval($_POST['invoice_id']);
        $invoice = EmdadInvoice_DB::get_invoice($invoice_id);

        // اگر remaining خالی بود ولی فاکتور پرداخت‌نشده بود، از total استفاده کن (فاکتورهای قدیمی)
        if ($invoice && floatval($invoice->remaining) <= 0 && !in_array($invoice->status, ['paid', 'cancelled'])) {
            global $wpdb;
            $total_paid = (float)$wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}emdad_invoice_payments WHERE invoice_id=%d AND status='success'",
                $invoice_id
            ));
            $real_remaining = max(0, floatval($invoice->total) - $total_paid);
            if ($real_remaining > 0) {
                $wpdb->update($wpdb->prefix . 'emdad_invoices', ['remaining' => $real_remaining], ['id' => $invoice_id]);
                $invoice = EmdadInvoice_DB::get_invoice($invoice_id);
            }
        }

        if (!$invoice || floatval($invoice->remaining) <= 0) {
            wp_die('فاکتور نامعتبر است یا پرداخت شده است.');
        }

        $settings_cur = get_option('emdad_invoice_settings', []);
        $currency     = $settings_cur['currency_label'] ?? 'تومان';

        // اگر واحد تومان است، ضرب در ۱۰ برای تبدیل به ریال؛ اگر ریال است، همان مقدار
        $remaining_num = floatval($invoice->remaining);
        $amount = ($currency === 'ریال')
            ? (int)$remaining_num
            : (int)($remaining_num * 10);

        // زرین‌پال حداکثر ۱,۰۰۰,۰۰۰,۰۰۰ ریال (= ۱۰۰,۰۰۰,۰۰۰ تومان) قبول می‌کند
        if ($amount > 1000000000) {
            wp_die(
                '<div style="font-family:Tahoma;direction:rtl;padding:30px;">' .
                '<h2 style="color:#dc2626;">❌ مبلغ فاکتور بیش از حد مجاز زرین‌پال</h2>' .
                '<p>زرین‌پال حداکثر <strong>۱۰۰,۰۰۰,۰۰۰ تومان</strong> در یک تراکنش قبول می‌کند.</p>' .
                '<p>مبلغ این فاکتور: <strong>' . number_format($remaining_num) . ' ' . esc_html($currency) . '</strong></p>' .
                '<p style="color:#888;font-size:13px;">برای مبالغ بالاتر، پرداخت را به چند مرحله تقسیم کنید یا از روش پرداخت دیگری استفاده نمایید.</p>' .
                '<a href="javascript:history.back()" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#e41522;color:#fff;border-radius:8px;text-decoration:none;">← بازگشت</a>' .
                '</div>'
            );
        }

        if ($amount < 1000) {
            wp_die(
                '<div style="font-family:Tahoma;direction:rtl;padding:30px;">' .
                '<h2 style="color:#dc2626;">❌ مبلغ فاکتور کمتر از حد مجاز</h2>' .
                '<p>زرین‌پال حداقل <strong>۱۰۰ تومان</strong> قبول می‌کند.</p>' .
                '<a href="javascript:history.back()" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#e41522;color:#fff;border-radius:8px;text-decoration:none;">← بازگشت</a>' .
                '</div>'
            );
        }

        $callback = home_url('/?emdad_verify_invoice=' . $invoice->id);

        $data = [
            'merchant_id'  => $merchant,
            'amount'       => $amount,
            'callback_url' => $callback,
            'description'  => 'پرداخت فاکتور: ' . $invoice->invoice_number,
        ];

        $ch = curl_init($this->api_url('request.json'));
        curl_setopt_array($ch, [
            CURLOPT_USERAGENT      => 'ZarinPal Rest Api v4',
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $result   = curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($result === false || empty($result)) {
            wp_die('❌ خطا در اتصال به زرین‌پال: ' . esc_html($curl_err ?: 'پاسخی دریافت نشد.'));
        }
        $response = json_decode($result, true);

        if (isset($response['data']['code']) && $response['data']['code'] == 100) {
            global $wpdb;
            $authority = sanitize_text_field($response['data']['authority'] ?? '');
            if (empty($authority)) {
                wp_die('❌ زرین‌پال Authority برنگرداند. پاسخ کامل: <code>' . esc_html(json_encode($response, JSON_UNESCAPED_UNICODE)) . '</code>');
            }
            $wpdb->update(
                $wpdb->prefix . 'emdad_invoices',
                ['zarrinpal_authority' => $authority],
                ['id' => $invoice->id]
            );
            wp_redirect($this->gateway_url($authority));
            exit;
        }

        $error_code = $response['errors']['code'] ?? $response['data']['code'] ?? 'نامشخص';
        $error_messages = [
            '-1'  => 'اطلاعات ارسالی ناقص است.',
            '-2'  => 'IP یا Merchant Code اشتباه است.',
            '-3'  => 'مبلغ باید بین ۱۰۰۰ تا ۵۰۰۰۰۰۰۰۰ ریال باشد.',
            '-4'  => 'سطح تأیید پذیرنده پایین‌تر از سطح نقره‌ای است.',
            '-9'  => 'IP سرور در زرین‌پال تأیید نشده یا Merchant Code نامعتبر است.',
            '-10' => 'Token یا Merchant یافت نشد.',
            '-11' => 'تراکنش یافت نشد.',
            '-15' => 'درگاه پرداخت غیرفعال است.',
            '-34' => 'مبلغ از حداکثر مجاز تجاوز کرده.',
            '-50' => 'مبلغ verify با مبلغ پرداخت‌شده برابر نیست.',
            '-51' => 'پرداخت ناموفق بود.',
            '-54' => 'Authority منقضی شده.',
            '101' => 'تراکنش قبلاً verify شده.',
        ];
        $error_msg = $error_messages[(string)$error_code] ?? 'خطای ناشناخته.';
        wp_die(
            '<div style="font-family:Tahoma;direction:rtl;padding:30px;">' .
            '<h2 style="color:#dc2626;">❌ خطا در اتصال به درگاه زرین‌پال</h2>' .
            '<p><strong>کد خطا:</strong> ' . esc_html($error_code) . '</p>' .
            '<p><strong>توضیح:</strong> ' . esc_html($error_msg) . '</p>' .
            '<p style="font-size:12px;color:#888;">پاسخ کامل: <code>' . esc_html(json_encode($response, JSON_UNESCAPED_UNICODE)) . '</code></p>' .
            '</div>'
        );
    }

    public function verify_payment() {
        if (!isset($_GET['emdad_verify_invoice'])) return;
        // Authority ممکنه با حرف بزرگ یا کوچک بیاد
        if (!isset($_GET['Authority']) && !isset($_GET['authority'])) return;

        $invoice_id = intval($_GET['emdad_verify_invoice']);
        $authority  = sanitize_text_field($_GET['Authority'] ?? $_GET['authority'] ?? '');
        $status     = sanitize_text_field($_GET['Status'] ?? $_GET['status'] ?? '');
        $invoice    = EmdadInvoice_DB::get_invoice($invoice_id);

        if (!$invoice) {
            wp_die('فاکتور یافت نشد.');
        }

        if ($status !== 'OK') {
            wp_die(
                '<div style="font-family:Tahoma;direction:rtl;padding:30px;">' .
                '<h2>❌ پرداخت لغو شد</h2>' .
                '<p>پرداخت توسط کاربر یا بانک لغو شد.</p>' .
                '<a href="' . home_url('/faktur/' . $invoice->invoice_number) . '" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#e41522;color:#fff;border-radius:8px;text-decoration:none;">بازگشت به فاکتور</a>' .
                '</div>'
            );
        }

        // چک فرمت Authority — production باید با S. شروع کند
        if (!empty($authority) && !str_starts_with($authority, 'S.') && !$this->is_sandbox()) {
            wp_die(
                '<div style="font-family:Tahoma;direction:rtl;padding:30px;">' .
                '<h2 style="color:#dc2626;">❌ خطای Authority نامعتبر</h2>' .
                '<p>Authority دریافت‌شده: <code>' . esc_html($authority) . '</code></p>' .
                '<p>احتمالاً درگاه روی <strong>Sandbox</strong> ثبت شده ولی Merchant Code مربوط به <strong>Production</strong> است، یا برعکس.</p>' .
                '<p>لطفاً در تنظیمات پلاگین بررسی کنید که حالت <strong>Sandbox</strong> با نوع Merchant Code شما مطابقت دارد.</p>' .
                '<a href="javascript:history.back()" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#555;color:#fff;border-radius:8px;text-decoration:none;">← بازگشت</a>' .
                '</div>'
            );
        }

        $merchant = $this->get_merchant();
        if (empty($merchant)) {
            wp_die('کد مرچنت تنظیم نشده است.');
        }

        $settings_cur = get_option('emdad_invoice_settings', []);
        $data = [
            'merchant_id' => $merchant,
            'amount'      => (($settings_cur['currency_label'] ?? 'تومان') === 'ریال') ? (int)$invoice->remaining : (int)($invoice->remaining * 10),
            'authority'   => $authority,
        ];

        $ch = curl_init($this->api_url('verify.json'));
        curl_setopt_array($ch, [
            CURLOPT_USERAGENT      => 'ZarinPal Rest Api v4',
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);

        if (isset($response['data']['code']) && in_array($response['data']['code'], [100, 101])) {
            $ref_id = $response['data']['ref_id'] ?? '';
            EmdadInvoice_DB::record_payment(
                $invoice->id,
                $invoice->remaining,
                'online',
                $ref_id
            );
            wp_die(
                '<div style="font-family:Tahoma;direction:rtl;text-align:center;padding:40px;">' .
                '<h2 style="color:#22c55e">✅ پرداخت موفق</h2>' .
                '<p>کد پیگیری: <strong>' . esc_html($ref_id) . '</strong></p>' .
                '<a href="' . home_url('/faktur/' . $invoice->invoice_number) . '" style="display:inline-block;margin-top:16px;padding:10px 28px;background:#e41522;color:#fff;border-radius:8px;text-decoration:none;">مشاهده فاکتور</a>' .
                '</div>'
            );
        }

        wp_die(
            '<div style="font-family:Tahoma;direction:rtl;text-align:center;padding:40px;">' .
            '<h2 style="color:#ef4444">❌ پرداخت ناموفق</h2>' .
            '<p>کد خطای زرین‌پال: <strong>' . esc_html($response['errors']['code'] ?? $response['data']['code'] ?? 'نامشخص') . '</strong></p>' .
            '<p style="font-size:12px;color:#888;">Authority: ' . esc_html($authority) . '</p>' .
            '<p style="font-size:12px;color:#888;">پاسخ کامل: <code>' . esc_html(json_encode($response, JSON_UNESCAPED_UNICODE)) . '</code></p>' .
            '<a href="' . home_url('/faktur/' . $invoice->invoice_number) . '" style="display:inline-block;margin-top:16px;padding:10px 28px;background:#e41522;color:#fff;border-radius:8px;text-decoration:none;">بازگشت به فاکتور</a>' .
            '</div>'
        );
    }

    private function purge_litespeed_cache($url = '', $tag = '') {
        // روش ۱: Purge با tag یکتای فاکتور (دقیق‌ترین روش)
        if (!empty($tag)) {
            do_action('litespeed_purge_tag', $tag);
        }
        // روش ۲: Purge با URL مستقیم
        if (!empty($url)) {
            do_action('litespeed_purge_url', $url);
        }
        // روش ۳: Header مستقیم به LiteSpeed Server
        if (!headers_sent()) {
            if (!empty($tag)) {
                header('X-LiteSpeed-Purge: tag=' . $tag);
            }
            header('X-LiteSpeed-Purge: ' . ($url ?: '*'));
        }
        // روش ۴: WP Rocket
        if (function_exists('rocket_clean_post')) {
            $post_id = url_to_postid($url);
            if ($post_id) rocket_clean_post($post_id);
        }
        // روش ۵: W3 Total Cache
        if (function_exists('w3tc_flush_url') && !empty($url)) {
            w3tc_flush_url($url);
        }
    }
}