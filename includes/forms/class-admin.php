<?php
defined( 'ABSPATH' ) || exit;

class Emdad_Forms_Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'menu']);
    }

    public static function menu() {
        $db = new Emdad_Forms_Database();
        // اصلاح نام فرم‌ها برای شمارش دقیق خوانده نشده‌ها
        $total = $db->get_unread_count('contact') + $db->get_unread_count('order') + $db->get_unread_count('service');
        $badge = $total ? " <span class='awaiting-mod'>$total</span>" : '';

        add_menu_page('درخواست‌ها', 'درخواست‌ها' . $badge, 'manage_options', 'emdad_forms', [__CLASS__, 'page'], 'dashicons-email-alt', 26);
        add_submenu_page('emdad_forms', 'تنظیمات پیامک', 'تنظیمات پیامک', 'manage_options', 'emdad_forms_sms', [__CLASS__, 'sms_page']);
    }

    public static function page() {
        $db = new Emdad_Forms_Database();
        
        if (isset($_GET['delete'])) { $db->delete(intval($_GET['delete'])); wp_redirect(remove_query_arg('delete')); exit; }
        if (isset($_GET['read'])) { $db->mark_read(intval($_GET['read'])); wp_redirect(remove_query_arg('read')); exit; }

        $tab = $_GET['tab'] ?? 'contact';
        $entries = $db->get_entries($tab);
        
        echo '<div class="wrap"><h1>درخواست‌های ثبت شده</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        // اصلاح لینکِ تب‌ها مطابق با فایل Frontend
        echo '<a class="nav-tab '.($tab=='contact'?'nav-tab-active':'').'" href="?page=emdad_forms&tab=contact">تماس با ما</a>';
        echo '<a class="nav-tab '.($tab=='order'?'nav-tab-active':'').'" href="?page=emdad_forms&tab=order">ثبت سفارش</a>';
        echo '<a class="nav-tab '.($tab=='service'?'nav-tab-active':'').'" href="?page=emdad_forms&tab=service">درخواست نصب</a>';
        echo '</h2><table class="wp-list-table widefat fixed striped" style="margin-top:20px;">';
        echo '<thead><tr><th>تاریخ</th><th>نام</th><th>تلفن</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>';
        
        if(empty($entries)) { echo '<tr><td colspan="5">هیچ درخواستی یافت نشد.</td></tr>'; }
        foreach($entries as $e) {
            $d = json_decode($e['data'], true);
            $status = $e['is_read'] ? 'خوانده شده' : '<strong style="color:red">جدید</strong>';
            
            // داده‌ها را برای SweetAlert آماده می‌کنیم
            $json_data = htmlspecialchars(json_encode($d, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

            echo "<tr>
                <td>{$e['created_at']}</td>
                <td>".esc_html($d['full_name'] ?? '-')."</td>
                <td>".esc_html($d['phone'] ?? '-')."</td>
                <td>{$status}</td>
                <td>
                    <button class='button button-primary' onclick='showDetails({$json_data})'>جزئیات</button>
                    <a href='?page=emdad_forms&tab={$tab}&read={$e['id']}' class='button'>خواندم</a>
                    <a href='?page=emdad_forms&tab={$tab}&delete={$e['id']}' class='button button-link-delete' onclick='return confirm(\"مطمئن هستید؟\");'>حذف</a>
                </td>
            </tr>";
        }
        echo '</tbody></table></div>';

        // بارگذاری SweetAlert2 و تابع نمایش جزئیات
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function showDetails(data) {
                let htmlContent = '<div style="text-align: right; direction: rtl; font-size: 14px; line-height: 2;">';
                const translations = {
                    'full_name': 'نام و نام خانوادگی',
                    'phone': 'شماره موبایل',
                    'subject': 'موضوع پیام',
                    'message': 'متن پیام',
                    'request_type': 'نوع درخواست',
                    'service_type': 'خدمت مورد نظر',
                    'description': 'توضیحات',
                    'email': 'ایمیل',
                    'tracking_code': 'کد پیگیری',
                    'date': 'تاریخ',
                    // فیلدهای جدید
                    'device_count': 'تعداد دوربین - دستگاه',
                    'needs_transfer': 'نیاز به انتقال تصویر',
                    'needs_visit': 'نیاز به بازدید',
                    'visit_hours': 'ساعات امکان بازدید'
                };

                for (const [key, value] of Object.entries(data)) {
                    // جلوگیری از نمایش فیلد ساعات بازدید در صورت خالی بودن
                    if (key === 'visit_hours' && !value) continue;
                    
                    let label = translations[key] || key;
                    htmlContent += `<strong>${label}:</strong> ${value} <br><hr style="border: 0; border-top: 1px solid #eee; margin: 5px 0;">`;
                }
                htmlContent += '</div>';

                Swal.fire({
                    title: 'جزئیات فرم',
                    html: htmlContent,
                    icon: 'info',
                    confirmButtonText: 'بستن',
                    confirmButtonColor: '#e41522'
                });
            }
        </script>
        <?php
    }

    public static function sms_page() {
        if (isset($_POST['save_sms'])) {
            update_option('emdad_sms_settings', [
                'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
                'admin_mobile' => sanitize_text_field($_POST['admin_mobile'] ?? ''),
                'admin_template_id' => intval($_POST['admin_tid'] ?? 789058),
                'user_template_id' => intval($_POST['user_tid'] ?? 248457),
                'enabled' => 1
            ]);
            echo '<div class="notice notice-success"><p>تنظیمات ذخیره شد.</p></div>';
        }

        $s = get_option('emdad_sms_settings', [
            'api_key' => '',
            'admin_mobile' => '9160721720',
            'admin_template_id' => 789058,
            'user_template_id' => 248457
        ]);

        ?>
        <div class="wrap">
            <h1>تنظیمات پیامک (SMS.ir)</h1>
            <form method="post">
                <table class="form-table">
                    <tr><th>API Key</th><td><input type="text" class="regular-text" name="api_key" value="<?php echo esc_attr($s['api_key'] ?? ''); ?>"></td></tr>
                    <tr><th>شناسه قالب ادمین</th><td><input type="number" name="admin_tid" value="<?php echo esc_attr($s['admin_template_id'] ?? 789058); ?>"></td></tr>
                    <tr><th>شناسه قالب کاربر</th><td><input type="number" name="user_tid" value="<?php echo esc_attr($s['user_template_id'] ?? 248457); ?>"></td></tr>
                    <tr><th>موبایل مدیر (بدون صفر)</th><td><input type="text" class="regular-text" name="admin_mobile" value="<?php echo esc_attr($s['admin_mobile'] ?? '9160721720'); ?>"></td></tr>
                </table>
                <?php submit_button('ذخیره تنظیمات', 'primary', 'save_sms'); ?>
            </form>
        </div>
        <?php
    }
}