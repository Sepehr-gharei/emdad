<?php
class Emdad_Forms_Ajax {
    public static function init() {
        add_action('wp_ajax_emdad_submit', [__CLASS__, 'handle']);
        add_action('wp_ajax_nopriv_emdad_submit', [__CLASS__, 'handle']);
    }

    public static function handle() {
        check_ajax_referer('emdad_submit_nonce', 'security');
        
        $form_id = sanitize_key($_POST['form_id']);
        $data = array_map('sanitize_textarea_field', $_POST);
        
        // تولید کد پیگیری و اضافه کردن تاریخ
        $data['tracking_code'] = 'EM-' . rand(10000, 99999);
        
        // اگر افزونه تقویم جلالی دارید از jdate استفاده کنید، در غیر این صورت date
        $data['date'] = function_exists('jdate') ? jdate('Y/m/d H:i') : current_time('Y/m/d H:i');

        // ذخیره دیتابیس
        (new Emdad_Forms_Database())->insert($form_id, $data);
        
        // ارسال پیامک
        (new CFB_SMS_Notifier())->send_notifications($form_id, $data);

        wp_send_json_success(['message' => 'درخواست شما ثبت شد. کد پیگیری: ' . $data['tracking_code']]);
    }
}