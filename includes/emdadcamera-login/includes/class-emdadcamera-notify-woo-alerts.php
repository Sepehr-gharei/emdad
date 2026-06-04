<?php
if (!defined('ABSPATH')) {
    exit;
}

class EmdadCamera_Notify_Woo_Alerts {
    public function __construct() {
        add_action('woocommerce_new_order', array($this, 'handle_new_order'), 20, 1);
        add_action('woocommerce_order_status_changed', array($this, 'handle_status_changed'), 20, 4);
    }

    public function handle_new_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        if ((int) get_option('emdadcamera_notify_customer_new_order_enabled', 0) === 1) {
            $template = (string) get_option('emdadcamera_notify_customer_new_order_message', '{customer_name} عزیز، سفارش شما با شماره {order_id} در {site_name} ثبت شد.');
            $this->send_to_customer($order, $template, 'customer_new_order');
        }

        if ((int) get_option('emdadcamera_notify_admin_new_order_enabled', 0) === 1) {
            $template = (string) get_option('emdadcamera_notify_admin_new_order_message', 'سفارش جدید #{order_id} ثبت شد. مشتری: {customer_name} - مبلغ: {order_total}');
            $this->send_to_admins($order, $template, 'admin_new_order');
        }
    }

    public function handle_status_changed($order_id, $old_status, $new_status, $order) {
        if (!$order || !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
        }

        $customer_statuses = (array) get_option('emdadcamera_notify_customer_statuses', array());
        $admin_statuses = (array) get_option('emdadcamera_notify_admin_statuses', array());

        if (in_array($new_status, $customer_statuses, true)) {
            $template = (string) get_option('emdadcamera_notify_customer_status_message', 'وضعیت سفارش #{order_id} به {order_status} تغییر کرد.');
            $this->send_to_customer($order, $template, 'customer_status_' . $new_status);
        }

        if (in_array($new_status, $admin_statuses, true)) {
            $template = (string) get_option('emdadcamera_notify_admin_status_message', 'وضعیت سفارش #{order_id} برای {customer_name} به {order_status} تغییر کرد.');
            $this->send_to_admins($order, $template, 'admin_status_' . $new_status);
        }
    }

    private function send_to_customer($order, $template, $event_key) {
        $mobile = emdadcamera_notify_get_customer_mobile($order);
        if ($mobile === '') {
            emdadcamera_notify_write_log('sms', '', '', $event_key, $order->get_id(), 'failed', 'شماره مشتری پیدا نشد.');
            return;
        }

        $message = emdadcamera_notify_render_message($template, $order);
        emdadcamera_notify_send_sms_message($mobile, $message, $event_key, $order->get_id());
    }

    private function send_to_admins($order, $template, $event_key) {
        $mobiles = emdadcamera_notify_get_admin_mobiles();
        if (empty($mobiles)) {
            emdadcamera_notify_write_log('sms', '', '', $event_key, $order->get_id(), 'failed', 'شماره مدیر تنظیم نشده است.');
            return;
        }

        $message = emdadcamera_notify_render_message($template, $order);
        foreach ($mobiles as $mobile) {
            emdadcamera_notify_send_sms_message($mobile, $message, $event_key, $order->get_id());
        }
    }
}
