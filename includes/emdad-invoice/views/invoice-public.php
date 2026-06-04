<?php if (!defined('ABSPATH')) exit;
$settings = get_option('emdad_invoice_settings', []);
$company_name        = $settings['company_name']         ?? 'گروه مهندسی امداد';
$company_address     = $settings['company_address']      ?? '';
$company_phone       = $settings['company_phone']        ?? '';
$company_mobile      = $settings['company_mobile']       ?? '';
$company_website     = $settings['company_website']      ?? '';
$company_email       = $settings['company_email']        ?? '';
$company_postal_code = $settings['company_postal_code']  ?? '';
$company_national_id = $settings['company_national_id']  ?? '';
$seller_economic_code= $settings['seller_economic_code'] ?? '';
$bank_card_number    = $settings['bank_card_number']     ?? '';
$bank_sheba          = $settings['bank_sheba']           ?? '';
$bank_name           = $settings['bank_name']            ?? '';
$bank_account_owner  = $settings['bank_account_owner']   ?? '';
$currency            = $settings['currency_label']       ?? 'تومان';
$logo_url            = $settings['logo_url']             ?? '';

$type_label  = $invoice->type === 'official' ? 'فاکتور رسمی فروش' : ($invoice->type === 'repair' ? 'رسید تعمیرات' : 'پیش‌فاکتور فروش');
$status_map  = ['draft'=>'پیش‌نویس','sent'=>'ارسال شده','paid'=>'پرداخت شده','partial'=>'پرداخت ناقص','cancelled'=>'لغو شده'];
$status_color= ['draft'=>'#95a5a6','sent'=>'#f59e0b','paid'=>'#22c55e','partial'=>'#f97316','cancelled'=>'#ef4444'];
$status_label= $status_map[$invoice->status] ?? $invoice->status;
$s_color     = $status_color[$invoice->status] ?? '#333';

$issue_date = function_exists('jdate') ? jdate('Y/m/d', strtotime($invoice->issue_date ?: $invoice->created_at)) : ($invoice->issue_date ?: date('Y/m/d'));
$due_date   = !empty($invoice->due_date) ? (function_exists('jdate') ? jdate('Y/m/d', strtotime($invoice->due_date)) : $invoice->due_date) : '';
$invoice_page_url = home_url('/faktur/' . $invoice->invoice_number . '/');
$method_map  = ['cash'=>'نقد','card'=>'کارت','online'=>'آنلاین','check'=>'چک','other'=>'سایر'];

/* فیلدهای اختیاری */
$customer_city      = $invoice->customer_city      ?? '';
$customer_email     = $invoice->customer_email     ?? '';
$footer_description = $invoice->footer_description ?? '';
$seller_econ        = !empty($invoice->seller_economic_code) ? $invoice->seller_economic_code : $seller_economic_code;

/* تبدیل عدد به حروف - نسخه ساده */
function emdad_number_to_words($num) {
    $num = (int)$num;
    if ($num == 0) return 'صفر';
    $ones  = ['','یک','دو','سه','چهار','پنج','شش','هفت','هشت','نه','ده','یازده','دوازده','سیزده','چهارده','پانزده','شانزده','هفده','هجده','نوزده'];
    $tens  = ['','','بیست','سی','چهل','پنجاه','شصت','هفتاد','هشتاد','نود'];
    $scale = ['','هزار','میلیون','میلیارد'];
    $parts = [];
    $i = 0;
    while ($num > 0) {
        $chunk = $num % 1000;
        if ($chunk != 0) {
            $w = '';
            if ($chunk >= 100) { $hundreds=['','یکصد','دویست','سیصد','چهارصد','پانصد','ششصد','هفتصد','هشتصد','نهصد']; $w .= $hundreds[(int)($chunk/100)]; $chunk %= 100; if($chunk) $w .= ' و '; }
            if ($chunk >= 20)  { $w .= $tens[(int)($chunk/10)]; if($chunk%10) $w .= ' و ' . $ones[$chunk%10]; }
            elseif ($chunk > 0){ $w .= $ones[$chunk]; }
            $parts[] = $w . ($scale[$i] ? ' ' . $scale[$i] : '');
        }
        $num = (int)($num / 1000);
        $i++;
    }
    return implode(' و ', array_reverse($parts));
}
$total_words = emdad_number_to_words((int)$invoice->total);
$remaining_words = emdad_number_to_words((int)$invoice->remaining);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($type_label); ?> <?php echo esc_html($invoice->invoice_number); ?></title>
    <link rel="stylesheet" href="<?php echo EMDAD_INVOICE_URL . 'assets/css/invoice.css'; ?>">
</head>
<body>

<?php if ($invoice->remaining > 0 && $invoice->status !== 'cancelled'): ?>
<div class="emdad-pay-bar">
    <div class="pay-bar-inner">
        <div>
            <span class="pay-bar-label">مبلغ قابل پرداخت:</span>
            <strong class="pay-bar-amount"><?php echo number_format($invoice->remaining); ?> <?php echo esc_html($currency); ?></strong>
        </div>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action"     value="emdad_pay_invoice">
            <input type="hidden" name="invoice_id" value="<?php echo esc_attr($invoice->id); ?>">
            <button type="submit" class="pay-bar-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                پرداخت آنلاین (زرین‌پال)
            </button>
        </form>
    </div>
</div>
<?php elseif ($invoice->status === 'paid'): ?>
<div class="emdad-pay-bar paid">
    <div class="pay-bar-inner" style="justify-content:center">
        <strong>✅ این فاکتور کاملاً تسویه شده است</strong>
    </div>
</div>
<?php endif; ?>

<div class="emdad-invoice-wrap">
<div class="emdad-invoice-container">
<div class="invoice-top-bar"></div>

<!-- ═══════════════════════ HEADER ═══════════════════════ -->
<header class="invoice-header">

    <div class="header-right">
        <?php if ($logo_url): ?>
        <div class="company-logo"><img src="<?php echo esc_url($logo_url); ?>" alt="logo"></div>
        <?php endif; ?>
        <div class="company-details">
            <h2><?php echo esc_html($company_name); ?></h2>
            <?php if ($company_phone):   ?><p><span class="info-icon">📞</span> <?php echo esc_html($company_phone); ?></p><?php endif; ?>
            <?php if ($company_mobile):  ?><p><span class="info-icon">📱</span> <?php echo esc_html($company_mobile); ?></p><?php endif; ?>
            <?php if ($company_address): ?><p><span class="info-icon">📍</span> <?php echo esc_html($company_address); ?></p><?php endif; ?>
            <?php if ($company_postal_code): ?><p><span class="info-icon">📮</span> کد پستی: <?php echo esc_html($company_postal_code); ?></p><?php endif; ?>
            <?php if ($company_website): ?><p><span class="info-icon">🌐</span> <?php echo esc_html($company_website); ?></p><?php endif; ?>
            <?php if ($company_email):   ?><p><span class="info-icon">✉️</span> <?php echo esc_html($company_email); ?></p><?php endif; ?>
        </div>
    </div>

    <div class="header-center">
        <div class="bismillah">بِسْمِ اللهِ الرَّحْمَنِ الرَّحِیم</div>
        <h1 class="invoice-type-title"><?php echo esc_html($type_label); ?></h1>
        <div class="invoice-badges">
            <span class="badge" style="background:<?php echo esc_attr($s_color); ?>"><?php echo esc_html($status_label); ?></span>
            <span class="badge badge-num"># <?php echo esc_html($invoice->invoice_number); ?></span>
        </div>
        <div class="invoice-dates">
            <div class="date-row"><span>تاریخ صدور:</span> <strong><?php echo esc_html($issue_date); ?></strong></div>
            <?php if ($due_date): ?><div class="date-row"><span>تاریخ سررسید:</span> <strong><?php echo esc_html($due_date); ?></strong></div><?php endif; ?>
        </div>
    </div>

    <div class="header-left">
        <div class="invoice-qr">
            <img src="<?php echo esc_url($qr_url); ?>" alt="QR" width="110" height="110">
            <p>اسکن برای پرداخت</p>
        </div>
        <div class="invoice-actions-download">
            <a href="<?php echo esc_url(add_query_arg(['download'=>'print','id'=>$invoice->id], $invoice_page_url)); ?>" class="btn-action btn-print" target="_blank">🖨️ چاپ / PDF</a>
            <a href="<?php echo esc_url(add_query_arg(['download'=>'img','id'=>$invoice->id], $invoice_page_url)); ?>"   class="btn-action btn-img"   target="_blank">📷 دانلود تصویر</a>
        </div>
    </div>

</header>

<!-- ═══════════════════════ اطلاعات طرفین ═══════════════════════ -->
<div class="parties-section">

    <!-- فروشنده -->
    <div class="party-box seller-box">
        <h3 class="party-title">🏢 مشخصات فروشنده</h3>
        <div class="party-info">
            <div class="pi-row"><span class="pi-label">نام شرکت:</span><span class="pi-val"><?php echo esc_html($invoice->seller_company ?: $company_name); ?></span></div>
            <?php if ($invoice->seller_name ?: ($settings['seller_name'] ?? '')): ?>
            <div class="pi-row"><span class="pi-label">نام کارشناس:</span><span class="pi-val"><?php echo esc_html($invoice->seller_name ?: ($settings['seller_name'] ?? '')); ?></span></div>
            <?php endif; ?>
            <?php if ($company_address): ?><div class="pi-row"><span class="pi-label">آدرس:</span><span class="pi-val"><?php echo esc_html($invoice->seller_address ?: $company_address); ?></span></div><?php endif; ?>
            <?php if ($company_phone):   ?><div class="pi-row"><span class="pi-label">تلفن:</span><span class="pi-val"><?php echo esc_html($invoice->seller_phone ?: $company_phone); ?></span></div><?php endif; ?>
            <?php if ($company_mobile):  ?><div class="pi-row"><span class="pi-label">موبایل:</span><span class="pi-val"><?php echo esc_html($company_mobile); ?></span></div><?php endif; ?>
            <?php if ($seller_econ):     ?><div class="pi-row"><span class="pi-label">کد اقتصادی:</span><span class="pi-val"><?php echo esc_html($seller_econ); ?></span></div><?php endif; ?>
            <?php if ($company_national_id): ?><div class="pi-row"><span class="pi-label">شناسه ملی:</span><span class="pi-val"><?php echo esc_html($company_national_id); ?></span></div><?php endif; ?>
            <?php if ($company_website): ?><div class="pi-row"><span class="pi-label">وب‌سایت:</span><span class="pi-val"><?php echo esc_html($company_website); ?></span></div><?php endif; ?>
        </div>
    </div>

    <!-- خریدار -->
    <div class="party-box buyer-box">
        <h3 class="party-title">👤 مشخصات خریدار</h3>
        <div class="party-info">
            <div class="pi-row"><span class="pi-label">نام:</span><span class="pi-val"><strong><?php echo esc_html($invoice->customer_name); ?></strong></span></div>
            <?php if (!empty($invoice->customer_company)):      ?><div class="pi-row"><span class="pi-label">شرکت:</span><span class="pi-val"><?php echo esc_html($invoice->customer_company); ?></span></div><?php endif; ?>
            <?php if (!empty($invoice->customer_phone)):        ?><div class="pi-row"><span class="pi-label">تلفن/موبایل:</span><span class="pi-val"><?php echo esc_html($invoice->customer_phone); ?></span></div><?php endif; ?>
            <?php if (!empty($invoice->customer_national_code)):?><div class="pi-row"><span class="pi-label">کد ملی:</span><span class="pi-val"><?php echo esc_html($invoice->customer_national_code); ?></span></div><?php endif; ?>
            <?php if (!empty($customer_city)):                  ?><div class="pi-row"><span class="pi-label">شهر:</span><span class="pi-val"><?php echo esc_html($customer_city); ?></span></div><?php endif; ?>
            <?php if (!empty($invoice->customer_address)):      ?><div class="pi-row"><span class="pi-label">آدرس:</span><span class="pi-val"><?php echo esc_html($invoice->customer_address); ?></span></div><?php endif; ?>
            <?php if (!empty($customer_email)):                 ?><div class="pi-row"><span class="pi-label">ایمیل:</span><span class="pi-val"><?php echo esc_html($customer_email); ?></span></div><?php endif; ?>
        </div>
    </div>

</div>

<!-- ═══════════════════════ جدول اقلام ═══════════════════════ -->
<div class="invoice-table-section">
    <h3 class="invoice-table-section-title">مشخصات کالا / خدمات</h3>
    <div class="invoice-table-wrap">
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width:36px">ردیف</th>
                    <th style="width:80px">کد کالا</th>
                    <th>نام کالا / خدمات و شرح</th>
                    <th style="width:55px">تعداد</th>
                    <th style="width:55px">واحد</th>
                    <th>مبلغ واحد<br><small style="font-weight:400;opacity:.8">(ریال)</small></th>
                    <th>مبلغ کل<br><small style="font-weight:400;opacity:.8">(ریال)</small></th>
                    <th>تخفیف<br><small style="font-weight:400;opacity:.8">(ریال)</small></th>
                    <th>پس از تخفیف<br><small style="font-weight:400;opacity:.8">(ریال)</small></th>
                    <th>مالیات و عوارض<br><small style="font-weight:400;opacity:.8">(ریال)</small></th>
                    <th>مبلغ کل ریال<br><small style="font-weight:400;opacity:.8">(پس از مالیات)</small></th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $item):
                    $line_base      = floatval($item->unit_price) * floatval($item->quantity);
                    $after_discount = $line_base - floatval($item->discount);
                    $item_tax_pct   = floatval($item->tax_percent ?? 0);
                    $item_tax_amt   = round($after_discount * $item_tax_pct / 100);
                    $after_tax      = $after_discount + $item_tax_amt;
                ?>
                <tr>
                    <td><span class="row-num"><?php echo $i++; ?></span></td>
                    <td class="td-code"><?php echo $item->product_code ? esc_html($item->product_code) : '<span class="td-muted">—</span>'; ?></td>
                    <td class="td-desc">
                        <strong><?php echo esc_html($item->name); ?></strong>
                        <?php if ($item->description): ?><small class="item-desc"><?php echo esc_html($item->description); ?></small><?php endif; ?>
                    </td>
                    <td><?php echo number_format(floatval($item->quantity), floatval($item->quantity) == intval($item->quantity) ? 0 : 2); ?></td>
                    <td><?php echo esc_html($item->unit); ?></td>
                    <td><?php echo number_format($item->unit_price); ?></td>
                    <td class="td-total"><?php echo number_format($line_base); ?></td>
                    <td class="td-discount"><?php echo $item->discount > 0 ? number_format($item->discount) : '<span class="td-muted">—</span>'; ?></td>
                    <td class="td-after-disc"><?php echo number_format($after_discount); ?></td>
                    <td class="td-tax">
                        <?php if ($item_tax_pct > 0): ?>
                            <?php echo number_format($item_tax_amt); ?>
                            <small class="tax-pct-badge"><?php echo rtrim(rtrim(number_format($item_tax_pct,2),'0'),'.'); ?>%</small>
                        <?php else: ?><span class="td-muted">—</span><?php endif; ?>
                    </td>
                    <td class="td-after-tax td-total"><?php echo number_format($after_tax); ?></td>
                </tr>
                <?php endforeach; ?>
                <!-- ردیف جمع کل -->
                <tr class="subtotal-row">
                    <td colspan="6" class="subtotal-label" style="text-align:<?php echo is_rtl() ? 'left' : 'right'; ?>">جمع کل اقلام</td>
                    <td class="td-total"><?php echo number_format($invoice->subtotal + $invoice->discount); ?></td>
                    <td class="td-discount"><?php echo $invoice->discount > 0 ? '('.number_format($invoice->discount).')' : '<span class="td-muted">—</span>'; ?></td>
                    <td class="td-after-disc td-total"><?php echo number_format($invoice->subtotal); ?></td>
                    <td class="td-tax"><?php echo $invoice->tax > 0 ? number_format($invoice->tax) : '<span class="td-muted">—</span>'; ?></td>
                    <td class="td-after-tax td-total"><?php echo number_format($invoice->total); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════════════════════ خلاصه مالی ═══════════════════════ -->
<div class="finance-summary-section">

    <!-- توضیحات / اطلاعات بانکی سمت چپ -->
    <div class="finance-left">
        <?php if ($bank_card_number || $bank_sheba): ?>
        <div class="bank-info-box">
            <h4 class="bank-info-title">💳 اطلاعات واریز</h4>
            <?php if ($bank_name): ?><div class="bank-row"><span>بانک:</span><strong><?php echo esc_html($bank_name); ?></strong></div><?php endif; ?>
            <?php if ($bank_account_owner): ?><div class="bank-row"><span>صاحب حساب:</span><strong><?php echo esc_html($bank_account_owner); ?></strong></div><?php endif; ?>
            <?php if ($bank_card_number): ?><div class="bank-row"><span>شماره کارت:</span><strong class="ltr-text"><?php echo esc_html($bank_card_number); ?></strong></div><?php endif; ?>
            <?php if ($bank_sheba): ?><div class="bank-row"><span>شماره شبا:</span><strong class="ltr-text"><?php echo esc_html($bank_sheba); ?></strong></div><?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="words-amount-box">
            <span>مبلغ کل فاکتور به حروف:</span>
            <strong><?php echo esc_html($total_words); ?> ریال</strong>
        </div>
        <?php if ($invoice->remaining > 0 && $invoice->remaining != $invoice->total): ?>
        <div class="words-amount-box words-remaining-box">
            <span>مانده قابل پرداخت به حروف:</span>
            <strong><?php echo esc_html($remaining_words); ?> ریال</strong>
        </div>
        <?php endif; ?>
    </div>

    <!-- جدول اعداد سمت راست -->
    <div class="totals-box">
        <div class="totals-row">
            <span>جمع مبلغ کالا (قبل از تخفیف):</span>
            <strong><?php echo number_format($invoice->subtotal + $invoice->discount); ?> ریال</strong>
        </div>
        <?php if ($invoice->discount > 0): ?>
        <div class="totals-row discount-row">
            <span>تخفیف<?php if($invoice->discount_percent>0): ?> (<?php echo rtrim(rtrim(number_format($invoice->discount_percent,2),'0'),'.'); ?>%)<?php endif; ?>:</span>
            <strong>− <?php echo number_format($invoice->discount); ?> ریال</strong>
        </div>
        <div class="totals-row">
            <span>جمع پس از تخفیف:</span>
            <strong><?php echo number_format($invoice->subtotal); ?> ریال</strong>
        </div>
        <?php endif; ?>
        <?php if ($invoice->tax > 0): ?>
        <div class="totals-row tax-row">
            <span>مالیات و عوارض<?php if($invoice->tax_percent>0): ?> (<?php echo rtrim(rtrim(number_format($invoice->tax_percent,2),'0'),'.'); ?>%)<?php endif; ?>:</span>
            <strong><?php echo number_format($invoice->tax); ?> ریال</strong>
        </div>
        <?php endif; ?>
        <div class="totals-row grand-total">
            <span>مبلغ کل فاکتور (ریال):</span>
            <strong><?php echo number_format($invoice->total); ?></strong>
        </div>
        <?php if ($invoice->paid_amount > 0): ?>
        <div class="totals-row paid-row">
            <span>پرداخت شده:</span>
            <strong><?php echo number_format($invoice->paid_amount); ?> ریال</strong>
        </div>
        <div class="totals-row remaining-row">
            <span>مانده قابل پرداخت:</span>
            <strong><?php echo number_format($invoice->remaining); ?> ریال</strong>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php if (!empty($payments)): ?>
<!-- ═══════════════════════ تاریخچه پرداخت ═══════════════════════ -->
<div class="section-payments">
    <h3 class="section-title">تاریخچه پرداخت‌ها</h3>
    <table class="payments-table">
        <thead><tr><th>تاریخ</th><th>مبلغ (<?php echo esc_html($currency); ?>)</th><th>روش پرداخت</th><th>کد رهگیری</th></tr></thead>
        <tbody>
            <?php foreach ($payments as $p):
                $pd = function_exists('jdate') ? jdate('Y/m/d H:i', strtotime($p->paid_at)) : date('Y/m/d H:i', strtotime($p->paid_at));
            ?>
            <tr>
                <td><?php echo esc_html($pd); ?></td>
                <td><?php echo number_format($p->amount); ?></td>
                <td><?php echo esc_html($method_map[$p->method] ?? $p->method); ?></td>
                <td><?php echo esc_html($p->reference_code ?: '—'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- ═══════════════════════ توضیحات ═══════════════════════ -->
<?php if ($footer_description || !empty($invoice->notes) || !empty($invoice->terms)): ?>
<div class="invoice-notes-wrap">
    <?php if ($footer_description): ?>
    <div class="invoice-note-box">
        <h4>توضیحات:</h4>
        <p><?php echo nl2br(esc_html($footer_description)); ?></p>
    </div>
    <?php endif; ?>
    <?php if (!empty($invoice->terms)): ?>
    <div class="invoice-note-box">
        <h4>شرایط و ضوابط:</h4>
        <p><?php echo nl2br(esc_html($invoice->terms)); ?></p>
    </div>
    <?php endif; ?>
    <?php if (!empty($invoice->notes)): ?>
    <div class="invoice-note-box note-internal">
        <h4>یادداشت:</h4>
        <p><?php echo nl2br(esc_html($invoice->notes)); ?></p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════════ امضا و مهر ═══════════════════════ -->
<div class="signature-section">
    <div class="signature-box">
        <div class="sig-label">مهر و امضای فروشنده</div>
        <div class="sig-area"></div>
        <div class="sig-name"><?php echo esc_html($company_name); ?></div>
    </div>
    <div class="signature-box">
        <div class="sig-label">مهر و امضای خریدار</div>
        <div class="sig-area"></div>
        <div class="sig-name"><?php echo esc_html($invoice->customer_name); ?></div>
    </div>
</div>

<!-- ═══════════════════════ پرداخت آنلاین ═══════════════════════ -->
<?php if ($invoice->remaining > 0 && $invoice->status !== 'cancelled'): ?>
<div class="invoice-pay-section">
    <div class="pay-amount-display">
        مبلغ قابل پرداخت:
        <strong><?php echo number_format($invoice->remaining); ?> <?php echo esc_html($currency); ?></strong>
    </div>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action"     value="emdad_pay_invoice">
        <input type="hidden" name="invoice_id" value="<?php echo esc_attr($invoice->id); ?>">
        <button type="submit" class="pay-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            پرداخت آنلاین از طریق زرین‌پال
        </button>
    </form>
</div>
<?php else: ?>
<div class="paid-stamp">
    <span>✅ این فاکتور تماماً پرداخت و تسویه شده است</span>
</div>
<?php endif; ?>

<div class="invoice-footer-bar">
    <span><?php echo esc_html($company_name); ?></span>
    <?php if ($company_phone):   ?><span>📞 <?php echo esc_html($company_phone); ?></span><?php endif; ?>
    <?php if ($company_website): ?><span>🌐 <?php echo esc_html($company_website); ?></span><?php endif; ?>
    <?php if ($company_email):   ?><span>✉️ <?php echo esc_html($company_email); ?></span><?php endif; ?>
</div>

</div><!-- /.emdad-invoice-container -->
</div><!-- /.emdad-invoice-wrap -->
</body>
</html>
