<?php if (!defined('ABSPATH')) exit;
$active_tab = sanitize_text_field($_GET['tab'] ?? 'invoices');
?>
<div class="wrap">
    <h1 style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:28px;">🏢</span> سیستم ERP امداد
    </h1>

    <nav class="nav-tab-wrapper emdad-erp-tabs" style="margin-top:16px;">
        <a href="<?php echo admin_url('admin.php?page=emdad-erp&tab=invoices'); ?>"
           class="nav-tab <?php echo $active_tab === 'invoices' ? 'nav-tab-active' : ''; ?>">
            📋 فاکتورها
        </a>
        <a href="#" class="nav-tab emdad-tab-disabled" style="opacity:.45;cursor:not-allowed;" title="به زودی">
            💰 حسابداری
        </a>
        <a href="#" class="nav-tab emdad-tab-disabled" style="opacity:.45;cursor:not-allowed;" title="به زودی">
            📦 انبارداری
        </a>
    </nav>

    <div class="emdad-erp-tab-content">

        <?php if ($active_tab === 'invoices'): ?>
        <?php
        /* ── بارگذاری داده‌های فاکتور ── */
        global $wpdb;
        $status_filter = sanitize_text_field($_GET['status'] ?? 'all');
        $search        = sanitize_text_field($_GET['search'] ?? '');
        $filter_date   = sanitize_text_field($_GET['filter_date'] ?? ''); // دریافت متغیر تاریخ
        
        $current_page  = max(1, intval($_GET['paged'] ?? 1));
        $per_page      = 20;
        $offset        = ($current_page - 1) * $per_page;

        $where  = "WHERE 1=1";
        $params = [];
        
        if ($status_filter !== 'all') { 
            $where .= " AND status = %s"; 
            $params[] = $status_filter; 
        }
        
        if ($search) {
            $where .= " AND (customer_name LIKE %s OR invoice_number LIKE %s OR customer_phone LIKE %s)";
            $q = "%$search%"; 
            $params[] = $q; 
            $params[] = $q; 
            $params[] = $q;
        }

        // پردازش تاریخ: تبدیل ورودی شمسی به فرمت میلادی دیتابیس
        if ($filter_date) {
            $db_search_date = $filter_date;
            $parts = explode('/', $filter_date);
            
            if (count($parts) === 3 && function_exists('jalali_to_gregorian')) {
                $g_date = jalali_to_gregorian((int)$parts[0], (int)$parts[1], (int)$parts[2]);
                if (is_array($g_date)) {
                    $db_search_date = sprintf('%04d-%02d-%02d', $g_date[0], $g_date[1], $g_date[2]);
                } else {
                    $db_search_date = $g_date; 
                }
            }
            
            $where .= " AND (issue_date LIKE %s OR created_at LIKE %s)";
            $dq = "%{$db_search_date}%";
            $params[] = $dq;
            $params[] = $dq;
        }

        $sql_count = "SELECT COUNT(*) FROM {$wpdb->prefix}emdad_invoices $where";
        $sql_rows  = "SELECT * FROM {$wpdb->prefix}emdad_invoices $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params_count = $params;
        $params_rows  = array_merge($params, [$per_page, $offset]);

        $total      = $params_count ? (int)$wpdb->get_var($wpdb->prepare($sql_count, ...$params_count)) : (int)$wpdb->get_var($sql_count);
        $invoices   = $params_rows  ? $wpdb->get_results($wpdb->prepare($sql_rows,   ...$params_rows))  : $wpdb->get_results(str_replace('%d OFFSET %d', $per_page . ' OFFSET ' . $offset, $sql_rows));
        $total_pages = ceil($total / $per_page);

        $stats = $wpdb->get_row("SELECT
            SUM(total) as total_amount,
            SUM(paid_amount) as total_paid,
            SUM(remaining) as total_remaining,
            COUNT(*) as total_count,
            SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status='sent' OR status='partial' THEN 1 ELSE 0 END) as pending_count
            FROM {$wpdb->prefix}emdad_invoices");
        ?>
        <?php include EMDAD_INVOICE_DIR . 'views/admin-invoices.php'; ?>
        <?php endif; ?>

    </div></div>