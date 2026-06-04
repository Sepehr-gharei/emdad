<?php defined('ABSPATH') || exit;
$customer_orders = wc_get_orders([
  'customer' => get_current_user_id(),
  'limit'    => 10,
  'paged'    => isset($_GET['paged']) ? absint($_GET['paged']) : 1,
]);
?>

<div class="account-section-title">
  <i class="ti ti-shopping-bag" aria-hidden="true"></i> سفارش‌های من
</div>

<div class="account-card">
  <?php if ($customer_orders): ?>
    <div class="orders-table-wrap">
      <table class="account-orders-table">
        <thead>
          <tr>
            <th>شماره</th>
            <th>تاریخ</th>
            <th>وضعیت</th>
            <th>مبلغ</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $status_map = [
          'completed'  => ['label' => 'تحویل شد',         'class' => 'badge-green'],
          'processing' => ['label' => 'در حال پردازش',    'class' => 'badge-amber'],
          'on-hold'    => ['label' => 'در انتظار',         'class' => 'badge-amber'],
          'cancelled'  => ['label' => 'لغو شد',            'class' => 'badge-red'],
          'refunded'   => ['label' => 'مسترد شد',          'class' => 'badge-red'],
          'pending'    => ['label' => 'در انتظار پرداخت', 'class' => 'badge-amber'],
        ];
        foreach ($customer_orders as $order):
          $st = $order->get_status();
          $bi = $status_map[$st] ?? ['label' => $st, 'class' => 'badge-amber'];
        ?>
          <tr>
            <td><strong>#<?php echo $order->get_id(); ?></strong></td>
            <td><?php echo wc_format_datetime($order->get_date_created(), get_option('date_format')); ?></td>
            <td><span class="badge <?php echo $bi['class']; ?>"><?php echo $bi['label']; ?></span></td>
            <td><?php echo wc_price($order->get_total()); ?></td>
            <td>
              <a href="<?php echo esc_url($order->get_view_order_url()); ?>"
                 class="btn btn-sm btn-primary">
                <span class="btn__text" data-text="مشاهده">مشاهده</span>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="account-empty-state">
      <i class="ti ti-shopping-bag-x" aria-hidden="true"></i>
      <p>هنوز سفارشی ثبت نشده است</p>
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
         class="btn btn-reverse">
        <span class="btn__text" data-text="رفتن به فروشگاه">رفتن به فروشگاه</span>
      </a>
    </div>
  <?php endif; ?>
</div>