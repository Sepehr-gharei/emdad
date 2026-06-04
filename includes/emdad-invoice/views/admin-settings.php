<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>⚙️ تنظیمات سیستم فاکتور</h1>
    <?php if (isset($_GET['saved'])): ?>
    <div class="notice notice-success is-dismissible"><p>✅ تنظیمات ذخیره شد.</p></div>
    <?php endif; ?>
    <form method="post" action="">
        <?php wp_nonce_field('emdad_save_settings', 'emdad_nonce'); ?>
        <input type="hidden" name="emdad_invoice_action" value="save_settings">

        <div class="emdad-form-card">
            <h3>🏢 اطلاعات شرکت / فروشنده</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>نام شرکت / برند</label>
                    <input type="text" name="company_name" value="<?php echo esc_attr($settings['company_name'] ?? 'گروه مهندسی امداد'); ?>">
                </div>
                <div class="emdad-field">
                    <label>شماره تماس ثابت</label>
                    <input type="text" name="company_phone" value="<?php echo esc_attr($settings['company_phone'] ?? ''); ?>">
                </div>
                <div class="emdad-field">
                    <label>موبایل</label>
                    <input type="text" name="company_mobile" value="<?php echo esc_attr($settings['company_mobile'] ?? ''); ?>">
                </div>
            </div>
            <div class="emdad-row">
                <div class="emdad-field" style="flex:2">
                    <label>آدرس شرکت</label>
                    <textarea name="company_address" rows="2"><?php echo esc_textarea($settings['company_address'] ?? ''); ?></textarea>
                </div>
                <div class="emdad-field">
                    <label>کد پستی</label>
                    <input type="text" name="company_postal_code" value="<?php echo esc_attr($settings['company_postal_code'] ?? ''); ?>">
                </div>
            </div>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>وب‌سایت</label>
                    <input type="text" name="company_website" value="<?php echo esc_attr($settings['company_website'] ?? ''); ?>" placeholder="www.example.com">
                </div>
                <div class="emdad-field">
                    <label>ایمیل شرکت</label>
                    <input type="email" name="company_email" value="<?php echo esc_attr($settings['company_email'] ?? ''); ?>">
                </div>
                <div class="emdad-field">
                    <label>لینک لوگو</label>
                    <input type="text" name="logo_url" value="<?php echo esc_attr($settings['logo_url'] ?? ''); ?>" placeholder="URL کامل لوگو">
                </div>
            </div>
        </div>

        <div class="emdad-form-card">
            <h3>🏦 اطلاعات بانکی</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>شماره کارت</label>
                    <input type="text" name="bank_card_number" value="<?php echo esc_attr($settings['bank_card_number'] ?? ''); ?>" placeholder="0000-0000-0000-0000">
                </div>
                <div class="emdad-field" style="flex:2">
                    <label>شماره شبا</label>
                    <input type="text" name="bank_sheba" value="<?php echo esc_attr($settings['bank_sheba'] ?? ''); ?>" placeholder="IR000000000000000000000000">
                </div>
            </div>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>نام بانک</label>
                    <input type="text" name="bank_name" value="<?php echo esc_attr($settings['bank_name'] ?? ''); ?>" placeholder="مثال: بانک ملت">
                </div>
                <div class="emdad-field">
                    <label>نام صاحب حساب</label>
                    <input type="text" name="bank_account_owner" value="<?php echo esc_attr($settings['bank_account_owner'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="emdad-form-card">
            <h3>📋 اطلاعات رسمی / مالیاتی</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>کد اقتصادی</label>
                    <input type="text" name="seller_economic_code" value="<?php echo esc_attr($settings['seller_economic_code'] ?? ''); ?>">
                </div>
                <div class="emdad-field">
                    <label>شناسه ملی شرکت</label>
                    <input type="text" name="company_national_id" value="<?php echo esc_attr($settings['company_national_id'] ?? ''); ?>">
                </div>
                <div class="emdad-field">
                    <label>نام کارشناس / فروشنده پیش‌فرض</label>
                    <input type="text" name="seller_name" value="<?php echo esc_attr($settings['seller_name'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="emdad-form-card">
            <h3>💰 پیش‌فرض‌های مالی</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>درصد مالیات پیش‌فرض (%)</label>
                    <input type="number" name="tax_percent" value="<?php echo esc_attr($settings['tax_percent'] ?? 9); ?>" min="0" max="100" step="0.1">
                </div>
                <div class="emdad-field" style="flex:2">
                    <label>واحد پول</label>
                    <select name="currency_label">
                        <option value="تومان" <?php selected($settings['currency_label'] ?? 'تومان', 'تومان'); ?>>تومان</option>
                        <option value="ریال" <?php selected($settings['currency_label'] ?? '', 'ریال'); ?>>ریال</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="emdad-form-card">
            <h3>📝 متون پیش‌فرض</h3>
            <div class="emdad-row">
                <div class="emdad-field">
                    <label>توضیحات پانوشت فاکتور (پیش‌فرض)</label>
                    <textarea name="default_notes" rows="3"><?php echo esc_textarea($settings['default_notes'] ?? ''); ?></textarea>
                </div>
                <div class="emdad-field">
                    <label>شرایط و ضوابط پیش‌فرض</label>
                    <textarea name="default_terms" rows="3"><?php echo esc_textarea($settings['default_terms'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="emdad-form-card">
            <h3>💳 درگاه پرداخت زرین‌پال</h3>
            <div class="emdad-row">
                <div class="emdad-field" style="flex:2">
                    <label>کد مرچنت (Merchant ID)</label>
                    <input type="text" name="zarrinpal_merchant" value="<?php echo esc_attr($settings['zarrinpal_merchant'] ?? ''); ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                </div>
                <div class="emdad-field">
                    <label>حالت اتصال</label>
                    <select name="zarrinpal_sandbox">
                        <option value="0" <?php selected($settings['zarrinpal_sandbox'] ?? '0', '0'); ?>>واقعی (Production)</option>
                        <option value="1" <?php selected($settings['zarrinpal_sandbox'] ?? '', '1'); ?>>تست (Sandbox)</option>
                    </select>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="btn-submit button-primary" value="💾 ذخیره تنظیمات">
        </p>
    </form>
</div>
