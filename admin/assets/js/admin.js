/*Select2 Sortable | 1.0.0 | Author: Vijay Hardaha | License : MIT*/
!function(a){a.fn.extend({select2Sortable:function(){var b=Array.prototype.slice.call(arguments,0),c=this.filter("[multiple]");if(0==c.length)this.select2(b[0]);else if(0===b.length||"object"==typeof b[0]){var d={sorter:a=>a.sort(function(c,a){return c.text.localeCompare(a.text)}),createTag:function(){}},e=a.extend([],d,b[0]);"object"!=typeof c.data("select2")&&c.select2(e),c.each(function(){var b=a(this),d=b.siblings(".select2-container").first("ul.select2-selection__rendered");c.select2SetOrderOnInit(b),d.sortable({placeholder:"ui-state-highlight",forcePlaceholderSize:!0,items:"li:not(.select2-search__field)",tolerance:"pointer"}),d.on("sortstop.select2sortable",function(){a(d.find(".select2-selection__choice").get().reverse()).each(function(){var c=a(this).attr("title"),d=b.find("option:contains("+c+")");b.prepend(d)})})})}else if(typeof("string"===b[0])){if(-1==a.inArray(b[0],["destroy"]))throw"Unknown method: "+b[0];"destroy"===b[0]&&c.select2SortableDestroy()}},select2SortableDestroy:function(){var b=this.filter("[multiple]");return b.each(function(){var b=a(this).siblings(".select2-container").first("ul.select2-selection__rendered");b.unbind("sortstop.select2sortable"),b.sortable("destroy")}),b},select2SetOrderOnInit:function(b){var c=b.attr("data-initials"),d=[];if("undefined"!=typeof c){var e=c.split(",");e.length&&(e=e.map(function(a){return a.trim()}),a.each(e,function(a,c){var e=b.find("option[value=\""+c+"\"]");d.push(e),e.remove()}))}d.length&&b.prepend(d)}})}(jQuery);

jQuery(document).ready(function($) {
$('#publish').on('click', function(e) {
        let isValid = true;
        let firstInvalidField = null;

        // بررسی تک تک آیتم‌های گردونه
        $('input[name^="wheel"][name$="[title]"]').each(function() {
            const $titleField = $(this);
            const titleValue = $titleField.val().trim();
            const rowName = $titleField.attr('name');
            
            // پیدا کردن فیلد صوتی (voice) مربوط به همین ردیف
            const voiceName = rowName.replace('[title]', '[voice]');
            const $voiceField = $('input[name="' + voiceName + '"]');
            const voiceValue = $voiceField.val().trim();

            // شرط خطا: عنوان پر باشد اما فایل صوتی خالی باشد
            if (titleValue !== '' && voiceValue === '') {
                isValid = false;
                
                // قرمز کردن کادر برای جلب توجه
                $voiceField.css({
                    'border': '2px solid #d63638',
                    'box-shadow': '0 0 5px rgba(214, 54, 56, 0.5)'
                });

                // ذخیره اولین فیلد خطا برای اسکرول (فقط اولین مورد را ذخیره می‌کنیم)
                if (firstInvalidField === null) {
                    firstInvalidField = $voiceField;
                }
            } else {
                // حذف استایل خطا اگر کاربر اصلاح کرده باشد
                $voiceField.css({
                    'border': '',
                    'box-shadow': ''
                });
            }
        });

        if (!isValid) {
            // جلوگیری از ذخیره شدن فرم
            e.preventDefault();

            // فعال کردن مجدد دکمه انتشار (چون وردپرس قفلش می‌کند)
            $('#publish').removeClass('disabled');
            $('.spinner').removeClass('is-active');

            // اسکرول آهسته به سمت اولین خطا
            if (firstInvalidField) {
                $('html, body').animate({
                    // عدد 200 برای فاصله از بالای صفحه است تا زیر نوار ابزار نرود
                    scrollTop: firstInvalidField.offset().top - 200 
                }, 2000, function() { 
                    // این تابع وقتی اجرا می‌شود که اسکرول تمام شده باشد
                    firstInvalidField.focus(); 
                });
            }

            alert('لطفاً فایل صوتی را برای آیتم‌های وارد شده انتخاب کنید.');
            return false;
        }
    });
    // ==========================================
    // 1. توابع عمومی راه‌اندازی (Init Functions)
    // ==========================================

    /**
     * این تابع تمام پلاگین‌ها (Select2, ColorPicker) را روی یک اسکوپ خاص فعال می‌کند.
     * @param {jQuery Object} scope - المانی که باید پلاگین‌ها درون آن فعال شوند (مثلا body یا یک ردیف جدید)
     */
    function init_metabox_fields(scope) {
        
        // --- Select2 ---
        if($.fn.select2) {
            scope.find('.emdadcamera-select').select2();
            scope.find('.select-multiple').select2({
                placeholder: 'انتخاب کنید',
                multiple: true,
                allowClear: true
            });
        }

        // --- Color Picker ---
        // تنظیمات دلخواه
        var colorOptions = {
            palettes: true,
            hide: true
        };

        // پیدا کردن اینپوت‌هایی که کلاس color-picker دارند
        scope.find('.color-picker').each(function(){
            // فقط اگر قبلاً فعال نشده است، آن را فعال کن
            if( ! $(this).hasClass('wp-color-picker') ){
                 $(this).wpColorPicker(colorOptions);
            }
        });
    }

    // فراخوانی اولیه روی کل صفحه
    init_metabox_fields($('body'));


    // ==========================================
    // 2. منطق آپلود فایل (File Uploader)
    // ==========================================

    function emdadcamera_file_uploader($wrapper) {
        var uploader;
        var addImgLink = $wrapper.find('.field-upload-file');
        var delImgLink = $wrapper.find('.field-delete-file');
        var imgContainer = $wrapper.find('.field-file-container');
        var imgURLInput = $wrapper.find('.field-file-url');

        // جلوگیری از بایندیگ چندباره
        addImgLink.off('click').on('click', function(event) {
            event.preventDefault();
            if (uploader) { uploader.open(); return; }
            uploader = wp.media({ multiple: false });
            uploader.on('select', function() {
                var attachment = uploader.state().get('selection').first().toJSON();
                imgContainer.empty().append('<img src="' + attachment.url + '" alt="" style="max-width:100%;"/>');
                imgURLInput.val(attachment.url);
                delImgLink.removeClass('hidden');
            });
            uploader.open();
        });

        delImgLink.off('click').on('click', function(event) {
            event.preventDefault();
            imgContainer.html('');
            delImgLink.addClass('hidden');
            imgURLInput.val('');
        });
    }

    // فعال‌سازی اولیه آپلودرها
    $('.field-file-uploader').each(function() {
        emdadcamera_file_uploader($(this));
    });


// ==========================================
    // 3. منطق Repeater (افزودن و حذف سطر)
    // ==========================================

    $(document).on('click', '.add-repeater-row', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var repeaterWrapper = button.closest('.field-repeater').find('.main-repeater');
        var lastItem = repeaterWrapper.find('.repeater-table').last();
        
        // 1. کلون کردن آخرین آیتم
        var clone = lastItem.clone();
        
        // 2. محاسبه ایندکس جدید
        // تعداد آیتم‌های موجود را می‌شمارد. مثلا اگر 2 تا هست، ایندکس جدید می‌شود 2 (چون از 0 شروع می‌شود)
        // اما برای نمایش به کاربر (متن دلخواه 3) باید +1 کنیم.
        var currentLength = repeaterWrapper.find('.repeater-table').length;
        var newIndex = currentLength; 
        var humanIndex = currentLength + 1; // شماره‌ای که کاربر می‌بیند
        
        // آپدیت کردن ID و Nameها در کلون
        clone.html(function(i, oldHTML) {
            return oldHTML.replace(/\[(\d+)\]/g, '[' + newIndex + ']');
        });

        // بروزرسانی ID کانتینر اصلی سطر
        var oldContainerId = lastItem.attr('id');
        if(oldContainerId) {
            clone.attr('id', oldContainerId.replace(/\[(\d+)\]/, '[' + newIndex + ']'));
        }

        // 3. پاکسازی مقادیر قبلی و مدیریت ادیتورها
        var editors = clone.find('.wp-editor-area');
        
        // پاکسازی فیلدهای معمولی قبل از هر چیز
        clone.find('input[type="text"], textarea, select').val('');
        clone.find('input[type="checkbox"]').prop('checked', false);
        clone.find('.field-file-container').empty(); 
        clone.find('.field-delete-file').hide();

        // -----------------------------------------------------------
        // --- تغییر جدید: نام‌گذاری خودکار (متن دلخواه 1، 2 و...) ---
        // -----------------------------------------------------------
        // ما دنبال اینپوتی می‌گردیم که در نامش کلمه [label] داشته باشد
        var $labelInput = clone.find('input[name*="[label]"]');
        
        if ($labelInput.length > 0) {
            // مقداردهی با نام + شماره جدید
            $labelInput.val('متن دلخواه ' + humanIndex);
        }
        // -----------------------------------------------------------


        // مدیریت ادیتور وردپرس (اگر وجود داشته باشد)
        if (editors.length > 0) {
            editors.each(function() {
                var $textarea = $(this);
                $textarea.val(''); // محتوای ادیتور هم پاک شود
                
                var fieldName = $textarea.attr('name');
                var newEditorId = fieldName.replace(/[\[\]]/g, '_'); 
                
                $textarea.attr('id', newEditorId);
                
                var $wrapper = $textarea.closest('.wp-editor-wrap');
                if ($wrapper.length > 0) {
                    $wrapper.replaceWith($textarea);
                    $textarea.show();
                }
            });
        }

        // اضافه کردن کلون به لیست
        repeaterWrapper.append(clone);

        // 4. راه‌اندازی مجدد ادیتورها برای آیتم جدید
        setTimeout(function() {
            clone.find('.wp-editor-area').each(function() {
                var editorId = $(this).attr('id');
                var settings = {};

                if (typeof tinyMCEPreInit !== 'undefined' && tinyMCEPreInit.mceInit['content']) {
                    settings = $.extend({}, tinyMCEPreInit.mceInit['content']);
                    settings.selector = '#' + editorId;
                    settings.body_class = settings.body_class.replace('content ', editorId + ' ');
                } else {
                    settings = {
                        selector: '#' + editorId,
                        theme: 'modern',
                        skin: 'lightgray',
                        wpautop: true
                    };
                }

                if (typeof tinymce !== 'undefined') {
                    tinymce.init(settings);
                }

                if (typeof quicktags !== 'undefined') {
                    var qtId = 'qt_' + editorId + '_toolbar';
                    if (QTags.instances[editorId]) {
                        delete QTags.instances[editorId];
                    }
                    quicktags({id: editorId});
                    QTags._buttonsInit();
                }
                
                if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                      var $wrap = $('#wp-' + editorId + '-wrap');
                      $wrap.find('.insert-media').on('click', function(){
                          wp.media.editor.open(editorId);
                      });
                }
            });
        }, 100);
        
        // فعال‌سازی مجدد Select2
        if($.fn.select2) {
            clone.find('select.select-multiple').each(function() {
               $(this).next('.select2-container').remove();
               $(this).select2({dir:'rtl', width:'100%'});
            });
        }
    });

// حذف سطر ریپیتر (نسخه نهایی و اصلاح شده)
    $(".main-repeater").on("click", ".delete-repeater-row", function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $container = $btn.closest(".main-repeater");
        
        if(confirm('آیا از حذف این مورد مطمئن هستید؟')) {
            $btn.closest('.repeater-table').slideUp(300, function() {
                $(this).remove();
                // به روز رسانی ایندکس‌ها بعد از حذف
                update_repeater_indexes($container);
            });
        }
    });
 
    // تابع مرتب‌سازی ایندکس‌ها (بعد از حذف یا درگ و دراپ)
    function update_repeater_indexes(container) {
        container.find(".repeater-table").each(function(index) {
            var newIndex = index;
            
            // آپدیت ID کانتینر
            var currentId = $(this).attr("id");
            if(currentId) {
                 $(this).attr("id", currentId.replace(/\[(\d+)\]/g, "[" + newIndex + "]"));
            }

            // آپدیت تمام اینپوت‌ها
            $(this).find("input, textarea, select").each(function() {
                var name = $(this).attr("name");
                var id = $(this).attr("id");
                var label = $(this).siblings("label");

                if (name) {
                    $(this).attr("name", name.replace(/\[(\d+)\]/g, "[" + newIndex + "]"));
                }
                if (id) {
                    var newId = id.replace(/\[(\d+)\]/g, "[" + newIndex + "]");
                    $(this).attr("id", newId);
                    if (label.length) {
                        label.attr("for", newId);
                    }
                }
            });
        });
    }

    // --- Sortable (Drag & Drop) ---
    if($.fn.sortable) {
        $(".main-repeater").sortable({
            items: '.repeater-table',
            cursor: 'move',
            handle: '.repeater-table-entry', // یا یک آیکون خاص برای هندل کردن
            opacity: 0.7,
            update: function(event, ui) {
               update_repeater_indexes($(this));
            }
        });
    }

    // ==========================================
    // 4. Multiple Image Uploader (گالری تصاویر)
    // ==========================================
    function emdadcamera_multiple_image_uploader($wrapper) {
        var uploader;
        var addImgLink = $wrapper.find('.field-upload-file');
        var imgContainer = $wrapper.find('.field-file-container');
        var imgIDsInput = $wrapper.find('.field-img-ids');

        addImgLink.on('click', function(event) {
            event.preventDefault();
            if (uploader) { uploader.open(); return; }
            uploader = wp.media({
                library: { type: 'image' },
                multiple: true,
            });
            uploader.on('select', function() {
                var attachments = uploader.state().get('selection').toJSON();
                imgContainer.empty();
                var ids = [];
                attachments.forEach(function(attachment) {
                    imgContainer.append('<div class="field-img-item" data-id="' + attachment.id + '"><img src="' + attachment.url + '" alt=""><button class="remove-image-button" data-id="' + attachment.id + '">×</button></div>');
                    ids.push(attachment.id);
                });
                imgIDsInput.val(ids.join(','));
            });
            uploader.open();
        });

        imgContainer.on('click', '.remove-image-button', function() {
            var idToRemove = $(this).data('id');
            var currentIds = imgIDsInput.val().split(',');
            var updatedIds = currentIds.filter(function(id) {
                return id !== idToRemove.toString();
            });
            imgIDsInput.val(updatedIds.join(','));
            $(this).parent().remove();
        });
    }

    $('.field-multiple-image-uploader').each(function() {
        var id = $(this).attr("id");
        emdadcamera_multiple_image_uploader($(this));
    });

    // --- Accordion Logic ---
    $('.emdadcamera-accordion-wrap').on('click', '.add_field', function() {
        var row = $(this).closest('.emdadcamera-accordion-wrap').find('p:first-child').clone();
        row.find('input[type="text"], textarea').val('');
        $(this).closest('.emdadcamera-accordion-wrap').append(row);
        return false;
    });

    $('.emdadcamera-accordion-wrap').on('click', '.remove_field', function() {
        $(this).parent().remove();
        return false;
    });

});