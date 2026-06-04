<?php defined('ABSPATH') || exit;
$user_id = get_current_user_id();

// آمار سفارش‌ها
$orders = wc_get_orders(['customer' => $user_id, 'limit' => -1]);
$total_orders = count($orders);

$pending_count = wc_get_orders([
  'customer' => $user_id, 'limit' => -1,
  'status'   => ['processing', 'on-hold'],
  'return'   => 'ids',
]);
$pending_count = count($pending_count);

$total_spent = 0;
foreach ($orders as $order) {
  if ($order->is_paid()) $total_spent += $order->get_total();
}

// آخرین ۵ سفارش
$recent_orders = wc_get_orders([
  'customer' => $user_id, 'limit' => 5,
  'orderby'  => 'date', 'order' => 'DESC',
]);
?>

<div class="account-section-title">
  <i class="ti ti-layout-dashboard" aria-hidden="true"></i> داشبورد
</div>

<!-- کارت‌های آماری -->
<div class="account-stats-grid">
  <div class="account-stat-card">
    <span class="stat-label">کل سفارش‌ها</span>
    <strong class="stat-value"><?php echo number_format($total_orders); ?></strong>
    <span class="stat-unit">سفارش</span>
  </div>
  <div class="account-stat-card">
    <span class="stat-label">در انتظار ارسال</span>
    <strong class="stat-value"><?php echo number_format($pending_count); ?></strong>
    <span class="stat-unit">عدد</span>
  </div>
  <div class="account-stat-card">
    <span class="stat-label">جمع خریدها</span>
    <strong class="stat-value"><?php echo wc_price($total_spent); ?></strong>
  </div>
</div>

<!-- سفارش‌های اخیر -->
<div class="account-card">
  <div class="account-card-head">
    <div class="account-card-title">
      <i class="ti ti-clock-hour-4" aria-hidden="true"></i> آخرین سفارش‌ها
    </div>
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"
       class="account-see-all">مشاهده همه ←</a>
  </div>

  <?php if ($recent_orders): foreach ($recent_orders as $order):
    $status_map = [
      'completed'  => ['label' => 'تحویل شد', 'class' => 'badge-green'],
      'processing' => ['label' => 'در حال پردازش', 'class' => 'badge-amber'],
      'on-hold'    => ['label' => 'در انتظار', 'class' => 'badge-amber'],
      'cancelled'  => ['label' => 'لغو شد', 'class' => 'badge-red'],
      'refunded'   => ['label' => 'مسترد شد', 'class' => 'badge-red'],
      'pending'    => ['label' => 'در انتظار پرداخت', 'class' => 'badge-amber'],
    ];
    $status_key  = $order->get_status();
    $badge_info  = $status_map[$status_key] ?? ['label' => $status_key, 'class' => 'badge-amber'];
    $first_item  = current($order->get_items());
    $product_img = $first_item
      ? get_the_post_thumbnail_url($first_item->get_product_id(), 'thumbnail')
      : false;
  ?>
    <div class="account-order-row">
      <div class="order-thumb">
        <?php if ($product_img): ?>
          <img src="<?php echo esc_url($product_img); ?>" alt="" />
        <?php else: ?>
          <i class="ti ti-camera" aria-hidden="true"></i>
        <?php endif; ?>
      </div>
      <div class="order-info">
        <div class="order-name">
          <?php echo $first_item
            ? esc_html($first_item->get_name())
            : 'سفارش #' . $order->get_id(); ?>
        </div>
        <div class="order-meta">
          <?php echo wc_format_datetime($order->get_date_created(), get_option('date_format')); ?>
          &nbsp;·&nbsp; #<?php echo $order->get_id(); ?>
        </div>
      </div>
      <div class="order-right">
        <div class="order-price"><?php echo wc_price($order->get_total()); ?></div>
        <span class="badge <?php echo $badge_info['class']; ?>">
          <?php echo $badge_info['label']; ?>
        </span>
      </div>
    </div>
  <?php endforeach; else: ?>
    <div class="account-empty-state">
      <i class="ti ti-shopping-bag-x" aria-hidden="true"></i>
      <p>هنوز سفارشی ثبت نشده است</p>
    </div>
  <?php endif; ?>
</div>

<!-- آدرس‌ها -->
<div class="account-card">
  <div class="account-card-head">
    <div class="account-card-title">
      <i class="ti ti-map-pin" aria-hidden="true"></i> آدرس‌های من
    </div>
  </div>
  <div class="account-addr-grid">
    <?php foreach (['billing' => 'آدرس صورت‌حساب', 'shipping' => 'آدرس ارسال'] as $type => $title):
      $addr = WC()->countries->get_formatted_address(
        array_filter(array_map('trim', [
          'address_1' => get_user_meta($user_id, $type . '_address_1', true),
          'address_2' => get_user_meta($user_id, $type . '_address_2', true),
          'city'      => get_user_meta($user_id, $type . '_city', true),
          'state'     => get_user_meta($user_id, $type . '_state', true),
          'postcode'  => get_user_meta($user_id, $type . '_postcode', true),
          'country'   => get_user_meta($user_id, $type . '_country', true),
        ]))
      );
    ?>
    <div class="account-addr-cell">
      <div class="addr-label"><?php echo $title; ?></div>
      <div class="addr-text">
        <?php echo $addr ?: '<em>ثبت نشده</em>'; ?>
      </div>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address/' . $type)); ?>"
         class="addr-edit-link">
        <i class="ti ti-edit" aria-hidden="true"></i> ویرایش
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>