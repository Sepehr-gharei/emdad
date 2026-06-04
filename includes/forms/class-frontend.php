<?php
defined( 'ABSPATH' ) || exit;

class Emdad_Forms_Frontend {
    
    private static $contact_info = 'contact_info';
    
    public static function init() {
        // برای فراخوانی با تابع گلوبال نیاز به کار خاصی نیست
    }
    
    private static function get_contact_setting($key) {
        return emdadcamera_Get_Setting(self::$contact_info, $key);
    }
    
    public static function render($form_id) {
        // دریافت مقادیر تنظیمات داخل کلاس
        $call_text1 = self::get_contact_setting('call_text1');
        $call_url1  = self::get_contact_setting('call_url1');
        
        ob_start();
        ?>
        <form class="emdad-ajax-form" data-form="<?php echo esc_attr($form_id); ?>">
            <?php wp_nonce_field('emdad_submit_nonce', 'security'); ?>
            
            <?php if ($form_id === 'contact'): // فرم تماس با ما ?>
                    <div class="form-group"><label>نام و نام خانوادگی</label><input type="text" name="full_name" required></div>
                    <div class="form-group"><label>شماره همراه</label><input type="tel" name="phone" required></div>
                    <div class="form-group"><label>موضوع پیام</label>
                        <select name="subject">
                            <option value="مشاوره، پشتیبانی، فروش، سایر">مشاوره، پشتیبانی، فروش، سایر</option>
                            <option value="مشاوره خرید">مشاوره خرید</option>
                            <option value="پشتیبانی فنی">پشتیبانی فنی</option>
                        </select>
                    </div>
                    <div class="form-group"><label>متن پیام</label><textarea name="message" required></textarea></div>
                    <button type="submit" class="btn btn-reverse"><span class="btn__text" data-text="ارسال پیام">ارسال پیام</span></button>

            <?php elseif ($form_id === 'order'): // فرم ثبت سفارش ?>
                <div class="order-form-wrapper">
                    <div class="form-aside">
                        <div class="product-thumb"><img src="https://emdadcamera.com/wp-content/uploads/2026/05/cameranobg-removebg.png" alt="دوربین"></div>
                        <div class="consult-box"><h3>نیاز به مشاوره دارید؟</h3><p>با ما تماس بگیرید.</p>
                            <a href="<?php echo esc_url($call_url1); ?>" class="phone-link"><i class="icon"><?php echo emdadcamera_Icon('telephone-icon'); ?></i><strong class="number"><?php echo esc_html($call_text1); ?></strong></a>
                        </div>
                    </div>
                    <div class="form-body">
                        <div class="form-row">
                            <div class="form-group"><label>نام و نام خانوادگی *</label><input type="text" name="full_name" required></div>
                            <div class="form-group"><label>شماره تماس *</label><input type="tel" name="phone" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>تعداد دوربین - دستگاه *</label><input type="number" name="device_count" min="1" required></div>
                            <div class="form-group"><label>انتقال تصویر لازم دارد؟ *</label>
                                <select name="needs_transfer" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="بله">بله</option>
                                    <option value="خیر">خیر</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label>بازدید نیاز دارد؟ *</label>
                            <select name="needs_visit" id="emdad_needs_visit" required>
                                <option value="خیر">خیر</option>
                                <option value="بله">بله</option>
                            </select>
                        </div>
                        <div class="form-group full-width" id="emdad_visit_hours_wrapper" style="display: none;">
                            <label>چه ساعت‌هایی امکان بازدید هست؟</label>
                            <input type="text" name="visit_hours" placeholder="مثلاً: عصرها از ساعت 17 الی 20">
                        </div>
                        <button type="submit" class="btn btn-reverse full-width"><span class="btn__text" data-text="ثبت و ارسال درخواست">ثبت و ارسال درخواست</span></button>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var visitSelect = document.getElementById('emdad_needs_visit');
                        var hoursWrapper = document.getElementById('emdad_visit_hours_wrapper');
                        if(visitSelect && hoursWrapper) {
                            visitSelect.addEventListener('change', function() {
                                hoursWrapper.style.display = (this.value === 'بله') ? 'block' : 'none';
                            });
                        }
                    });
                </script>

            <?php elseif ($form_id === 'service'): // فرم سرویس ?>
                <div class="form-group"><input type="text" name="full_name" placeholder="نام و نام خانوادگی" required></div>
                <div class="form-group"><input type="tel" name="phone" placeholder="شماره تماس" required></div>
                <button type="submit" class="btn btn-reverse"><span class="btn__text" data-text="ثبت درخواست نصب">ثبت درخواست نصب</span></button>
            <?php endif; ?>

            <div class="emdad-form-response" style="display:none; margin-top:15px; padding:10px;"></div>
        </form>
        <?php 
        return ob_get_clean();
    }
}