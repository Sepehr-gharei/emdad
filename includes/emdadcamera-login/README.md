# EmdadCamera Login — راهنمای نصب

## ساختار پوشه

پوشه `emdadcamera-login` را داخل پوشه قالب خود قرار دهید:

```
wp-content/themes/your-theme/
    └── emdadcamera-login/
        ├── emdadcamera-login.php       ← فایل اصلی
        ├── includes/
        │   ├── class-emdadcamera-login-db.php
        │   ├── class-emdadcamera-login-ajax.php
        │   ├── class-emdadcamera-login-form.php
        │   ├── class-emdadcamera-notify-admin.php
        │   └── class-emdadcamera-notify-woo-alerts.php
        └── assets/
            ├── css/emdadcamera-login.css
            ├── js/emdadcamera-login.js
            └── icons/eye.svg
```

---

## ۱. اضافه کردن به functions.php قالب

در فایل `functions.php` قالب خود این خط را اضافه کنید:

```php
require_once get_template_directory() . '/emdadcamera-login/emdadcamera-login.php';
```

---

## ۲. ساخت صفحه لاگین

### روش الف — استفاده از URL پارامتر (خودکار)

سیستم به طور خودکار کار می‌کند. وقتی کاربر وارد آدرس زیر شود، فرم لاگین نمایش داده می‌شود:

```
https://yoursite.com/?login
```

و اگر کاربر بخواهد به صفحه my-account برود ولی لاگین نباشد، به همین آدرس هدایت می‌شود.

### روش ب — استفاده در یک Template Page

یک فایل `page-login.php` در قالب بسازید و فرم را مستقیم داخل آن نمایش دهید:

```php
<?php
/*
 * Template Name: صفحه ورود
 */
get_header();
?>
<main>
    <div class="container">
        <?php echo emdadcamera_render_login_form(); ?>
    </div>
</main>
<?php get_footer(); ?>
```

سپس در وردپرس یک برگه جدید بسازید و قالب "صفحه ورود" را برای آن انتخاب کنید.

### روش ج — استفاده در هر جای قالب

```php
<?php echo emdadcamera_render_login_form(); ?>
```

---

## ۳. تنظیمات

بعد از نصب، منوی **EmdadCamera Login** در پنل مدیریت وردپرس ظاهر می‌شود.

### تب‌های تنظیمات:

- **عمومی** — نقش پیش‌فرض کاربر، ورود با رمز، تنظیمات OTP
- **ظاهر** — لوگو، رنگ دکمه، فونت، حالت روشن/تیره
- **پیامک** — کلید API سامانه پیامک SMS.IR و کد قالب پیامک
- **Woo Alerts** — اعلان‌های سفارش WooCommerce برای مشتری و ادمین
- **Logs** — مشاهده، دانلود، و پاک‌کردن لاگ‌ها

---

## ۴. ایجاد جدول دیتابیس

جدول دیتابیس به صورت خودکار در اولین بار اجرا ساخته می‌شود.
اگر می‌خواهید دستی ایجاد کنید:

```php
EmdadCamera_Login_DB::create_table();
```

---

## ۵. نکات مهم

- این کد از سرویس SMS.IR برای ارسال پیامک OTP استفاده می‌کند.
- کلید API و کد قالب پیامک را از پنل SMS.IR دریافت کنید.
- پوشه `emdadcamera-login` باید درون پوشه قالب فعال باشد، نه child theme.
  اگر از child theme استفاده می‌کنید، `get_template_directory()` را به `get_stylesheet_directory()` تغییر دهید.
