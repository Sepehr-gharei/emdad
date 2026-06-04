<?php
defined( 'ABSPATH' ) || exit;

class CFB_SMS_Notifier {
    private $api_key;
    private $admin_template;
    private $user_template;
    private $admin_mobile;

    public function __construct() {
        $settings = get_option('emdad_sms_settings', []);
        $this->api_key      = $settings['api_key'] ?? 'Wdf35n6UPmW055UHdm2TSEjEcga0XHhFG5xbOOiakXnO0aT6';
        $this->admin_template = !empty($settings['admin_template_id']) ? (int)$settings['admin_template_id'] : 789058;
        $this->user_template  = !empty($settings['user_template_id']) ? (int)$settings['user_template_id'] : 248457;
        $this->admin_mobile = $settings['admin_mobile'] ?? '9160721720';
    }

    public function send_notifications($form_id, $data) {
        if (empty($this->api_key)) return;

        // --- ارسال به ادمین (فقط نام، شماره و تاریخ) ---
        // مقادیر name باید با متغیرهایی که در پنل sms.ir ساختید دقیقاً یکی باشند
        $admin_params = [
            ['name' => 'username', 'value' => $data['full_name'] ?? 'کاربر ناشناس'],
            ['name' => 'phone', 'value' => $data['phone'] ?? '-'],
            ['name' => 'date', 'value' => $data['date'] ?? date('Y/m/d H:i')]
        ];

        $this->call_verify_api($this->admin_mobile, $this->admin_template, $admin_params);

        // --- ارسال به کاربر (در صورت نیاز) ---
        if (!empty($data['phone'])) {
            $user_params = [['name' => 'TRACKING_CODE', 'value' => (string)$data['tracking_code']]];
            $this->call_verify_api($data['phone'], $this->user_template, $user_params);
        }
    }

    private function call_verify_api($mobile, $template_id, $params) {
        $mobile = $this->normalize_mobile($mobile);
        $data = [
            'Mobile' => $mobile,
            'TemplateId' => $template_id,
            'Parameters' => $params
        ];

        $ch = curl_init('https://api.sms.ir/v1/send/verify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-api-key: ' . $this->api_key, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    private function normalize_mobile($mobile) {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) == 11 && substr($mobile, 0, 1) == '0') return substr($mobile, 1);
        return $mobile;
    }
}