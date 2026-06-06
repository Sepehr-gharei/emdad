<?php
if (!defined('ABSPATH')) exit;

class EmdadInvoice_Admin {

    public function __construct() {
        add_action('admin_menu',       [$this, 'register_menus']);
        add_action('admin_init',       [$this, 'handle_form_submit']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // AJAX
        add_action('wp_ajax_emdad_get_invoice_data',    [$this, 'ajax_get_invoice_data']);
        add_action('wp_ajax_emdad_delete_invoice',      [$this, 'ajax_delete_invoice']);
        add_action('wp_ajax_emdad_change_status',       [$this, 'ajax_change_status']);
        add_action('wp_ajax_emdad_register_payment',    [$this, 'ajax_register_payment']);
        add_action('wp_ajax_emdad_search_customers',    [$this, 'ajax_search_customers']);
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'emdad') === false) return;
        wp_enqueue_style('emdad-jdp-css',  EMDAD_INVOICE_URL . 'assets/css/jalalidatepicker.min.css');
        wp_enqueue_style('emdad-admin-css', EMDAD_INVOICE_URL . 'assets/css/invoice.css');
        wp_enqueue_script('emdad-jdp-js',  EMDAD_INVOICE_URL . 'assets/js/jalalidatepicker.min.js', [], false, true);
    }

    public function register_menus() {
        // منوی اصلی ERP
        add_menu_page('ERP امداد', 'ERP', 'manage_options', 'emdad-erp',
            [$this, 'page_erp'], 'dashicons-store', 30);

        // صفحه‌های داخلی (hidden submenu — با parent خودشان ثبت می‌شن تا URL کار کنه)
        add_submenu_page('emdad-erp', 'فاکتورها', 'فاکتورها', 'manage_options',
            'emdad-erp', [$this, 'page_erp']);
        add_submenu_page('emdad-erp', 'فاکتور جدید', 'فاکتور جدید', 'manage_options',
            'emdad-invoice-new', [$this, 'page_invoice_form']);
        add_submenu_page('emdad-erp', 'تنظیمات فاکتور', 'تنظیمات فاکتور', 'manage_options',
            'emdad-invoice-settings', [$this, 'page_settings']);
    }

    public function handle_form_submit() {
        if (!isset($_POST['emdad_invoice_action'])) return;
        if (!current_user_can('manage_options')) wp_die('دسترسی ندارید');

        // ── تأیید nonce ──
        $action = sanitize_text_field($_POST['emdad_invoice_action']);
        $nonce_action = ($action === 'save_settings') ? 'emdad_save_settings' : 'emdad_save_invoice';
        if (!isset($_POST['emdad_nonce']) || !wp_verify_nonce($_POST['emdad_nonce'], $nonce_action)) {
            wp_die('خطای امنیتی — لطفاً صفحه را رفرش کرده و دوباره امتحان کنید.');
        }

        if ($action === 'save_invoice') {
            $id = $this->save_invoice($_POST);
            wp_redirect(admin_url('admin.php?page=emdad-erp&tab=invoices&saved=1&id=' . $id));
            exit;
        }
        if ($action === 'save_settings') {
            $this->save_settings($_POST);
            wp_redirect(admin_url('admin.php?page=emdad-invoice-settings&saved=1'));
            exit;
        }
    }

    private function save_invoice($data) {
        global $wpdb;
        $table      = $wpdb->prefix . 'emdad_invoices';
        $invoice_id = intval($data['invoice_id'] ?? 0);
        $type       = sanitize_text_field($data['invoice_type'] ?? 'official');
        $items      = $data['items'] ?? [];
        $subtotal   = 0;
        $item_rows  = [];

        $items_tax_total = 0; // جمع مالیات ردیف‌ها

        foreach ($items as $item) {
            $qty         = floatval($item['quantity'] ?? 1);
            $price       = floatval(str_replace(',', '', $item['unit_price'] ?? 0));
            $disc_pct    = floatval($item['discount_percent'] ?? 0);
            $item_tax_pct= floatval($item['tax_percent'] ?? 0);
            $line_base   = $price * $qty;
            $disc_amt    = round($line_base * $disc_pct / 100);
            $after_disc  = $line_base - $disc_amt;
            $item_tax_amt= round($after_disc * $item_tax_pct / 100);
            $item_total  = $after_disc; // total بدون مالیات ردیف — مالیات جدا حساب می‌شه
            $subtotal   += $item_total;
            $items_tax_total += $item_tax_amt;
            $item_rows[] = [
                'product_code'     => sanitize_text_field($item['product_code'] ?? ''),
                'name'             => sanitize_text_field($item['name'] ?? ''),
                'description'      => sanitize_textarea_field($item['description'] ?? ''),
                'quantity'         => $qty,
                'unit'             => sanitize_text_field($item['unit'] ?? 'عدد'),
                'unit_price'       => $price,
                'discount'         => $disc_amt,
                'discount_percent' => $disc_pct,
                'tax_percent'      => $item_tax_pct,
                'total'            => $item_total,
                'sort_order'       => intval($item['sort_order'] ?? 0),
            ];
        }

        $discount_pct   = floatval($data['discount_percent'] ?? 0);
        $discount_amt   = round($subtotal * $discount_pct / 100);
        $after_discount = $subtotal - $discount_amt;
        $tax_percent    = floatval($data['tax_percent'] ?? 0);
        $global_tax_amt = ($type === 'official') ? round($after_discount * $tax_percent / 100) : 0;
        $tax_amount     = $items_tax_total + $global_tax_amt; // ترکیب مالیات ردیف + کلی
        $total          = $after_discount + $tax_amount;

        $issue_date_raw = sanitize_text_field($data['issue_date'] ?? '');
        $due_date_raw   = sanitize_text_field($data['due_date']   ?? '');

        // تبدیل تاریخ شمسی به میلادی برای MySQL
        $issue_date = $this->jalali_to_gregorian_date($issue_date_raw) ?: date('Y-m-d');
        $due_date   = $this->jalali_to_gregorian_date($due_date_raw)   ?: '';

        $invoice_data = [
            'type'                  => $type,
            'customer_name'         => sanitize_text_field($data['customer_name'] ?? ''),
            'customer_phone'        => sanitize_text_field($data['customer_phone'] ?? ''),
            'customer_national_code'=> sanitize_text_field($data['customer_national_code'] ?? ''),
            'customer_company'      => sanitize_text_field($data['customer_company'] ?? ''),
            'customer_address'      => sanitize_textarea_field($data['customer_address'] ?? ''),
            'customer_city'         => sanitize_text_field($data['customer_city'] ?? ''),
            'customer_email'        => sanitize_email($data['customer_email'] ?? ''),
            'seller_name'           => sanitize_text_field($data['seller_name'] ?? ''),
            'seller_phone'          => sanitize_text_field($data['seller_phone'] ?? ''),
            'seller_company'        => sanitize_text_field($data['seller_company'] ?? ''),
            'seller_economic_code'  => sanitize_text_field($data['seller_economic_code'] ?? ''),
            'seller_address'        => sanitize_textarea_field($data['seller_address'] ?? ''),
            'subtotal'              => $subtotal,
            'discount'              => $discount_amt,
            'discount_percent'      => $discount_pct,
            'tax'                   => $tax_amount,
            'tax_percent'           => $tax_percent,
            'total'                 => $total,
            'remaining'             => $total,
            'notes'                 => sanitize_textarea_field($data['notes'] ?? ''),
            'terms'                 => sanitize_textarea_field($data['terms'] ?? ''),
            'footer_description'    => sanitize_textarea_field($data['footer_description'] ?? ''),
            'issue_date'            => $issue_date,
            'due_date'              => $due_date ?: null,
            'status'                => 'sent',
            'updated_at'            => current_time('mysql'),
        ];

        // در حالت ویرایش: وضعیت را از فرم بگیر و remaining را بر اساس آن تنظیم کن
        if ($invoice_id > 0) {
            $allowed_statuses = ['draft', 'sent', 'partial', 'paid', 'cancelled'];
            $new_status = sanitize_text_field($data['invoice_status'] ?? 'sent');
            if (!in_array($new_status, $allowed_statuses)) $new_status = 'sent';
            $invoice_data['status'] = $new_status;

            // اگر وضعیت «پرداخت شده» انتخاب شد، remaining را صفر کن و paid_amount را کامل کن
            if ($new_status === 'paid') {
                $invoice_data['paid_amount'] = $total;
                $invoice_data['remaining']   = 0;
            } elseif ($new_status === 'cancelled') {
                $invoice_data['remaining'] = 0;
            }
            // برای partial و sent مقدار remaining دست‌نخورده بماند (محاسبه از پرداخت‌های واقعی)
            elseif (in_array($new_status, ['partial', 'sent', 'draft'])) {
                $total_paid_so_far = (float)$wpdb->get_var($wpdb->prepare(
                    "SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}emdad_invoice_payments WHERE invoice_id=%d AND status='success'",
                    $invoice_id
                ));
                $invoice_data['paid_amount'] = $total_paid_so_far;
                $invoice_data['remaining']   = max(0, $total - $total_paid_so_far);
            }

            $wpdb->update($table, $invoice_data, ['id' => $invoice_id]);
            $wpdb->delete($wpdb->prefix . 'emdad_invoice_items', ['invoice_id' => $invoice_id]);
        } else {
            $invoice_data['invoice_number'] = EmdadInvoice_DB::generate_invoice_number($type);
            $invoice_data['created_at']     = current_time('mysql');
            $invoice_data['created_by']     = get_current_user_id();
            $result = $wpdb->insert($table, $invoice_data);
            if ($result === false) {
                wp_die(
                    '<h2>خطا در ثبت فاکتور</h2>' .
                    '<p>فاکتور ذخیره نشد. خطای دیتابیس:</p>' .
                    '<pre>' . esc_html($wpdb->last_error) . '</pre>' .
                    '<p>شماره فاکتور: <strong>' . esc_html($invoice_data['invoice_number']) . '</strong></p>' .
                    '<p><a href="' . esc_url(admin_url('admin.php?page=emdad-invoice-new')) . '">بازگشت به فرم فاکتور ←</a></p>',
                    'خطا در ثبت فاکتور',
                    ['response' => 500, 'back_link' => false]
                );
            }
            $invoice_id = $wpdb->insert_id;
        }

        foreach ($item_rows as $i => $row) {
            $row['invoice_id'] = $invoice_id;
            $row['sort_order'] = $i;
            $wpdb->insert($wpdb->prefix . 'emdad_invoice_items', $row);
        }
        return $invoice_id;
    }

    /**
     * تبدیل تاریخ شمسی (Y/m/d یا Y-m-d) به میلادی برای ذخیره در MySQL
     * اگر تاریخ خالی یا از قبل میلادی بود همان رو برمی‌گردونه
     */
    private function jalali_to_gregorian_date($date_str) {
        if (empty($date_str)) return '';

        // اگر jdate_to_gregorian تابع وجود داشت (پلاگین Persian Woocommerce و مشابه)
        $clean = str_replace('/', '-', trim($date_str));
        $parts = explode('-', $clean);
        if (count($parts) !== 3) return $date_str;

        $jy = intval($parts[0]);
        $jm = intval($parts[1]);
        $jd = intval($parts[2]);

        // اگر سال > 1600 یعنی میلادیه، نیازی به تبدیل نیست
        if ($jy > 1600) return $date_str;

        // الگوریتم تبدیل جلالی به گرگوری
        $jy -= 979;
        $jm -= 1;
        $jd -= 1;
        $j_day_no = 365 * $jy + intval($jy / 33) * 8 + intval(($jy % 33 + 3) / 4);
        for ($i = 0; $i < $jm; ++$i) $j_day_no += [31,28,31,30,31,30,31,31,30,31,30,31][$i < 6 ? $i*2 : 1] + ($i < 6 ? 0 : ($i === 6 ? 0 : 0));
        // ماه‌های جلالی
        $jmonth_days = [31,31,31,31,31,31,30,30,30,30,30,29];
        $j_day_no = 365*$jy + (int)($jy/33)*8 + (int)(($jy%33+3)/4);
        for ($i=0; $i<$jm; $i++) $j_day_no += $jmonth_days[$i];
        $j_day_no += $jd;
        $g_day_no = $j_day_no + 79;
        $gy = 1600 + 400 * (int)($g_day_no / 146097);
        $g_day_no = $g_day_no % 146097;
        $leap = true;
        if ($g_day_no >= 36525) { $g_day_no--; $gy += 100*(int)($g_day_no/36524); $g_day_no = $g_day_no%36524; if ($g_day_no >= 365) $g_day_no++; else $leap = false; }
        $gy += 4*(int)($g_day_no/1461);
        $g_day_no %= 1461;
        if ($g_day_no >= 366) { $leap = false; $g_day_no--; $gy += (int)($g_day_no/365); $g_day_no = $g_day_no%365; }
        $g_days_in_month = [31, $leap?29:28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $gm = 0;
        for ($i=0; $i<12; $i++) { $gm++; if ($g_day_no < $g_days_in_month[$i]) break; $g_day_no -= $g_days_in_month[$i]; }
        $gd = $g_day_no + 1;
        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    private function save_settings($data) {
        $settings = [
            // اطلاعات شرکت
            'company_name'         => sanitize_text_field($data['company_name'] ?? ''),
            'company_address'      => sanitize_textarea_field($data['company_address'] ?? ''),
            'company_phone'        => sanitize_text_field($data['company_phone'] ?? ''),
            'company_mobile'       => sanitize_text_field($data['company_mobile'] ?? ''),
            'company_postal_code'  => sanitize_text_field($data['company_postal_code'] ?? ''),
            'company_website'      => sanitize_text_field($data['company_website'] ?? ''),
            'company_email'        => sanitize_email($data['company_email'] ?? ''),
            'logo_url'             => esc_url_raw($data['logo_url'] ?? ''),
            // اطلاعات بانکی
            'bank_card_number'     => sanitize_text_field($data['bank_card_number'] ?? ''),
            'bank_sheba'           => sanitize_text_field($data['bank_sheba'] ?? ''),
            'bank_name'            => sanitize_text_field($data['bank_name'] ?? ''),
            'bank_account_owner'   => sanitize_text_field($data['bank_account_owner'] ?? ''),
            // اطلاعات رسمی / مالیاتی
            'seller_economic_code' => sanitize_text_field($data['seller_economic_code'] ?? ''),
            'company_national_id'  => sanitize_text_field($data['company_national_id'] ?? ''),
            'seller_name'          => sanitize_text_field($data['seller_name'] ?? ''),
            // پیش‌فرض‌های مالی
            'tax_percent'          => floatval($data['tax_percent'] ?? 9),
            'currency_label'       => sanitize_text_field($data['currency_label'] ?? 'تومان'),
            // متون پیش‌فرض
            'default_notes'        => sanitize_textarea_field($data['default_notes'] ?? ''),
            'default_terms'        => sanitize_textarea_field($data['default_terms'] ?? ''),
            // درگاه پرداخت
            'zarrinpal_merchant'   => sanitize_text_field($data['zarrinpal_merchant'] ?? ''),
            'zarrinpal_sandbox'    => sanitize_text_field($data['zarrinpal_sandbox'] ?? '0'),
        ];
        update_option('emdad_invoice_settings', $settings);
    }

    public function ajax_register_payment() {
        if (!current_user_can('manage_options')) wp_send_json_error('دسترسی ندارید');
        $invoice_id = intval($_POST['invoice_id'] ?? 0);
        $amount     = floatval(str_replace(',', '', $_POST['amount'] ?? 0));
        $method     = sanitize_text_field($_POST['method'] ?? 'cash');
        $ref        = sanitize_text_field($_POST['reference_code'] ?? '');
        if ($amount <= 0 || $invoice_id <= 0) wp_send_json_error('مبلغ نامعتبر است');

        $result = EmdadInvoice_DB::record_payment($invoice_id, $amount, $method, $ref);
        wp_send_json_success($result);
    }

    public function ajax_change_status() {
        if (!current_user_can('manage_options')) wp_send_json_error();
        global $wpdb;
        $id     = intval($_POST['invoice_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $allowed = ['draft','sent','paid','partial','cancelled'];
        if (!in_array($status, $allowed)) wp_send_json_error('وضعیت نامعتبر');

        $invoice = EmdadInvoice_DB::get_invoice($id);
        if (!$invoice) wp_send_json_error('فاکتور یافت نشد');

        // محاسبه مجموع پرداخت‌های واقعی ثبت‌شده
        $total_paid_real = (float)$wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}emdad_invoice_payments WHERE invoice_id=%d AND status='success'",
            $id
        ));

        $update_data = [
            'status'     => $status,
            'updated_at' => current_time('mysql'),
        ];

        if ($status === 'paid') {
            // پرداخت کامل دستی: paid_amount کامل، remaining صفر
            $update_data['paid_amount'] = $invoice->total;
            $update_data['remaining']   = 0;
        } elseif ($status === 'cancelled') {
            $update_data['remaining'] = 0;
        } elseif (in_array($status, ['sent', 'draft', 'partial'])) {
            // برگشت به حالت قبل: remaining را بر اساس پرداخت‌های واقعی محاسبه کن
            $update_data['paid_amount'] = $total_paid_real;
            $update_data['remaining']   = max(0, $invoice->total - $total_paid_real);

            // اگر remaining بعد از محاسبه صفر شد ولی ادمین sent انتخاب کرده، آن را نگه‌دار
            // (ممکنه پرداخت‌های ثبت‌شده کامل باشن — در این حالت partial منطقی‌تر است)
            if ($update_data['remaining'] <= 0 && $total_paid_real > 0) {
                $update_data['status'] = 'paid';
                $status = 'paid';
            }
        }

        $wpdb->update($wpdb->prefix . 'emdad_invoices', $update_data, ['id' => $id]);

        wp_send_json_success([
            'new_status'       => $status,
            'paid_amount'      => $update_data['paid_amount'] ?? $invoice->paid_amount,
            'remaining'        => $update_data['remaining']   ?? $invoice->remaining,
            'total_paid_real'  => $total_paid_real,
        ]);
    }

    public function ajax_delete_invoice() {
        if (!current_user_can('manage_options')) wp_send_json_error();
        global $wpdb;
        $id = intval($_POST['invoice_id'] ?? 0);
        $wpdb->delete($wpdb->prefix . 'emdad_invoice_items',    ['invoice_id' => $id]);
        $wpdb->delete($wpdb->prefix . 'emdad_invoice_payments', ['invoice_id' => $id]);
        $wpdb->delete($wpdb->prefix . 'emdad_invoices',         ['id' => $id]);
        wp_send_json_success();
    }

    public function ajax_get_invoice_data() {
        if (!current_user_can('manage_options')) wp_send_json_error();
        $id      = intval($_POST['invoice_id'] ?? 0);
        $invoice = EmdadInvoice_DB::get_invoice($id);
        $items   = EmdadInvoice_DB::get_invoice_items($id);
        $payments= EmdadInvoice_DB::get_invoice_payments($id);
        wp_send_json_success(['invoice' => $invoice, 'items' => $items, 'payments' => $payments]);
    }

    public function ajax_search_customers() {
        if (!current_user_can('manage_options')) wp_send_json_error();
        global $wpdb;
        $q = '%' . $wpdb->esc_like(sanitize_text_field($_POST['q'] ?? '')) . '%';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}emdad_customers WHERE name LIKE %s OR phone LIKE %s OR company_name LIKE %s LIMIT 10",
            $q, $q, $q
        ));
        wp_send_json_success($results);
    }

    /* ──────────────────────── PAGES ──────────────────────── */

    public function page_erp() {
        $active_tab = sanitize_text_field($_GET['tab'] ?? 'invoices');
        include EMDAD_INVOICE_DIR . 'views/admin-erp.php';
    }

    public function page_invoices() {
        global $wpdb;
        $status_filter  = sanitize_text_field($_GET['status'] ?? 'all');
        $search         = sanitize_text_field($_GET['search'] ?? '');
        $current_page   = max(1, intval($_GET['paged'] ?? 1));
        $per_page       = 20;
        $offset         = ($current_page - 1) * $per_page;

        $where  = "WHERE 1=1";
        $params = [];
        if ($status_filter !== 'all') { $where .= " AND status = %s"; $params[] = $status_filter; }
        if ($search) { $where .= " AND (customer_name LIKE %s OR invoice_number LIKE %s OR customer_phone LIKE %s)"; $q = "%$search%"; $params[] = $q; $params[] = $q; $params[] = $q; }

        $sql_count = "SELECT COUNT(*) FROM {$wpdb->prefix}emdad_invoices $where";
        $sql_rows  = "SELECT * FROM {$wpdb->prefix}emdad_invoices $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params_count = $params;
        $params_rows  = array_merge($params, [$per_page, $offset]);

        $total   = $params_count ? (int)$wpdb->get_var($wpdb->prepare($sql_count, ...$params_count)) : (int)$wpdb->get_var($sql_count);
        $invoices= $params_rows  ? $wpdb->get_results($wpdb->prepare($sql_rows,  ...$params_rows))  : $wpdb->get_results(str_replace('%d OFFSET %d', $per_page . ' OFFSET ' . $offset, $sql_rows));
        $total_pages = ceil($total / $per_page);

        // Summary stats
        $stats = $wpdb->get_row("SELECT
            SUM(total) as total_amount,
            SUM(paid_amount) as total_paid,
            SUM(remaining) as total_remaining,
            COUNT(*) as total_count,
            SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status='sent' OR status='partial' THEN 1 ELSE 0 END) as pending_count
            FROM {$wpdb->prefix}emdad_invoices");

        include EMDAD_INVOICE_DIR . 'views/admin-invoices.php';
    }

    public function page_invoice_form() {
        $edit_invoice = null;
        $edit_items   = [];
        $settings     = get_option('emdad_invoice_settings', []);
        if (isset($_GET['edit'])) {
            $edit_invoice = EmdadInvoice_DB::get_invoice(intval($_GET['edit']));
            $edit_items   = EmdadInvoice_DB::get_invoice_items(intval($_GET['edit']));
        }
        include EMDAD_INVOICE_DIR . 'views/admin-invoice-form.php';
    }

    public function page_settings() {
        $settings = get_option('emdad_invoice_settings', []);
        include EMDAD_INVOICE_DIR . 'views/admin-settings.php';
    }
}
