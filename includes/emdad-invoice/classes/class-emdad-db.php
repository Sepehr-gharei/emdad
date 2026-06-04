<?php
if (!defined('ABSPATH')) exit;

class EmdadInvoice_DB {

    public function __construct() {
        add_action('after_switch_theme', [$this, 'create_tables']);
        add_action('init', [$this, 'maybe_create_tables']);
    }

    public function maybe_create_tables() {
        if (get_option('emdad_invoice_db_version') !== EMDAD_INVOICE_VERSION) {
            $this->create_tables();
            $this->run_migrations();
        }
    }

    /**
     * اضافه کردن ستون‌های جدید به جداول موجود
     * برای سایت‌هایی که جدول قبلاً بدون این ستون‌ها ساخته شده
     */
    public function run_migrations() {
        global $wpdb;
        $table = $wpdb->prefix . 'emdad_invoices';

        $existing = $wpdb->get_col("DESCRIBE `{$table}`", 0);

        $to_add = [
            'customer_city'       => "VARCHAR(100) DEFAULT '' AFTER `customer_address`",
            'customer_email'      => "VARCHAR(100) DEFAULT '' AFTER `customer_city`",
            'seller_name'         => "VARCHAR(200) DEFAULT '' AFTER `customer_email`",
            'seller_phone'        => "VARCHAR(20)  DEFAULT '' AFTER `seller_name`",
            'seller_company'      => "VARCHAR(200) DEFAULT '' AFTER `seller_phone`",
            'seller_economic_code'=> "VARCHAR(50)  DEFAULT '' AFTER `seller_company`",
            'seller_address'      => "TEXT AFTER `seller_economic_code`",
            'footer_description'  => "TEXT AFTER `terms`",
            'paid_amount'         => "DECIMAL(15,0) DEFAULT 0 AFTER `remaining`",
        ];

        foreach ($to_add as $col => $def) {
            if (!in_array($col, $existing)) {
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}");
            }
        }
    }

    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql_customers = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}emdad_customers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            phone VARCHAR(20) DEFAULT '',
            national_code VARCHAR(20) DEFAULT '',
            company_name VARCHAR(200) DEFAULT '',
            address TEXT DEFAULT '',
            email VARCHAR(100) DEFAULT '',
            notes TEXT DEFAULT '',
            balance DECIMAL(15,0) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        $sql_products = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}emdad_invoice_products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(300) NOT NULL,
            code VARCHAR(50) DEFAULT '',
            description TEXT DEFAULT '',
            price DECIMAL(15,0) DEFAULT 0,
            unit VARCHAR(50) DEFAULT 'عدد',
            type ENUM('product','service') DEFAULT 'product',
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        $sql_invoices = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}emdad_invoices (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            invoice_number VARCHAR(50) NOT NULL UNIQUE,
            type ENUM('official','unofficial','repair') DEFAULT 'official',
            skin VARCHAR(50) DEFAULT 'modern',
            status ENUM('draft','sent','paid','partial','cancelled') DEFAULT 'draft',
            customer_id BIGINT UNSIGNED DEFAULT NULL,
            customer_name VARCHAR(200) DEFAULT '',
            customer_phone VARCHAR(20) DEFAULT '',
            customer_national_code VARCHAR(20) DEFAULT '',
            customer_company VARCHAR(200) DEFAULT '',
            customer_address TEXT DEFAULT '',
            customer_city VARCHAR(100) DEFAULT '',
            customer_email VARCHAR(100) DEFAULT '',
            seller_name VARCHAR(200) DEFAULT '',
            seller_phone VARCHAR(20) DEFAULT '',
            seller_national_code VARCHAR(20) DEFAULT '',
            seller_company VARCHAR(200) DEFAULT '',
            seller_address TEXT DEFAULT '',
            seller_economic_code VARCHAR(50) DEFAULT '',
            subtotal DECIMAL(15,0) DEFAULT 0,
            discount DECIMAL(15,0) DEFAULT 0,
            discount_percent DECIMAL(5,2) DEFAULT 0,
            tax DECIMAL(15,0) DEFAULT 0,
            tax_percent DECIMAL(5,2) DEFAULT 9,
            total DECIMAL(15,0) DEFAULT 0,
            paid_amount DECIMAL(15,0) DEFAULT 0,
            remaining DECIMAL(15,0) DEFAULT 0,
            notes TEXT DEFAULT '',
            terms TEXT DEFAULT '',
            footer_description TEXT DEFAULT '',
            issue_date DATE DEFAULT NULL,
            due_date DATE DEFAULT NULL,
            payment_method VARCHAR(100) DEFAULT '',
            zarrinpal_authority VARCHAR(100) DEFAULT '',
            zarrinpal_ref_id VARCHAR(100) DEFAULT '',
            created_by BIGINT UNSIGNED DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        // ── product_code و tax_percent اضافه شد ──
        $sql_items = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}emdad_invoice_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            invoice_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED DEFAULT NULL,
            product_code VARCHAR(100) DEFAULT '',
            name VARCHAR(300) NOT NULL,
            description TEXT DEFAULT '',
            quantity DECIMAL(10,3) DEFAULT 1,
            unit VARCHAR(50) DEFAULT 'عدد',
            unit_price DECIMAL(15,0) DEFAULT 0,
            discount DECIMAL(15,0) DEFAULT 0,
            discount_percent DECIMAL(5,2) DEFAULT 0,
            tax_percent DECIMAL(5,2) DEFAULT 0,
            total DECIMAL(15,0) DEFAULT 0,
            sort_order INT DEFAULT 0
        ) $charset_collate;";

        $sql_payments = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}emdad_invoice_payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            invoice_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(15,0) NOT NULL,
            method ENUM('cash','card','online','check','other') DEFAULT 'cash',
            reference_code VARCHAR(200) DEFAULT '',
            gateway VARCHAR(50) DEFAULT '',
            status ENUM('pending','success','failed') DEFAULT 'pending',
            notes TEXT DEFAULT '',
            paid_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        $sql_ledger = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}emdad_customer_ledger (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id BIGINT UNSIGNED NOT NULL,
            invoice_id BIGINT UNSIGNED DEFAULT NULL,
            type ENUM('invoice','payment','adjustment') DEFAULT 'invoice',
            debit DECIMAL(15,0) DEFAULT 0,
            credit DECIMAL(15,0) DEFAULT 0,
            balance DECIMAL(15,0) DEFAULT 0,
            description TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_customers); dbDelta($sql_products); dbDelta($sql_invoices);
        dbDelta($sql_items); dbDelta($sql_payments); dbDelta($sql_ledger);

        // اگر جدول قدیمی بود ستون‌های جدید رو اضافه کن
        $col_code = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->prefix}emdad_invoice_items LIKE 'product_code'");
        if (empty($col_code)) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}emdad_invoice_items ADD COLUMN product_code VARCHAR(100) DEFAULT '' AFTER product_id");
        }
        $col_tax = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->prefix}emdad_invoice_items LIKE 'tax_percent'");
        if (empty($col_tax)) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}emdad_invoice_items ADD COLUMN tax_percent DECIMAL(5,2) DEFAULT 0 AFTER discount_percent");
        }

        update_option('emdad_invoice_db_version', EMDAD_INVOICE_VERSION);
    }

    public static function get_invoice($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}emdad_invoices WHERE id = %d", $id));
    }

    public static function get_invoice_items($invoice_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}emdad_invoice_items WHERE invoice_id = %d ORDER BY sort_order ASC", $invoice_id));
    }

    public static function get_invoice_payments($invoice_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}emdad_invoice_payments WHERE invoice_id = %d AND status='success' ORDER BY paid_at DESC", $invoice_id));
    }

    public static function get_customer($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}emdad_customers WHERE id = %d", $id));
    }

    public static function get_customer_ledger($customer_id, $limit = 50) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT l.*, i.invoice_number FROM {$wpdb->prefix}emdad_customer_ledger l LEFT JOIN {$wpdb->prefix}emdad_invoices i ON l.invoice_id = i.id WHERE l.customer_id = %d ORDER BY l.created_at DESC LIMIT %d", $customer_id, $limit));
    }

    public static function generate_invoice_number($type = 'official') {
        global $wpdb;
        $prefix = $type === 'official' ? 'R' : ($type === 'repair' ? 'T' : 'G');
        $year = function_exists('jdate') ? jdate('Y') : idate('Y');

        // از MAX به‌جای COUNT استفاده می‌کنیم تا بعد از حذف فاکتور، شماره تکراری تولید نشود
        $like_pattern = $prefix . $year . '%';
        $last_number = $wpdb->get_var($wpdb->prepare(
            "SELECT invoice_number FROM {$wpdb->prefix}emdad_invoices
             WHERE invoice_number LIKE %s
             ORDER BY id DESC LIMIT 1",
            $like_pattern
        ));

        if ($last_number) {
            // آخرین عدد را استخراج کن و یکی اضافه کن
            $last_seq = (int) substr($last_number, strlen($prefix . $year));
            $next_seq = $last_seq + 1;
        } else {
            $next_seq = 1;
        }

        // اگر شماره تولیدشده تکراری بود (race condition)، تا پیدا کردن شماره آزاد ادامه بده
        $attempt = 0;
        do {
            $candidate = $prefix . $year . str_pad($next_seq + $attempt, 4, '0', STR_PAD_LEFT);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}emdad_invoices WHERE invoice_number = %s",
                $candidate
            ));
            $attempt++;
        } while ($exists > 0 && $attempt < 100);

        return $candidate;
    }

    public static function update_customer_balance($customer_id) {
        global $wpdb;
        $total_debit  = (float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(debit),0)  FROM {$wpdb->prefix}emdad_customer_ledger WHERE customer_id=%d", $customer_id));
        $total_credit = (float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(credit),0) FROM {$wpdb->prefix}emdad_customer_ledger WHERE customer_id=%d", $customer_id));
        $balance = $total_credit - $total_debit;
        $wpdb->update("{$wpdb->prefix}emdad_customers", ['balance' => $balance, 'updated_at' => current_time('mysql')], ['id' => $customer_id]);
    }
}
