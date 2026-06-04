<?php
class Main_Settings extends Admin_Helper {
    public function All_Settings() {
        $theme_options = get_option('emdadcamera_main_option', [] );
        $contact_info = isset($theme_options['contact_info']) ? $theme_options['contact_info'] : [];
        $codes = isset($theme_options['codes']) ? $theme_options['codes'] : [];
        $global_info =isset($theme_options['global_info']) ? $theme_options['global_info'] : [];
        $home_settings =isset($theme_options['home_settings']) ? $theme_options['home_settings'] : [];
        $before_body_val = isset($codes['before_body']) ? stripslashes($codes['before_body']) : '';
        $before_head_val = isset($codes['before_head']) ? stripslashes($codes['before_head']) : '';

        $settings = [
            'contact_info' => [
                'menu' => 'اطلاعات تماس',
                'lable' => 'تنظیمات اطلاعات تماس',
                'settings' => [
                    'call_text1' => [ 'type' => 'text', 'title' => 'متن شماره ثابت', 'value' => $contact_info['call_text1']??'', 'width' => 'w50 ltr', ],
                    'call_url1' => [ 'type' => 'text', 'title' => 'آدرس شماره ثابت', 'value' => $contact_info['call_url1']??'', 'width' => 'w50 ltr', ],
                    'call_text2' => [ 'type' => 'text', 'title' => 'متن شماره ثابت 2', 'value' => $contact_info['call_text2']??'', 'width' => 'w50 ltr', ],
                    'call_url2' => [ 'type' => 'text', 'title' => 'آدرس شماره ثابت 2', 'value' => $contact_info['call_url2']??'', 'width' => 'w50 ltr', ],
                    'call_text3' => [ 'type' => 'text', 'title' => 'متن شماره موبایل', 'value' => $contact_info['call_text3']??'', 'width' => 'w50 ltr', ],
                    'call_url3' => [ 'type' => 'text', 'title' => 'آدرس شماره موبایل', 'value' => $contact_info['call_url3']??'', 'width' => 'w50 ltr', ],
                    'email_text' => [ 'type' => 'text', 'title' => 'ایمیل', 'value' => $contact_info['email_text']??'', 'width' => 'w50 ltr', ],
                    'email_url' => [ 'type' => 'text', 'title' => 'آدرس ایمیل', 'value' => $contact_info['email_url']??'', 'width' => 'w50 ltr', ],
                    'address' => [ 'type' => 'textarea', 'title' => 'آدرس', 'value' => $contact_info['address']??'', 'width' => 'w100' ],
                    'instagram' => [ 'type' => 'textarea', 'title' => 'لینک اینستاگرام', 'value' => $contact_info['instagram']??'', 'width' => 'w50' ],
                    'whatsapp' => [ 'type' => 'textarea', 'title' => 'لینک واتساپ', 'value' => $contact_info['whatsapp']??'', 'width' => 'w50' ],
                    'telegram' => [ 'type' => 'textarea', 'title' => 'لینک تلگرام', 'value' => $contact_info['telegram']??'', 'width' => 'w50' ],
                    'bale' => [ 'type' => 'textarea', 'title' => 'لینک بله', 'value' => $contact_info['bale']??'', 'width' => 'w50' ],
                ],
              
            ],
            'global_info' => [
                'menu' => 'اطلاعات عمومی',
                'lable' => 'تنظیمات اطلاعات عمومی',
                'settings' => [
                    'footer_text' => [ 'type' => 'text', 'title' => 'متن درباره امداد دوربین فوتر', 'value' => $global_info['footer_text']??'', 'width' => 'w100 rtl', ],
                    'footer_copyright' => [ 'type' => 'text', 'title' => 'متن کپی رایت امداد دوربین فوتر', 'value' =>  $global_info['footer_copyright']??'', 'width' => 'w100 rtl', ],
                    'banner_before_footer_img' => ['type' => 'file', 'format' => 'img', 'title'  => 'عکس بنر قبل از فوتر', 'value' =>  $global_info['banner_before_footer_img']??'', 'width' => 'w100 rtl', ],
                    'banner_before_footer_title' => ['type' => 'text', 'title' => 'تایتل بنر قبل از فوتر', 'value' =>  $global_info['banner_before_footer_title']??'', 'width' => 'w100 rtl', ],
                    'banner_before_footer_text' => ['type' => 'text', 'title' => 'متن بنر قبل از فوتر ', 'value' =>  $global_info['banner_before_footer_text']??'', 'width' => 'w100 rtl', ],
                   
                ],
            ],
            'codes' => [
                'menu' => 'کد های سفارشی',
                'lable' => 'تنظیمات کد های سفارشی',
                'settings' => [
                    'before_body' => [ 'type' => 'textarea', 'title' => 'کد سفارشی قبل از تگ بسته Body', 'value' => $before_body_val, 'width' => 'w50 ltr', ],
                    'before_head' => [ 'type' => 'textarea', 'title' => 'کد سفارشی قبل از تگ بسته Head', 'value' => $before_head_val, 'width' => 'w50 ltr', ],
                ],
            ],
        ];
        return $settings;
    }

    protected function General_Settings($current_tab) {
        $all_settings = $this->All_Settings();
        foreach($all_settings as $name => $section) {
            $lable = $section['lable'];
            $settings = $section['settings'];
            if ($name == $current_tab) {
                echo "<div class='content-tab $name-options'>"
                    . "<h2>$lable</h2>"
                    . "<div class='emdadcamera-form-setting flex flex-wrap'>";
                    foreach($settings as $id => $setting) {
                        echo $this->emdadcamera_Type_To_Function($id,$setting);
                    }
                    echo "</div>"
                . "</div>";
            }
        }
    }
}
new Main_Settings;
?>