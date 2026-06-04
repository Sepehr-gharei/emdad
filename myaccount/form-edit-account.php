<?php defined('ABSPATH') || exit; ?>

<div class="account-section-title">
  <i class="ti ti-user-edit" aria-hidden="true"></i> ویرایش پروفایل
</div>

<?php wc_print_notices(); ?>

<div class="account-card account-form-card">
  <form class="woocommerce-EditAccountForm edit-account account-form"
        action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

    <?php do_action('woocommerce_edit_account_form_start'); ?>

    <div class="account-form-row">
      <div class="account-form-group">
        <label for="account_first_name">نام</label>
        <input type="text" id="account_first_name" name="account_first_name"
               value="<?php echo esc_attr($user->first_name); ?>" />
      </div>
      <div class="account-form-group">
        <label for="account_last_name">نام خانوادگی</label>
        <input type="text" id="account_last_name" name="account_last_name"
               value="<?php echo esc_attr($user->last_name); ?>" />
      </div>
    </div>

    <div class="account-form-group">
      <label for="account_display_name">نام نمایشی</label>
      <input type="text" id="account_display_name" name="account_display_name"
             value="<?php echo esc_attr($user->display_name); ?>" />
    </div>

    <div class="account-form-group">
      <label for="account_email">ایمیل</label>
      <input type="email" id="account_email" name="account_email"
             value="<?php echo esc_attr($user->user_email); ?>" />
    </div>

    <hr class="sp-divider" style="margin: 24px 0;">

    <p class="account-form-hint">
      برای تغییر رمز، فیلدهای زیر را پر کنید (در غیر این صورت خالی بگذارید)
    </p>

    <div class="account-form-group">
      <label for="password_current">رمز فعلی</label>
      <input type="password" id="password_current" name="password_current" autocomplete="off" />
    </div>
    <div class="account-form-row">
      <div class="account-form-group">
        <label for="password_1">رمز جدید</label>
        <input type="password" id="password_1" name="password_1" autocomplete="off" />
      </div>
      <div class="account-form-group">
        <label for="password_2">تکرار رمز جدید</label>
        <input type="password" id="password_2" name="password_2" autocomplete="off" />
      </div>
    </div>

    <?php do_action('woocommerce_edit_account_form'); ?>

    <div class="account-form-submit">
      <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
      <input type="hidden" name="action" value="save_account_details" />
      <button type="submit" class="btn btn-reverse" name="save_account_details">
        <span class="btn__text" data-text="ذخیره تغییرات">ذخیره تغییرات</span>
      </button>
    </div>

    <?php do_action('woocommerce_edit_account_form_end'); ?>
  </form>
</div>