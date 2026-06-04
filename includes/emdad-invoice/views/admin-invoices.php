<?php if (!defined('ABSPATH')) exit;
$status_labels = ['draft'=>'پیش‌نویس','sent'=>'ارسال شده','paid'=>'پرداخت شده','partial'=>'پرداخت ناقص','cancelled'=>'لغو شده'];
$current_status = sanitize_text_field($_GET['status'] ?? 'all');
$search = sanitize_text_field($_GET['search'] ?? '');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">📋 مدیریت فاکتورها</h1>
    <a href="<?php echo admin_url('admin.php?page=emdad-invoice-new'); ?>" class="page-title-action">+ فاکتور جدید</a>

    <!-- Stats Cards -->
    <div class="emdad-admin-stats">
        <div class="stat-card">
            <h4>کل فاکتورها</h4>
            <strong><?php echo number_format($stats->total_count ?? 0); ?></strong>
        </div>
        <div class="stat-card green">
            <h4>کل مبالغ (تومان)</h4>
            <strong><?php echo number_format($stats->total_amount ?? 0); ?></strong>
        </div>
        <div class="stat-card green">
            <h4>پرداخت شده (تومان)</h4>
            <strong><?php echo number_format($stats->total_paid ?? 0); ?></strong>
        </div>
        <div class="stat-card red">
            <h4>مانده (تومان)</h4>
            <strong><?php echo number_format($stats->total_remaining ?? 0); ?></strong>
        </div>
        <div class="stat-card">
            <h4>تسویه شده</h4>
            <strong><?php echo number_format($stats->paid_count ?? 0); ?></strong>
        </div>
        <div class="stat-card red">
            <h4>در انتظار پرداخت</h4>
            <strong><?php echo number_format($stats->pending_count ?? 0); ?></strong>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="emdad-filters">
        <a href="<?php echo admin_url('admin.php?page=emdad-invoices'); ?>" class="<?php echo $current_status === 'all' ? 'active' : ''; ?>">همه</a>
        <?php foreach ($status_labels as $k => $v): ?>
        <a href="<?php echo admin_url('admin.php?page=emdad-invoices&status=' . $k); ?>" class="<?php echo $current_status === $k ? 'active' : ''; ?>"><?php echo esc_html($v); ?></a>
        <?php endforeach; ?>
        <form method="get" action="<?php echo admin_url('admin.php'); ?>" class="emdad-search" style="display:flex;gap:6px;margin-right:auto;">
            <input type="hidden" name="page" value="emdad-invoices">
            <input type="hidden" name="status" value="<?php echo esc_attr($current_status); ?>">
            <input type="text" name="search" placeholder="جستجو (نام، شماره، تلفن)..." value="<?php echo esc_attr($search); ?>">
            <button type="submit" class="button">🔍</button>
            <?php if ($search): ?><a href="<?php echo admin_url('admin.php?page=emdad-invoices'); ?>" class="button">✕</a><?php endif; ?>
        </form>
    </div>

    <?php if (isset($_GET['saved'])): ?>
    <div class="notice notice-success is-dismissible"><p>✅ فاکتور با موفقیت ذخیره شد!</p></div>
    <?php endif; ?>

    <!-- Table -->
    <table class="emdad-table widefat">
        <thead>
            <tr>
                <th>شماره</th>
                <th>نام مشتری</th>
                <th>تلفن</th>
                <th>نوع</th>
                <th>تاریخ صدور</th>
                <th>سررسید</th>
                <th>مبلغ کل (تومان)</th>
                <th>پرداخت شده</th>
                <th>مانده</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($invoices)): foreach ($invoices as $inv):
            $type_map = ['official'=>'رسمی','unofficial'=>'غیررسمی','repair'=>'تعمیرات'];
            $issue = function_exists('jdate') ? jdate('Y/m/d', strtotime($inv->issue_date ?: $inv->created_at)) : ($inv->issue_date ?: date('Y/m/d', strtotime($inv->created_at)));
            $due   = $inv->due_date ? (function_exists('jdate') ? jdate('Y/m/d', strtotime($inv->due_date)) : $inv->due_date) : '—';
        ?>
        <tr>
            <td><strong><?php echo esc_html($inv->invoice_number); ?></strong></td>
            <td><?php echo esc_html($inv->customer_name); ?></td>
            <td><?php echo esc_html($inv->customer_phone); ?></td>
            <td><?php echo esc_html($type_map[$inv->type] ?? $inv->type); ?></td>
            <td><?php echo esc_html($issue); ?></td>
            <td><?php echo esc_html($due); ?></td>
            <td><?php echo number_format($inv->total); ?></td>
            <td style="color:var(--green)"><?php echo number_format($inv->paid_amount); ?></td>
            <td style="color:<?php echo $inv->remaining > 0 ? 'var(--red)' : 'var(--green)'; ?>">
                <?php echo number_format($inv->remaining); ?>
            </td>
            <td>
                <span class="status-badge status-<?php echo esc_attr($inv->status); ?>">
                    <?php echo esc_html($status_labels[$inv->status] ?? $inv->status); ?>
                </span>
            </td>
            <td style="white-space:nowrap;display:flex;gap:4px;justify-content:center;">
                <a href="<?php echo home_url('/faktur/' . $inv->invoice_number . '/'); ?>" target="_blank" class="btn-sm btn-view">مشاهده</a>
                <a href="<?php echo admin_url('admin.php?page=emdad-invoice-new&edit=' . $inv->id); ?>" class="btn-sm btn-edit">ویرایش</a>
                <button class="btn-sm btn-del emdad-delete-invoice" data-id="<?php echo esc_attr($inv->id); ?>">حذف</button>
            </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="11" style="text-align:center;padding:30px;color:#888;">هیچ فاکتوری یافت نشد.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top:16px;display:flex;gap:6px;align-items:center;">
        <?php for ($p = 1; $p <= $total_pages; $p++):
            $url = add_query_arg(['paged' => $p]);
        ?>
        <a href="<?php echo esc_url($url); ?>" style="padding:6px 12px;border-radius:6px;background:<?php echo $p == $current_page ? 'var(--c1)' : '#eee'; ?>;color:<?php echo $p == $current_page ? '#fff' : '#333'; ?>">
            <?php echo $p; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.emdad-delete-invoice').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('آیا مطمئن هستید؟ این فاکتور به طور کامل حذف خواهد شد.')) return;
        var id = this.dataset.id;
        var data = new FormData();
        data.append('action', 'emdad_delete_invoice');
        data.append('invoice_id', id);
        data.append('_ajax_nonce', '<?php echo wp_create_nonce('emdad_admin'); ?>');
        fetch(ajaxurl, {method: 'POST', body: data})
            .then(r => r.json())
            .then(function(res) {
                if (res.success) {
                    btn.closest('tr').remove();
                } else {
                    alert('خطا در حذف');
                }
            });
    });
});
</script>
