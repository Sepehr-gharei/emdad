jQuery(function ($) {

    /* ======================================================
       متغیرهای مشترک
    ====================================================== */
    let identifier  = ''; // شماره موبایل یا ایمیل وارد‌شده
    let otpVia      = 'mobile'; // 'mobile' | 'email'
    let otpSubmitting = false;

    /* ======================================================
       ابزارها
    ====================================================== */
    function showMsg($el, msg, type) {
        $el.removeClass('success error').addClass(type).html(msg).show();
        if (type === 'success') {
            setTimeout(function () { $el.hide().text(''); }, 5000);
        }
    }

    function setLoading($btn, state) {
        $btn.toggleClass('loading', state).prop('disabled', state);
    }

    /* ======================================================
       رفتار چشم (نمایش/پنهان رمز) — مشترک
    ====================================================== */
    $(document).on('click', '.ec-eye-btn, .ec-staff-eye-btn, .af-otp-password-toggle', function () {
        var target = $(this).data('target') || '#ec-staff-password';
        var $input = $(target);
        if (!$input.length) return;
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('is-visible', type === 'text');
    });

    /* ======================================================
       جمع‌آوری OTP (کلاس af-otp-digit یا ec-otp-digit)
    ====================================================== */
    function collectOtp() {
        var code = '';
        $('.af-otp-digit, .ec-otp-digit').each(function () {
            code += ($(this).val() || '').replace(/\D/g, '').slice(0, 1);
        });
        $('#otp-code').val(code);
        return code;
    }

    function maybeAutoSubmit() {
        var code = collectOtp();
        if (otpSubmitting) return;
        if (otp_login_ajax.auto_verify && code.length === 4) {
            otpSubmitting = true;
            $('#ec-form-otp, #form-otp').trigger('submit');
        }
    }

    $(document).on('input', '.af-otp-digit, .ec-otp-digit', function () {
        var val = ($(this).val() || '').replace(/\D/g, '').slice(0, 1);
        $(this).val(val);
        collectOtp();
        if (val) $(this).next('.af-otp-digit, .ec-otp-digit').focus();
        maybeAutoSubmit();
    });

    $(document).on('keydown', '.af-otp-digit, .ec-otp-digit', function (e) {
        if (e.key === 'Backspace' && !$(this).val()) $(this).prev('.af-otp-digit, .ec-otp-digit').focus();
        if (e.key === 'ArrowLeft')  $(this).prev('.af-otp-digit, .ec-otp-digit').focus();
        if (e.key === 'ArrowRight') $(this).next('.af-otp-digit, .ec-otp-digit').focus();
    });

    $(document).on('paste', '.af-otp-digit, .ec-otp-digit', function (e) {
        e.preventDefault();
        var pasted = ((e.originalEvent || e).clipboardData.getData('text') || '')
            .replace(/\D/g, '').slice(0, 4).split('');
        $('.af-otp-digit, .ec-otp-digit').each(function (i) { $(this).val(pasted[i] || ''); });
        collectOtp();
        $('.af-otp-digit, .ec-otp-digit').eq(Math.min(pasted.length, 3)).focus();
        maybeAutoSubmit();
    });

    /* ======================================================
       تغییر مرحله — فرم مشتری
    ====================================================== */
    function showCustomerStep(id) {
        $('.ec-step').removeClass('active');
        $('#ec-step-' + id).addClass('active');
    }

    /* ======================================================
       فرم کارفرما — مرحله ۱: identifier (موبایل یا ایمیل)
    ====================================================== */
    $('#ec-form-identifier').on('submit', function (e) {
        e.preventDefault();
        identifier = $('#ec-identifier').val().trim();
        var $btn = $(this).find('.ec-btn-primary');
        var $msg = $('#ec-msg-identifier');

        if (!identifier) {
            showMsg($msg, 'لطفاً شماره موبایل یا ایمیل را وارد کنید.', 'error');
            return;
        }

        setLoading($btn, true);
        $msg.hide().text('');

        /* مرحله اول: بررسی نوع identifier */
        $.post(otp_login_ajax.ajax_url, {
            action:     'check_identifier',
            identifier: identifier,
            nonce:      otp_login_ajax.nonce
        }, function (res) {
            if (!res.success) {
                setLoading($btn, false);
                /* اگر ایمیل ثبت‌نشده بود، پیام ویژه نشان بده */
                var errMsg = (res.data && res.data.message) ? res.data.message : res.data;
                if (res.data && res.data.type === 'email_not_found') {
                    showMsg($msg,
                        '<span class="ec-alert-email">' + errMsg +
                        '<br><small>اگر هنوز ثبت‌نام نکرده‌اید، با شماره موبایل ثبت‌نام کنید.</small></span>',
                        'error');
                } else {
                    showMsg($msg, errMsg, 'error');
                }
                return;
            }

            /* موفق — ارسال OTP */
            otpVia = res.data.via; // 'mobile' | 'email'
            $('#ec-otp-via').val(otpVia);

            var sendAction = (otpVia === 'email') ? 'send_otp_email' : 'send_otp';
            var sendData   = (otpVia === 'email')
                ? { action: sendAction, email: identifier, nonce: otp_login_ajax.nonce }
                : { action: sendAction, mobile: identifier, nonce: otp_login_ajax.nonce };

            $.post(otp_login_ajax.ajax_url, sendData, function (otpRes) {
                setLoading($btn, false);
                if (otpRes.success) {
    if (otpVia === 'email') {
        $('#ec-otp-sub-text').text('کد تأیید به ایمیل ' + identifier + ' ارسال شد');
    } else {
        $('#ec-otp-sub-text').text('کد تأیید به شماره ' + identifier + ' ارسال شد');
    }
    
    // همیشه نشون بده و تایمر ۱۲۰ ثانیه رو استارت بزن
    $('#ec-resend-sms-wrap').show();
    startEmdadResendTimer(120); 
    
    showCustomerStep('otp');
    otpSubmitting = false;
    $('.ec-otp-digit, .af-otp-digit').val('');
    $('#otp-code').val('');
    $('.ec-otp-digit, .af-otp-digit').first().focus();
} else {
                    showMsg($msg, otpRes.data, 'error');
                }
            }).fail(function () {
                setLoading($btn, false);
                showMsg($msg, 'خطا در ارتباط با سرور', 'error');
            });

        }).fail(function () {
            setLoading($btn, false);
            showMsg($msg, 'خطا در ارتباط با سرور', 'error');
        });
    });

    /* ======================================================
       دریافت مجدد OTP از طریق پیامک
    ====================================================== */
    $('#ec-btn-resend-sms').on('click', function () {
        var $btn = $(this);
        var $msg = $('#ec-msg-otp');
        $btn.prop('disabled', true).text('در حال ارسال...');

        $.post(otp_login_ajax.ajax_url, {
            action:     'resend_otp_sms',
            identifier: identifier,
            nonce:      otp_login_ajax.nonce
        }, function (res) {
            $btn.prop('disabled', false).text('دریافت مجدد کد از طریق پیامک');
            if (res.success) {
    showMsg($msg, 'کد مجدداً ارسال شد.', 'success');
    startEmdadResendTimer(120); // استارت دوباره تایمر
} else {
    showMsg($msg, res.data, 'error');
}
        }).fail(function () {
            $btn.prop('disabled', false).text('دریافت مجدد کد از طریق پیامک');
            showMsg($msg, 'خطا در ارتباط با سرور', 'error');
        });
    });

    /* ======================================================
       فرم کارفرما — مرحله ۲: OTP
    ====================================================== */
    $('#ec-form-otp').on('submit', function (e) {
        e.preventDefault();
        var otp = collectOtp();
        var $msg = $('#ec-msg-otp');

        if (otp.length < 4) {
            otpSubmitting = false;
            showMsg($msg, 'کد ۴ رقمی را کامل وارد کنید', 'error');
            return;
        }

        var $btn = $(this).find('.ec-btn-primary');
        setLoading($btn, true);

        /* برای verify همیشه موبایل می‌فرستیم (حتی اگر با ایمیل OTP رفته، OTP روی موبایل ذخیره شده) */
        var mobileForVerify = identifier;
        if (otpVia === 'email') {
            /* باید با AJAX موبایل کاربر رو بگیریم — یا همان ایمیل می‌فرستیم و سمت سرور handle می‌شه */
            mobileForVerify = identifier; // سرور ایمیل رو هم accept می‌کنه
        }

        $.post(otp_login_ajax.ajax_url, {
            action: 'verify_otp',
            mobile: mobileForVerify,
            otp:    otp,
            nonce:  otp_login_ajax.nonce
        }, function (res) {
            setLoading($btn, false);
            otpSubmitting = false;
            if (res.success) {
                if (res.data.action === 'login') {
                    showMsg($msg, res.data.message, 'success');
                    setTimeout(function () { location.href = res.data.redirect; }, 1000);
                } else {
                    /* ثبت‌نام خودکار بدون نمایش فرم */
                    showCustomerStep('register');
                    var $regMsg = $('#ec-msg-register');
                    showMsg($regMsg, 'در حال ایجاد حساب کاربری...', 'info');
                    var mobileForAutoReg = (otpVia === 'mobile') ? identifier : '';
                    $.post(otp_login_ajax.ajax_url, {
                        action: 'register_user',
                        mobile: mobileForAutoReg,
                        nonce:  otp_login_ajax.nonce
                    }, function (regRes) {
                        if (regRes.success) {
                            showMsg($regMsg, regRes.data.message, 'success');
                            setTimeout(function () { location.href = regRes.data.redirect; }, 1200);
                        } else {
                            showMsg($regMsg, regRes.data, 'error');
                        }
                    }).fail(function () {
                        showMsg($regMsg, 'خطا در ارتباط با سرور', 'error');
                    });
                }
            } else {
                showMsg($msg, res.data, 'error');
            }
        }).fail(function () {
            setLoading($btn, false);
            otpSubmitting = false;
            showMsg($msg, 'خطا در ارتباط با سرور', 'error');
        });
    });

    /* دکمه برگشت به identifier */
    $('#ec-btn-back-identifier').on('click', function () {
        showCustomerStep('identifier');
    });

    /* ======================================================
       فرم کارفرما — مرحله ۳: ثبت‌نام
    ====================================================== */
    var usernameTimer;
    $('#ec-username').on('input', function () {
        clearTimeout(usernameTimer);
        var val = $(this).val();
        var $status = $('#ec-username-status');
        if (val.length < 3) { $status.text('').css('color', ''); return; }
        usernameTimer = setTimeout(function () {
            $.post(otp_login_ajax.ajax_url, {
                action: 'check_username',
                username: val,
                nonce: otp_login_ajax.nonce
            }, function (res) {
                $status.text(res.data).css('color', res.success ? '#16a34a' : '#dc2626');
            });
        }, 400);
    });

    $('#ec-form-register').on('submit', function (e) {
        e.preventDefault();
        var $btn = $(this).find('.ec-btn-primary');
        var $msg = $('#ec-msg-register');
        setLoading($btn, true);

        /* اگر با ایمیل بوده، باید موبایل واقعی رو بفرستیم ولی اینجا identifier موبایل است */
        var mobileForReg = (otpVia === 'mobile') ? identifier : '';

        $.post(otp_login_ajax.ajax_url, {
            action:   'register_user',
            mobile:   mobileForReg,
            name:     $('#ec-full-name').val(),
            username: $('#ec-username').val(),
            nonce:    otp_login_ajax.nonce
        }, function (res) {
            setLoading($btn, false);
            if (res.success) {
                showMsg($msg, res.data.message, 'success');
                setTimeout(function () { location.href = res.data.redirect; }, 1000);
            } else {
                showMsg($msg, res.data, 'error');
            }
        }).fail(function () {
            setLoading($btn, false);
            showMsg($msg, 'خطا در ارتباط با سرور', 'error');
        });
    });

    /* ======================================================
       فرم پرسنل — ورود با یوزرنیم/پسورد
    ====================================================== */
    $('#ec-form-staff').on('submit', function (e) {
        e.preventDefault();
        var $btn = $(this).find('.ec-staff-btn-primary');
        var $msg = $('#ec-msg-staff');
        setLoading($btn, true);

        $.post(otp_login_ajax.ajax_url, {
            action:   'staff_login',
            username: $('#ec-staff-username').val(),
            password: $('#ec-staff-password').val(),
            nonce:    otp_login_ajax.nonce
        }, function (res) {
            setLoading($btn, false);
            if (res.success) {
                showMsg($msg, res.data.message, 'success');
                setTimeout(function () { location.href = res.data.redirect; }, 900);
            } else {
                showMsg($msg, res.data, 'error');
                $('.ec-staff-card').addClass('ec-shake');
                setTimeout(function () { $('.ec-staff-card').removeClass('ec-shake'); }, 500);
            }
        }).fail(function () {
            setLoading($btn, false);
            showMsg($msg, 'خطا در ارتباط با سرور', 'error');
        });
    });

    /* ======================================================
       پشتیبانی از فرم‌های قدیمی (legacy)
    ====================================================== */
    $('#form-mobile').on('submit', function (e) {
        e.preventDefault();
        var mob = $('#mobile').val().trim();
        var $btn = $(this).find('.otp-btn');
        setLoading($btn, true);
        $.post(otp_login_ajax.ajax_url, { action: 'send_otp', mobile: mob, nonce: otp_login_ajax.nonce }, function (res) {
            setLoading($btn, false);
            if (res.success) {
                $('.otp-step').removeClass('active');
                $('#step-otp').addClass('active');
                otpSubmitting = false;
                $('.af-otp-digit').val('');
                $('#otp-code').val('');
                $('.af-otp-digit').first().focus();
            }
        });
    });

    $('#form-otp').on('submit', function (e) {
        e.preventDefault();
        var otp = collectOtp();
        if (otp.length < 4) { otpSubmitting = false; return; }
        var $btn = $(this).find('.otp-btn.primary, .otp-btn');
        setLoading($btn, true);
        $.post(otp_login_ajax.ajax_url, { action: 'verify_otp', mobile: identifier, otp: otp, nonce: otp_login_ajax.nonce }, function (res) {
            setLoading($btn, false);
            otpSubmitting = false;
            if (res.success) {
                if (res.data.action === 'login') {
                    setTimeout(function () { location.href = res.data.redirect; }, 1000);
                } else {
                    $('.otp-step').removeClass('active');
                    $('#step-register').addClass('active');
                }
            }
        });
    });
// این تابع را زمانی که درخواست AJAX ارسال کد با موفقیت انجام شد، فراخوانی کنید
function startEmdadResendTimer(durationInSeconds) {
    const button = document.getElementById('ec-btn-resend-sms');
    const display = document.getElementById('ec-timer-display');
    if (!button || !display) return;
    
    button.disabled = true;
    button.style.opacity = '0.5';
    button.style.cursor = 'not-allowed';

    const endTime = Date.now() + durationInSeconds * 1000;
    localStorage.setItem('emdad_otp_timeout', endTime);

    const timerInterval = setInterval(function () {
        const remainingTime = Math.round((endTime - Date.now()) / 1000);

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            button.disabled = false;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
            display.innerHTML = "";
            button.innerHTML = "دریافت مجدد کد";
            localStorage.removeItem('emdad_otp_timeout');
        } else {
            let minutes = parseInt(remainingTime / 60, 10);
            let seconds = parseInt(remainingTime % 60, 10);
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            
            display.innerHTML = "مانده تا دریافت مجدد: " + minutes + ":" + seconds;
            button.innerHTML = "لطفاً صبر کنید...";
        }
    }, 1000);
}

// چک کردن وضعیت تایمر هنگام رفرش صفحه
const savedEndTime = localStorage.getItem('emdad_otp_timeout');
if (savedEndTime) {
    const remaining = Math.round((savedEndTime - Date.now()) / 1000);
    if (remaining > 0) {
        // برای اینکه کادر ارسال مجدد دیده بشه
        $('#ec-resend-sms-wrap').show();
        startEmdadResendTimer(remaining);
    } else {
        localStorage.removeItem('emdad_otp_timeout');
    }
}
// بررسی وضعیت تایمر هنگام لود مجدد صفحه
document.addEventListener("DOMContentLoaded", function() {
    const savedEndTime = localStorage.getItem('emdad_otp_timeout');
    if (savedEndTime) {
         const remaining = Math.round((savedEndTime - Date.now()) / 1000);
         if (remaining > 0) {
             startEmdadResendTimer(remaining);
         } else {
             localStorage.removeItem('emdad_otp_timeout');
         }
    }
});
});
