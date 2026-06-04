<?php defined('ABSPATH') || exit; get_header(); ?>

<section class="custom-woo-layout account-page-layout">
  <div class="container">
    <div class="account-wrapper">

      <?php /* ===== SIDEBAR ===== */ ?>
      <aside class="account-sidebar">

        <div class="account-avatar-card">
          <?php
          $current_user = wp_get_current_user();
          $initials     = mb_strtoupper(
            mb_substr($current_user->first_name ?: $current_user->display_name, 0, 1) .
            mb_substr($current_user->last_name, 0, 1)
          );
          ?>
          <div class="avatar-circle"><?php echo esc_html($initials); ?></div>
          <p class="avatar-name"><?php echo esc_html($current_user->display_name); ?></p>
          <p class="avatar-email"><?php echo esc_html($current_user->user_email); ?></p>
        </div>

        <nav class="account-nav">
          <?php
          $endpoints = [
            'dashboard'       => ['icon' => 'ti-layout-dashboard', 'label' => 'داشبورد'],
            'orders'          => ['icon' => 'ti-shopping-bag',      'label' => 'سفارش‌های من'],
            'edit-address'    => ['icon' => 'ti-map-pin',           'label' => 'آدرس‌ها'],
            'edit-account'    => ['icon' => 'ti-user-edit',         'label' => 'ویرایش پروفایل'],
          ];
          $current = WC()->query->get_current_endpoint();
          if (empty($current)) $current = 'dashboard';

          foreach ($endpoints as $key => $ep):
            $url = ($key === 'dashboard')
              ? wc_get_account_endpoint_url('dashboard')
              : wc_get_account_endpoint_url($key);
            $active = ($current === $key) ? 'active' : '';
          ?>
            <a href="<?php echo esc_url($url); ?>"
               class="account-nav-item <?php echo $active; ?>">
              <i class="ti <?php echo $ep['icon']; ?>" aria-hidden="true"></i>
              <?php echo $ep['label']; ?>
            </a>
          <?php endforeach; ?>

          <a href="<?php echo esc_url(wc_logout_url()); ?>"
             class="account-nav-item danger">
            <i class="ti ti-logout" aria-hidden="true"></i>
            خروج از حساب
          </a>
        </nav>
      </aside>

      <?php /* ===== MAIN CONTENT ===== */ ?>
      <div class="account-main">
        <?php woocommerce_account_content(); ?>
      </div>

    </div>
  </div>
</section>

<?php get_footer(); ?>