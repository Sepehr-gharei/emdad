<?php if (!defined('ABSPATH')) exit;
$settings = get_option('emdad_invoice_settings', []);
$is_edit = $edit_invoice !== null;
$def_tax = $settings['tax_percent'] ?? 9;
$def_seller = $settings['seller_name'] ?? '';
$def_terms  = $settings['default_terms'] ?? '';
$def_notes  = $settings['default_notes'] ?? '';
?>
<div class="wrap">
    <h1><?php echo $is_edit ? '✏️ ویرایش فاکتور' : '➕ فاکتور جدید'; ?></h1>
    <a href="<?php echo admin_url('admin.php?page=emdad-invoices'); ?>" class="button" style="margin:10px 0 20px;display:inline-block;">← برگشت به لیست</a>

    <form method="post" action="" id="emdad-invoice-form">
        <?php wp_nonce_field('emdad_save_invoice', 'emdad_nonce'); ?>
        <input type="hidden" name="emdad_invoice_action" value="save_invoice">
        <input type="hidden" name="invoice_id" value="<?php echo $is_edit ? $edit_invoice->id : 0; ?>">

        <!-- اطلاعات اصلی -->
        <div class="emdad-form-card">
            <h3>📄 اطلاعات فاکتور</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>نوع فاکتور</label>
                    <select name="invoice_type" id="invoice-type">
                        <option value="official"  <?php selected($is_edit ? $edit_invoice->type : 'official', 'official'); ?>>رسمی (با مالیات و عوارض)</option>
                        <option value="unofficial"<?php selected($is_edit ? $edit_invoice->type : '', 'unofficial'); ?>>غیررسمی (بدون مالیات)</option>
                        <option value="repair"    <?php selected($is_edit ? $edit_invoice->type : '', 'repair'); ?>>رسید تعمیرات</option>
                    </select>
                </div>
                <div class="emdad-field">
                    <label>تاریخ صدور</label>
                    <input type="text" name="issue_date" data-jdp value="<?php echo $is_edit ? esc_attr($edit_invoice->issue_date) : ''; ?>" placeholder="تاریخ شمسی">
                </div>
                <div class="emdad-field">
                    <label>تاریخ سررسید</label>
                    <input type="text" name="due_date" data-jdp value="<?php echo $is_edit ? esc_attr($edit_invoice->due_date) : ''; ?>" placeholder="تاریخ شمسی (اختیاری)">
                </div>
            </div>
        </div>

        <!-- اطلاعات مشتری -->
        <div class="emdad-form-card">
            <h3>🧑 اطلاعات خریدار</h3>
            <div class="emdad-row">
                <div class="emdad-field" style="flex:2">
                    <label>نام و نام خانوادگی *</label>
                    <input type="text" name="customer_name" required value="<?php echo $is_edit ? esc_attr($edit_invoice->customer_name) : ''; ?>" placeholder="نام کامل خریدار">
                </div>
                <div class="emdad-field">
                    <label>شماره تماس</label>
                    <input type="text" name="customer_phone" value="<?php echo $is_edit ? esc_attr($edit_invoice->customer_phone) : ''; ?>">
                </div>
                <div class="emdad-field">
                    <label>کد ملی</label>
                    <input type="text" name="customer_national_code" value="<?php echo $is_edit ? esc_attr($edit_invoice->customer_national_code) : ''; ?>">
                </div>
            </div>
            <div class="emdad-row">
                <div class="emdad-field" style="flex:2">
                    <label>نام شرکت</label>
                    <input type="text" name="customer_company" value="<?php echo $is_edit ? esc_attr($edit_invoice->customer_company) : ''; ?>">
                </div>
                <div class="emdad-field">
                    <label>شهر</label>
                    <input type="text" name="customer_city" value="<?php echo $is_edit ? esc_attr($edit_invoice->customer_city) : ''; ?>">
                </div>
                <div class="emdad-field">
                    <label>ایمیل</label>
                    <input type="email" name="customer_email" value="<?php echo $is_edit ? esc_attr($edit_invoice->customer_email) : ''; ?>">
                </div>
            </div>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>آدرس</label>
                    <textarea name="customer_address" rows="2"><?php echo $is_edit ? esc_textarea($edit_invoice->customer_address) : ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- اطلاعات فروشنده -->
        <div class="emdad-form-card">
            <h3>🏢 اطلاعات فروشنده</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>نام کارشناس</label>
                    <input type="text" name="seller_name" value="<?php echo $is_edit ? esc_attr($edit_invoice->seller_name) : esc_attr($def_seller); ?>">
                </div>
                <div class="emdad-field">
                    <label>نام شرکت فروشنده</label>
                    <input type="text" name="seller_company" value="<?php echo $is_edit ? esc_attr($edit_invoice->seller_company) : ''; ?>">
                </div>
                <div class="emdad-field">
                    <label>کد اقتصادی</label>
                    <input type="text" name="seller_economic_code" value="<?php echo $is_edit ? esc_attr($edit_invoice->seller_economic_code) : esc_attr($settings['seller_economic_code'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <!-- اقلام -->
        <div class="emdad-form-card">
            <h3>📦 اقلام فاکتور</h3>
            <div class="items-header" style="grid-template-columns:90px 2fr 1fr 80px 1fr 90px 90px;">
                <span>کد کالا</span>
                <span>نام کالا / خدمت *</span>
                <span>تعداد</span>
                <span>واحد</span>
                <span>قیمت واحد (ریال)</span>
                <span>تخفیف (%)</span>
                <span>مالیات (%)</span>
            </div>
            <div id="emdad-items-container">
                <?php
                $items_to_render = $is_edit && !empty($edit_items) ? $edit_items : [null];
                $item_idx = 0;
                foreach ($items_to_render as $item):
                ?>
                <div class="item-row">
                    <span class="remove-item" onclick="this.closest('.item-row').remove();calcTotal()">✕</span>
                    <div class="item-inputs" style="grid-template-columns:90px 2fr 1fr 80px 1fr 90px 90px;">
                        <input type="text"   name="items[<?php echo $item_idx; ?>][product_code]"    placeholder="کد کالا"    value="<?php echo $item ? esc_attr($item->product_code ?? '') : ''; ?>">
                        <input type="text"   name="items[<?php echo $item_idx; ?>][name]"            placeholder="نام کالا یا خدمت" required value="<?php echo $item ? esc_attr($item->name) : ''; ?>">
                        <input type="number" name="items[<?php echo $item_idx; ?>][quantity]"        placeholder="تعداد" value="<?php echo $item ? floatval($item->quantity) : 1; ?>" step="0.01" min="0" class="qty-input" oninput="calcTotal()">
                        <select name="items[<?php echo $item_idx; ?>][unit]">
                            <?php foreach(['عدد','متر','کیلوگرم','ست','دستگاه','جفت','لیتر','ساعت','روز','ماه'] as $u): ?>
                            <option value="<?php echo esc_attr($u); ?>" <?php selected($item ? $item->unit : 'عدد', $u); ?>><?php echo esc_html($u); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="items[<?php echo $item_idx; ?>][unit_price]"      placeholder="قیمت واحد" value="<?php echo $item ? intval($item->unit_price) : ''; ?>" min="0" class="price-input" oninput="calcTotal()">
                        <input type="number" name="items[<?php echo $item_idx; ?>][discount_percent]" placeholder="%" value="<?php echo $item ? floatval($item->discount_percent) : 0; ?>" min="0" max="100" step="0.1" class="disc-input" oninput="calcTotal()">
                        <input type="number" name="items[<?php echo $item_idx; ?>][tax_percent]"     placeholder="%" value="<?php echo $item ? floatval($item->tax_percent ?? 0) : 0; ?>" min="0" max="100" step="0.1" class="item-tax-input" oninput="calcTotal()">
                    </div>
                    <div style="margin-top:6px">
                        <input type="text" name="items[<?php echo $item_idx; ?>][description]" placeholder="شرح کالا / خدمت (اختیاری)" style="width:100%;border:1px dashed #dde;border-radius:6px;padding:5px 8px;font-size:12px;" value="<?php echo $item ? esc_attr($item->description) : ''; ?>">
                    </div>
                </div>
                <?php $item_idx++; endforeach; ?>
            </div>
            <button type="button" class="btn-add-item" onclick="addItem()">+ افزودن ردیف</button>
            <div class="invoice-total-preview" id="total-preview">مبلغ کل: محاسبه نشده</div>
        </div>

        <!-- مالیات و تخفیف کلی -->
        <div class="emdad-form-card">
            <h3>💰 محاسبات مالی کلی</h3>
            <p style="font-size:13px;color:#888;margin-bottom:12px">مالیات می‌تواند هم به صورت کلی (اینجا) و هم به صورت ردیف‌ای (در اقلام) تعریف شود.</p>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>درصد تخفیف کلی (%)</label>
                    <input type="number" name="discount_percent" value="<?php echo $is_edit ? $edit_invoice->discount_percent : 0; ?>" min="0" max="100" step="0.1" oninput="calcTotal()">
                </div>
                <div class="emdad-field" id="tax-field">
                    <label>درصد مالیات و عوارض کلی (%) — فاکتور رسمی</label>
                    <input type="number" name="tax_percent" value="<?php echo $is_edit ? $edit_invoice->tax_percent : $def_tax; ?>" min="0" max="100" step="0.1" oninput="calcTotal()">
                </div>
            </div>
        </div>

        <!-- یادداشت‌ها -->
        <div class="emdad-form-card">
            <h3>📝 توضیحات و شرایط</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>توضیحات پانوشت (نمایش در فاکتور)</label>
                    <textarea name="footer_description" rows="3" placeholder="متنی که پایین فاکتور نمایش داده می‌شود..."><?php echo $is_edit ? esc_textarea($edit_invoice->footer_description) : esc_textarea($def_notes); ?></textarea>
                </div>
                <div class="emdad-field">
                    <label>شرایط و ضوابط</label>
                    <textarea name="terms" rows="3"><?php echo $is_edit ? esc_textarea($edit_invoice->terms) : esc_textarea($def_terms); ?></textarea>
                </div>
                <div class="emdad-field">
                    <label>یادداشت داخلی (ادمین)</label>
                    <textarea name="notes" rows="3"><?php echo $is_edit ? esc_textarea($edit_invoice->notes) : ''; ?></textarea>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="btn-submit button-primary" value="<?php echo $is_edit ? '💾 ذخیره تغییرات' : '🚀 صدور فاکتور'; ?>">
        </p>
    </form>
</div>

<script>
var itemIndex = <?php echo $item_idx; ?>;

<?php
$units = ['عدد','متر','کیلوگرم','ست','دستگاه','جفت','لیتر','ساعت','روز','ماه'];
$optHtml = '';
foreach ($units as $u) $optHtml .= '<option value="' . $u . '">' . $u . '</option>';
$optHtmlJson = json_encode($optHtml);
?>

function addItem() {
    var container = document.getElementById('emdad-items-container');
    var html = '<div class="item-row">' +
        '<span class="remove-item" onclick="this.closest(\'.item-row\').remove();calcTotal()">✕</span>' +
        '<div class="item-inputs" style="grid-template-columns:90px 2fr 1fr 80px 1fr 90px 90px;">' +
            '<input type="text"   name="items[' + itemIndex + '][product_code]"    placeholder="کد کالا">' +
            '<input type="text"   name="items[' + itemIndex + '][name]"            placeholder="نام کالا یا خدمت" required>' +
            '<input type="number" name="items[' + itemIndex + '][quantity]"        value="1" step="0.01" min="0" class="qty-input" oninput="calcTotal()">' +
            '<select name="items[' + itemIndex + '][unit]">' + <?php echo $optHtmlJson; ?> + '</select>' +
            '<input type="number" name="items[' + itemIndex + '][unit_price]"      placeholder="قیمت واحد" min="0" class="price-input" oninput="calcTotal()">' +
            '<input type="number" name="items[' + itemIndex + '][discount_percent]" placeholder="%" value="0" min="0" max="100" step="0.1" class="disc-input" oninput="calcTotal()">' +
            '<input type="number" name="items[' + itemIndex + '][tax_percent]"     placeholder="%" value="0" min="0" max="100" step="0.1" class="item-tax-input" oninput="calcTotal()">' +
        '</div>' +
        '<div style="margin-top:6px"><input type="text" name="items[' + itemIndex + '][description]" placeholder="شرح کالا / خدمت (اختیاری)" style="width:100%;border:1px dashed #dde;border-radius:6px;padding:5px 8px;font-size:12px;"></div>' +
    '</div>';
    container.insertAdjacentHTML('beforeend', html);
    itemIndex++;
}

function calcTotal() {
    var rows = document.querySelectorAll('#emdad-items-container .item-row');
    var subtotal   = 0;
    var totalTax   = 0;

    rows.forEach(function(row) {
        var qty      = parseFloat(row.querySelector('.qty-input')?.value      || 0);
        var price    = parseFloat(row.querySelector('.price-input')?.value    || 0);
        var disc     = parseFloat(row.querySelector('.disc-input')?.value     || 0);
        var itemTax  = parseFloat(row.querySelector('.item-tax-input')?.value || 0);
        var lineBase = qty * price;
        var lineDisc = lineBase * disc / 100;
        var lineAfterDisc = lineBase - lineDisc;
        var lineTax  = lineAfterDisc * itemTax / 100;
        subtotal  += lineAfterDisc;
        totalTax  += lineTax;
    });

    var discPct    = parseFloat(document.querySelector('[name="discount_percent"]')?.value || 0);
    var afterGlobalDisc = subtotal * (1 - discPct / 100);

    var invType    = document.getElementById('invoice-type')?.value;
    var globalTax  = invType === 'official' ? parseFloat(document.querySelector('[name="tax_percent"]')?.value || 0) : 0;
    var globalTaxAmt = afterGlobalDisc * globalTax / 100;
    var total = afterGlobalDisc + totalTax + globalTaxAmt;

    var preview = document.getElementById('total-preview');
    if (preview) preview.textContent = 'مبلغ کل: ' + Math.round(total).toLocaleString('fa-IR') + ' ریال';
}

document.getElementById('invoice-type')?.addEventListener('change', function() {
    var taxField = document.getElementById('tax-field');
    if (taxField) taxField.style.opacity = this.value === 'official' ? '1' : '0.4';
    calcTotal();
});

document.getElementById('emdad-invoice-form')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.type === 'number') e.preventDefault();
});

calcTotal();
</script>
