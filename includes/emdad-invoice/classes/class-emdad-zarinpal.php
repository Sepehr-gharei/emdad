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
            : 'https://api.zarinpal.com/pg/v4/payment/';
        return $base . $endpoint;
    }

    private function gateway_url($authority) {
        if ($this->is_sandbox()) {
            return 'https://sandbox.zarinpal.com/pg/StartPay/' . $authority;
        }
        return 'https://www.zarinpal.com/pg/StartPay/' . $authority;
    }

    public function request_payment() {
        if (!isset($_POST['invoice_id'])) return;

        $merchant = $this->get_merchant();
        if (empty($merchant)) {
            wp_die('❌ کد مرچنت زرین‌پال تنظیم نشده است. لطفاً از <a href="' . admin_url('admin.php?page=emdad-settings') . '">تنظیمات فاکتور</a> آن را وارد کنید.');
        }

        $invoice_id = intval($_POST['invoice_id']);
        $invoice = EmdadInvoice_DB::get_invoice($invoice_id);

        if (!$invoice || $invoice->remaining <= 0) {
            wp_die('فاکتور نامعتبر است یا پرداخت شده است.');
        }

        $amount   = (int)$invoice->remaining * 10; // تبدیل تومان به ریال
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
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);

        if (isset($response['data']['code']) && $response['data']['code'] == 100) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'emdad_invoices',
                ['zarrinpal_authority' => $response['data']['authority']],
                ['id' => $invoice->id]
            );
            wp_redirect($this->gateway_url($response['data']['authority']));
            exit;
        }

        $error_code = $response['errors']['code'] ?? $response['data']['code'] ?? 'نامشخص';
        wp_die('خطا در اتصال به درگاه زرین‌پال. کد خطا: ' . esc_html($error_code));
    }

    public function verify_payment() {
        if (!isset($_GET['emdad_verify_invoice']) || !isset($_GET['Authority'])) return;

        $invoice_id = intval($_GET['emdad_verify_invoice']);
        $authority  = sanitize_text_field($_GET['Authority']);
        $status     = sanitize_text_field($_GET['Status'] ?? '');
        $invoice    = EmdadInvoice_DB::get_invoice($invoice_id);

        if (!$invoice) {
            wp_die('فاکتور یافت نشد.');
        }

        if ($status !== 'OK') {
            wp_die('پرداخت توسط کاربر لغو شد.<br><a href="' . home_url('/faktur/' . $invoice->invoice_number) . '">بازگشت به فاکتور</a>');
        }

        $merchant = $this->get_merchant();
        if (empty($merchant)) {
            wp_die('کد مرچنت تنظیم نشده است.');
        }

        $data = [
            'merchant_id' => $merchant,
            'amount'      => (int)$invoice->remaining * 10,
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
            $admin_class = new EmdadInvoice_Admin();
            $admin_class->register_payment([
                'invoice_id'     => $invoice->id,
                'amount'         => $invoice->remaining,
                'method'         => 'online',
                'reference_code' => $ref_id,
            ]);
            wp_die(
                '<div style="font-family:Tahoma;direction:rtl;text-align:center;padding:40px;">' .
                '<h2 style="color:#22c55e">✅ پرداخت موفق</h2>' .
                '<p>کد پیگیری: <strong>' . esc_html($ref_id) . '</strong></p>' .
                '<a href="' . home_url('/faktur/' . $invoice->invoice_number) . '" style="display:inline-block;margin-top:16px;padding:10px 28px;background:#e41522;color:#fff;border-radius:8px;text-decoration:none;">مشاهده فاکتور</a>' .
                '</div>'
            );
        }

        wp_die('پرداخت ناموفق بود.<br><a href="' . home_url('/faktur/' . $invoice->invoice_number) . '">بازگشت</a>');
    }
}
