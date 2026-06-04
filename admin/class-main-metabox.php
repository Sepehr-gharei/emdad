<?php
class Main_Metaboxes extends Admin_Helper {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_custom_metaboxes'));
        add_action('save_post', array($this, 'save_metabox_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    public function get_posts_list_array() {
        $posts = get_posts(array('post_type' => 'post', 'numberposts' => -1));
        $options = array();
        if ($posts) {
            foreach ($posts as $post) {
                $options[$post->ID] = $post->post_title;
            }
        }
        return $options;
    }

    public function get_categories_list_array() {
        $terms = get_terms(array('taxonomy' => 'category', 'hide_empty' => false));
        $options = array();
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }
        }
        return $options;
    }

    public function All_Metaboxes() {
        $metaboxes = [
            'home' => [
                'title' => 'تنظیمات برگه اصلی (Home)',
                'post_types' => ['page'],
                'page_templates' => ['page-home.php'], // این متاباکس فقط در تمپلیت Home لود می‌شود
                'context' => 'normal',
                'priority' => 'high',
                'settings' => [
                    'heading_hero' => [ 'type' => 'heading', 'title' => 'بخش هیرو (محتوای صفحه اصلی)' ],
                    'hero_title' => [ 'type' => 'editor', 'title' => 'عنوان اصلی', 'width' => 'w100', 'default' => '' ],
                    'hero_desc' => [ 'type' => 'text', 'title' => 'متن کوتاه', 'width' => 'w100', 'default' => '' ],
                    'hero_subtitle' => [ 'type' => 'text', 'title' => 'زیر عنوان', 'width' => 'w100', 'default' => '' ],
                    'hero_image' => [ 'type' => 'file', 'format' => 'img', 'title' => 'تصویر هیرو', 'width' => 'w50' ],
                    'hero_buttons' => [
                        'type' => 'repeater', 
                        'title' => 'دکمه‌های هیرو', 
                        'btn' => 'افزودن دکمه', 
                        'width' => 'w50', 
                        // کلید section کلا حذف شد!
                        'settings' => [
                            'hero_buttons[0][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان دکمه'
                            ],
                            'hero_buttons[0][url]'   => [
                                'type' => 'url',
                                'title' => 'لینک دکمه'
                            ],
                        ]
                    ],
                    'about_title' => [ 'type' => 'editor', 'title' => 'عنوان سکشن درباره ما', 'width' => 'w100', 'default' => '' ],
                    'about_desc' => [ 'type' => 'text', 'title' => 'متن درباره ما', 'width' => 'w100', 'default' => '' ],
                    'about_image' => [ 'type' => 'file', 'format' => 'img', 'title' => 'نصویر ایکون', 'width' => 'w50' ],
                    'about_buttons' => [
                        'type' => 'repeater',
                        'title' => 'دکمه‌های درباره ما',
                        'btn' => 'افزودن دکمه',
                        'width' => 'w50',
                        'settings' => [
                            'about_buttons[0][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان دکمه'
                            ],
                            'about_buttons[0][url]'   => [
                                'type' => 'url',
                                'title' => 'لینک دکمه'
                            ],
                        ],
                    ],
                ],
            ],

            'camera_specs' => [
                'title' => 'مشخصات فنی دوربین مداربسته',
                'post_types' => ['product'], // فعال‌سازی فقط برای محصولات ووکامرس
                'page_templates' => [],
                'context' => 'normal',
                'priority' => 'high',
                'settings' => [
                    'specs_heading' => [ 'type' => 'heading', 'title' => 'ویژگی‌های اصلی دوربین' ],
                    'resolution' => [ 'type' => 'text', 'title' => 'کیفیت تصویر (رزولوشن)', 'width' => 'w50', 'default' => '' ],
                    'lens_type' => [ 'type' => 'text', 'title' => 'نوع لنز', 'width' => 'w50', 'default' => '' ],
                    'night_vision' => [ 'type' => 'text', 'title' => 'برد دید در شب', 'width' => 'w50', 'default' => '' ],
                    'connectivity' => [ 'type' => 'text', 'title' => 'نوع اتصال (سیمی/بیسیم)', 'width' => 'w50', 'default' => '' ],
                    'body_material' => [ 'type' => 'text', 'title' => 'جنس بدنه', 'width' => 'w50', 'default' => '' ],
                    'water_resistance' => [ 'type' => 'text', 'title' => 'استاندارد مقاومت (IP)', 'width' => 'w50', 'default' => '' ],
                    'storage' => [ 'type' => 'text', 'title' => 'پشتیبانی از کارت حافظه', 'width' => 'w50', 'default' => '' ],
                    'warranty' => [ 'type' => 'text', 'title' => 'گارانتی و خدمات', 'width' => 'w50', 'default' => '' ],
                    'product_features' => [
                        'type' => 'repeater',
                        'title' => 'ویژگی‌های برجسته محصول',
                        'btn' => 'افزودن ویژگی',
                        'width' => 'w100',
                        'settings' => [
                            'product_features[0][icon]' => [
                                'type' => 'text',
                                'title' => 'اسم آیکون'
                            ],
                            'product_features[0][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان ویژگی'
                            ],
                            'product_features[0][text]' => [
                                'type' => 'text',
                                'title' => 'متن ویژگی'
                            ],
                        ],
                    ],
                ],
                
            ],
            'applications' => [
                'title' => 'مشخصات فنی دوربین مداربسته',
                'post_types' => ['applications'], // فعال‌سازی فقط برای محصولات ووکامرس
                'page_templates' => [],
                'context' => 'normal',
                'priority' => 'high',
                'settings' => [
                    'applications_heading' => [ 'type' => 'heading', 'title' => 'ویژگی‌های اصلی اپلیکیشن و برنامه ها ' ],
                    'size' => [ 'type' => 'text', 'title' => 'حجم برنامه', 'width' => 'w50', 'default' => '' ],
                    'appstore' => [ 'type' => 'link', 'title' => 'لینک اپ استور', 'width' => 'w50', 'default' => '' ],
                    'download' => [ 'type' => 'link', 'title' => 'لینک دانلود', 'width' => 'w50', 'default' => '' ],
                    'icon' => ['type' => 'file', 'format' => 'img', 'title' => 'ایکون', 'width' => 'w100', 'default' => '' ],
                ],
            ],
            'contact' => [
                'title' => 'تنظیمات برگه اصلی (contact)',
                'post_types' => ['page'],
                'page_templates' => ['page-contact.php'],
                'context' => 'normal',
                'priority' => 'high',
                'settings' => [
                    'contact_hero' => [ 'type' => 'heading', 'title' => 'بخش هیرو (محتوای صفحه تماس با ما)' ],
                    'contact_image' => [ 'type' => 'file', 'format' => 'img', 'title' => 'تصویر هیرو', 'width' => 'w100' ],
                    'contact_map' => [ 'type' => 'text', 'title'  => 'لینک مپ', 'width' => 'w100' ],
                ],
            ],

            'about' => [
                'title' => 'تنظیمات برگه اصلی (about)',
                'post_types' => ['page'],
                'page_templates' => ['page-about.php'],
                'context' => 'normal',
                'priority' => 'high',
                'settings' => [
                    'about_hero' => [ 'type' => 'heading', 'title' => 'محتوای صفحه درباره ما' ],
                    'about_image' => [ 'type' => 'file', 'format' => 'img', 'title' => 'تصویر هیرو', 'width' => 'w100' ],
                    'about_feather' => [
                        'type' => 'repeater',
                        'title' => 'ویژگی ها',
                        'btn' => 'افزودن ایتم',
                        'width' => 'w50',
                        'settings' => [
                            'about_feather[0][icon]' => [
                                'type' => 'text',
                                'title' => 'اسم ایکون'
                            ],
                            'about_feather[1][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان ایتم'
                            ],
                            'about_feather[2][text]' => [
                                'type' => 'text',
                                'title' => 'متن ایتم'
                            ],
                        ],
                    ],
                    'about_why' => [
                        'type' => 'repeater',
                        'title' => 'چرا ما',
                        'btn' => 'افزودن ایتم',
                        'width' => 'w50',
                        'settings' => [
                            'about_why[0][icon]' => [
                                'type' => 'text',
                                'title' => 'اسم ایکون'
                            ],
                            'about_why[1][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان ایتم'
                            ],
                            'about_why[2][text]' => [
                                'type' => 'text',
                                'title' => 'متن ایتم'
                            ],
                        ],
                    ],
                    'about_timeline' => [
                        'type' => 'repeater',
                        'title' => 'تایم لایت',
                        'btn' => 'افزودن تایم لاین',
                        'width' => 'w100',
                        'settings' => [
                            'about_timeline[0][icon]' => [
                                'type' => 'text',
                                'title' => 'اسم ایکون'
                            ],
                            'about_timeline[1][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان ایتم'
                            ],
                            'about_timeline[2][text]' => [
                                'type' => 'text',
                                'title' => 'متن ایتم'
                            ],
                            'about_timeline[3][feather1]' => [
                                'type' => 'text',
                                'title' => 'ویژگی 1'
                            ],
                            'about_timeline[4][feather2]' => [
                                'type' => 'text',
                                'title' => 'ویژگی 2'
                            ],
                            'about_timeline[5][feather3]' => [
                                'type' => 'text',
                                'title' => 'ویژگی 3'
                            ],
                            'about_timeline[5][tag1]' => [
                                'type' => 'text',
                                'title' => 'تگ 1'
                            ],
                            'about_timeline[6][tag2]' => [
                                'type' => 'text',
                                'title' => 'تگ 2'
                            ],
                        ],
                    ],
                ],

            ],

              'landing' => [
                'title' => 'تنظیمات برگه اصلی (landing)',
                'post_types' => ['page'],
                'page_templates' => ['page-landing.php'],
                'context' => 'normal',
                'priority' => 'high',
                'settings' => [
                    'landing_hero' => [ 'type' => 'heading', 'title' => 'محتوای صفحه درباره ما' ],
                    'landing_image' => [ 'type' => 'file', 'format' => 'img', 'title' => 'تصویر هیرو', 'width' => 'w100' ],
                    'landing_process' => [
                        'type' => 'repeater',
                        'title' => 'روند کار',
                        'btn' => 'افزودن ایتم',
                        'width' => 'w50',
                        'settings' => [
                            'landing_process[0][icon]' => [
                                'type' => 'text',
                                'title' => 'اسم ایکون'
                            ],
                            'landing_process[1][title]' => [
                                'type' => 'text',
                                'title' => 'عنوان ایتم'
                            ],
                        ],
                    ],
                  'landing_gallery_heading' => [ 
                        'type' => 'heading', 
                        'title' => 'گالری تصاویر' 
                    ],
                    'landing_gallery_images' => [ 
                        'type' => 'image-gallery', 
                        'title' => 'انتخاب تصاویر گالری', 
                        'width' => 'w100' 
                    ],
                   'landing_faq' => [
                        'type' => 'repeater',
                        'title' => 'سوالات متداوم',
                        'btn' => 'افزودن ایتم',
                        'width' => 'w50',
                        'settings' => [
                            'landing_faq[0][title]' => [
                                'type' => 'text',
                                'title' => 'تایتل'
                            ],
                            'landing_faq[1][text]' => [
                                'type' => 'text',
                                'title' => 'متن '
                            ],
                        ],
                    ],
                ],

            ],
        ];
        return $metaboxes;
    }

    public function add_custom_metaboxes($post_type) {
        $all_metaboxes = $this->All_Metaboxes();
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $current_template = get_post_meta($post_id, '_wp_page_template', true);

        foreach ($all_metaboxes as $id => $metabox) {
            $post_types = $metabox['post_types'];
            $templates = $metabox['page_templates'];
            if (!in_array($post_type, $post_types)) continue;
            if (!empty($templates) && !in_array($current_template, $templates)) continue;
            add_meta_box($id, $metabox['title'], array($this, 'render_metabox'), $post_types, $metabox['context'], $metabox['priority'], array('metabox_id' => $id));
        }
    }

    public function generate_field_html($field_id, $setting) {
        return $this->emdadcamera_Type_To_Function($field_id, $setting);
    }

    public function render_metabox($post, $metabox) {
        wp_nonce_field('theme_metabox_nonce', 'theme_metabox_nonce');
        $metabox_id = $metabox['args']['metabox_id'];
        $all_metaboxes = $this->All_Metaboxes();
        $settings = $all_metaboxes[$metabox_id]['settings'];
        
        echo '<input type="hidden" name="emdadcamera_metabox_active_' . $metabox_id . '" value="1">';
        
        echo "<style>
        .emdadcamera-switch {
          position: relative; display: inline-block; width: 50px; height: 26px; }
        .emdadcamera-switch input {
          opacity: 0; width: 0; height: 0; margin: 0; }
        .emdadcamera-slider {
          position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
        .emdadcamera-slider:before {
          position: absolute; content: ''; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; }
        .emdadcamera-slider.round {
          border-radius: 34px; }
        .emdadcamera-slider.round:before {
          border-radius: 50%; }
        .emdadcamera-switch input:checked + .emdadcamera-slider {
          background-color: #2196F3 !important; }
        .emdadcamera-switch input:focus + .emdadcamera-slider {
          box-shadow: 0 0 1px #2196F3 !important; }
        .emdadcamera-switch input:checked + .emdadcamera-slider:before {
          transform: translateX(24px); }
        .emdadcamera-editor-wrapper {
          border: 1px solid #ccc; margin-top: 5px; background: #fff; }
        </style>";

        $meta_values = get_post_meta($post->ID, '_' . $metabox_id, true);
        $meta_exists = metadata_exists('post', $post->ID, '_' . $metabox_id);
        if(!is_array($meta_values)) $meta_values = [];
        
        echo "<div class='emdadcamera-form-setting flex flex-wrap'>";
        foreach ($settings as $field_id => $setting) {
            $db_value = isset($meta_values[$field_id]) ? $meta_values[$field_id] : '';
            if ($meta_exists) {
                $setting['value'] = $db_value;
            } elseif (isset($setting['default'])) {
                 $setting['value'] = $setting['default'];
            } else {
                 $setting['value'] = '';
            }
            if($setting['type'] == 'repeater') {
                 echo $this->emdadcamera_Type_To_Function($field_id, $setting);
            } else {
                 echo $this->generate_field_html($field_id, $setting);
            }
        }
        echo "</div>";
    }

    public function save_metabox_data($post_id) {
        if (!isset($_POST['theme_metabox_nonce']) || !wp_verify_nonce($_POST['theme_metabox_nonce'], 'theme_metabox_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        $all_metaboxes = $this->All_Metaboxes();
        foreach ($all_metaboxes as $metabox_id => $metabox) {
            if (!isset($_POST['emdadcamera_metabox_active_' . $metabox_id])) continue;
            $meta_key = '_' . $metabox_id;
            $data = [];
            foreach ($metabox['settings'] as $field_id => $setting) {
                if ($setting['type'] === 'repeater') {
                    $data[$field_id] = isset($_POST[$field_id]) ? wp_unslash($_POST[$field_id]) : [];
                } else {
                    $data[$field_id] = isset($_POST[$field_id]) ? wp_unslash($_POST[$field_id]) : '';
                }
            }
            update_post_meta($post_id, $meta_key, $data);
        }
    }
}
new Main_Metaboxes();
?>