<?php
if (!class_exists('Admin_Helper')) {
    class Admin_Helper {

        public function emdadcamera_Text($id, $title, $content, $width) {
            $field = "<div id='$id' class='emdadcamera-field $width field-text'>"
                . "<label for='$id'>$title</label>"
                . "<input type='text' value='". (!empty($content) ? esc_attr($content) : '') ."' name='$id' />"
            . "</div>";
            return $field;
        }

        public function emdadcamera_Textarea($id, $title, $content, $width) {
            $escaped_content = !empty($content) ? esc_attr($content) : '';
            $field = "<div id='$id' class='emdadcamera-field $width field-textarea'>"
                . "<label for='$id'>$title</label>"
                . "<textarea name='$id' rows='4' cols='50'>". $escaped_content ."</textarea>"
            . "</div>";
            return $field;
        }

        public function emdadcamera_URL($id, $title, $content, $width) {
            $field = "<div id='$id' class='emdadcamera-field $width field-url'>"
                . "<label for='$id'>$title</label>"
                . "<input type='text' value='". (!empty($content) ? esc_attr($content) : '') ."' name='$id' dir='ltr' />"
            . "</div>";
            return $field;
        }

        public function emdadcamera_File_Uploader($id, $title, $content, $width, $type) {
            $single_id = preg_replace('/[\[\]]/', '', $id);
            $field = "<div id='$single_id' class='emdadcamera-field $width field-file-uploader'>"
                . "<label for='$id'>$title</label>"
                . "<div class='flex align-items-center flex-wrap'>"
                    . "<button class='button field-upload-file' id='$single_id'>انتخاب فایل</button>"
                    . "<input value='". (!empty($content) ? esc_url($content) : '') ."' type='text' class='field-file-url regular-text' name='$id' dir='ltr' />"
                    . "<div class='field-file-container'>";
                    switch($type) {
                        case 'img' : $field .= (!empty($content) ? '<img src="'. esc_url($content) .'">' : ''); break;
                        case 'audio' : $field .= (!empty($content) ? '<audio controls=""><source src="'. esc_url($content) .'" type="audio/mpeg"></audio>' : ''); break;
                        case 'video' : $field .= (!empty($content) ? '<video width="320" height="240" controls><source src="'. esc_url($content) .'" type="video/mp4"></video>' : ''); break;
                    }
                    $display = empty($content) ? 'none' : 'inline-block';
                    $field .= "</div>"
                    . "<a class='field-delete-file' style='display:$display; color:#a00; margin-right:10px; cursor:pointer;' href='#'>حذف فایل</a>"
                . "</div>"
            . "</div>";
            return $field;
        }

        public function emdadcamera_Multiple_Image_Uploader($id, $title, $content, $width) {
            $content_array = !empty($content) ? explode(',', $content) : [];
            $field = "<div id='$id' class='emdadcamera-field $width field-multiple-image-uploader'>"
                . "<label for='$id'>$title</label>"
                . "<div class='flex align-items-center flex-wrap'>"
                    . "<button class='button field-upload-file' id='chooseLogo'>انتخاب تصاویر</button>"
                    . "<input value='". (!empty($content) ? implode(',', $content_array) : '') ."' type='text' class='field-img-ids' name='$id' />"
                    . "<div class='field-file-container'>";
                    if (!empty($content_array) && is_array($content_array)) {
                        foreach ($content_array as $image_id) {
                            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                            if ($image_url) {
                                $field .= "<div class='field-img-item' data-id='$image_id'><img src='$image_url' alt=''><button class='remove-image-button' data-id='$image_id'>×</button></div>";
                            }
                        }
                    }
                    $field .= "</div></div></div>";
            return $field;
        }

        public function emdadcamera_Checkbox($id, $title, $content, $width) {
            $checked = (!empty($content) && ($content == '1' || $content === 'on' || $content === true)) ? 'checked' : '';
            $field = "<div class='emdadcamera-field $width field-checkbox'>"
                . "<div style='display: flex; align-items: center; justify-content: space-between; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>"
                    . "<span style='font-weight:bold;'>$title</span>"
                    . "<label class='emdadcamera-switch' style='position: relative; display: inline-block; width: 50px; height: 26px; margin: 0;'>"
                        . "<input type='hidden' name='$id' value='0'>" 
                        . "<input type='checkbox' name='$id' value='1' $checked style='opacity: 0; width: 0; height: 0;'>"
                        . "<span class='emdadcamera-slider round' style='position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;'></span>"
                    . "</label>"
                . "</div>"
            . "</div>";
            return $field;
        }

        public function emdadcamera_Select($id, $title, $content, $selected, $width) {
            $field = "<div id='$id' class='emdadcamera-field $width field-select'>"
                . "<label for='$id'>$title</label>"
                . "<select class='emdadcamera-select select-single' name='$id' style='width:100%;'>";
                    foreach($content as $val_id => $value) {
                        $is_selected = ((string)$selected === (string)$val_id) ? ' selected' : '';
                        $field .= "<option value='$val_id' $is_selected>$value</option>";
                    }
                $field .= "</select></div>";
            return $field;
        }

        public function emdadcamera_Select2($id, $select_id, $title, $content, $selected, $width) {
            $field = "<div id='$id' class='emdadcamera-field $width field-select2'>"
                . "<label for='$id'>$title</label>"
                . "<select class='emdadcamera-select select-multiple' name='$select_id' multiple='multiple' style='width:100%;'>";
                    foreach($content as $val_id => $value) {
                        $is_selected = (!empty($selected) && is_array($selected) && in_array($val_id, $selected)) ? ' selected' : '';
                        $field .= "<option value='$val_id' $is_selected>$value</option>";
                    }
                $field .= "</select></div>";
            return $field;
        }

        public function emdadcamera_Color($id, $title, $value, $alpha, $default, $width) {
            $default = !empty($default) ? $default : '';
            $alpha_attr = $alpha == true ? " data-alpha-enabled='true'" : '';
            $field = "<div id='tmt-field-$id' class='tmt-field field-color $width emdadcamera-field'>"
                . "<div class='tmt-field-entry flex'>"
                    . "<label for='$id' style='display:block;margin-bottom:5px;'>$title</label>"
                    . "<input type='text' id='$id' class='color-picker' data-alpha-color-type='hex'$alpha_attr value='". (!empty($value) ? esc_attr($value) : $default) ."' name='$id' data-default-color='$default' />"
                . "</div></div>";
            return $field;
        }

        public function emdadcamera_Editor($id, $title, $content, $width) {
            $width = !empty($width) ? $width : 'w100';
            $content = html_entity_decode(stripslashes($content));
            $editor_id = str_replace(['[', ']'], '_', $id);

            $settings = array(
                'textarea_name' => $id, 
                'editor_class'  => $editor_id,
                'wpautop'       => true,
                'media_buttons' => true,
                'textarea_rows' => 8,
                'drag_drop_upload' => true,
                'teeny'         => false, 
                'quicktags'     => true,
                'tinymce'       => true,
            );
            
            ob_start();
            echo '<div class="emdadcamera-editor-wrapper ' . esc_attr($width) . '">';
                if($title) echo '<label for="'.$editor_id.'" style="display:block;margin-bottom:5px;font-weight:bold;">'.$title.'</label>';
                wp_editor($content, $editor_id, $settings);
            echo '</div>';
            return ob_get_clean();
        }

        public function emdadcamera_Heading($id, $title) {
            return "<h3 id='$id' class='emdadcamera-field w100 field-heading' style='background:#e5e5e5;padding:10px;margin:15px 0 5px;border-bottom:1px solid #ccc;'>$title</h3>";
        }

        public function emdadcamera_Type_To_Function($id, $setting) {
            $type = $setting['type'];
            $value = isset($setting['value']) ? $setting['value'] : '';
            $width = isset($setting['width']) ? $setting['width'] : 'w100';
            $title = $setting['title'];

            switch($type) {
                case 'repeater':
                    $btn = $setting['btn'];
                    $repeater_settings = $setting['settings'];
                    $section = isset($setting['section']) ? $setting['section'] : '';
                    return $this->emdadcamera_Repeater($id, $title, $section, $btn, $repeater_settings, $value, $width);
                case 'heading': return $this->emdadcamera_Heading($id, $title);
                case 'textarea': return $this->emdadcamera_Textarea($id, $title, $value, $width);
                case 'editor': return $this->emdadcamera_Editor($id, $title, $value, $width);
                case 'text': return $this->emdadcamera_Text($id, $title, $value, $width);
                case 'checkbox': return $this->emdadcamera_Checkbox($id, $title, $value, $width);
                case 'url': return $this->emdadcamera_URL($id, $title, $value, $width);
                case 'color': 
                    $alpha = isset($setting['alpha']) ? $setting['alpha'] : false;
                    $default = isset($setting['default']) ? $setting['default'] : '';
                    return $this->emdadcamera_Color($id, $title, $value, $alpha, $default, $width);
                case 'select': return $this->emdadcamera_Select($id, $title, isset($setting['array']) ? $setting['array'] : [], $value, $width);
                case 'select2': 
                    $select_id = !empty($setting['id']) ? $setting['id'] : $id . '[]';
                    if (strpos($select_id, '[]') === false) $select_id .= '[]';
                    return $this->emdadcamera_Select2($id, $select_id, $title, isset($setting['array']) ? $setting['array'] : [], $value, $width);
                case 'file': 
                    $format = isset($setting['format']) ? $setting['format'] : 'img';
                    return $this->emdadcamera_File_Uploader($id, $title, $value, $width, $format);
                case 'image-gallery': 
                    $ids = isset($setting['ids']) ? $setting['ids'] : $value;
                    return $this->emdadcamera_Multiple_Image_Uploader($id, $title, $ids, $width);
                default: 
                    return "<div class='emdadcamera-field $width'><label>$title</label><input type='text' name='$id' value='$value'></div>";
            }
        }

        public function emdadcamera_Repeater($id, $title, $section, $btn, $settings, $passed_value, $width) {
            $field = '';
            $repeater_value = [];

            if ($section) {
                $theme_options = get_option('emdadcamera_main_option', []);
                $get_option = isset($theme_options[$section]) ? $theme_options[$section] : [];
                $repeater_value = isset($get_option[$id]) ? $get_option[$id] : [];
            } else {
                $repeater_value = $passed_value;
            }

            if (!is_array($repeater_value)) { $repeater_value = []; }

            $field .= "<div id='$id' class='emdadcamera-field field-repeater'>"
                . "<label for='$id'>$title</label>"
                . "<div class='main-repeater flex flex-wrap'>";
                
                if (!empty($repeater_value)) {
                    $i = -1;
                    foreach ($repeater_value as $row_data) {
                        $i++;
                        $field .= "<div id='". $id . "[" . $i . "]' class='repeater-table $width'>"
                            . "<div class='repeater-table-entry'>"
                            . "<button type='button' class='button delete-repeater-row'>حذف</button>";
                            
                            foreach ($settings as $key => $sub_setting) {
                                $parts = explode('[', $key);
                                $lastPart = end($parts);
                                $sub_id = rtrim($lastPart, ']');
                                
                                $unique_name = $id . '[' . $i . '][' . $sub_id . ']';
                                $sub_setting['value'] = isset($row_data[$sub_id]) ? $row_data[$sub_id] : '';
                                
                                $field .= $this->emdadcamera_Type_To_Function_Internal($unique_name, $sub_setting);
                            }
                            $field .= "</div></div>";
                    }
                } else {
                     $field .= "<div id='". $id . "[0]' class='repeater-table $width'>"
                        . "<div class='repeater-table-entry'>";
                        foreach ($settings as $sub_id => $setting) {
                             $setting['value'] = '';
                             $field .= $this->emdadcamera_Type_To_Function_Internal($sub_id, $setting);
                        }
                        $field .= "</div>"
                    . "</div>";
                }
                
                $field .= "<button type='button' class='button w100 button-primary add-repeater-row' data-id='$id'>$btn</button>"
                . "</div></div>";
            
            return $field;
        }
        
        public function emdadcamera_Type_To_Function_Internal($id, $setting) {
            return $this->emdadcamera_Type_To_Function($id, $setting);
        }
    }
}
?>