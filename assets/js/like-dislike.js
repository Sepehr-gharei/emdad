jQuery(document).ready(function ($) {

    // تابع تنظیم کوکی
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + 24 * days * 60 * 60 * 1000);
            expires = `; expires=${date.toUTCString()}`;
        }
        document.cookie = `${name}=${value || ""}${expires}; path=/`;
    }

    // تابع دریافت کوکی
    function getCookie(name) {
        const nameEQ = `${name}=`;
        const ca = document.cookie.split(";");
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === " ") c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // 1. بارگذاری وضعیت اولیه از کوکی در زمان لود صفحه
    $(".like-dislike").each(function () {
        const $box = $(this);
        const cookieKey = `ld_${$box.data("type")}_${$box.data("post-id")}`;
        const saved = getCookie(cookieKey);
        
        $box.find(".like-button, .dislike-button").removeClass("active");
        if (saved === "like") {
            $box.find(".like-button").addClass("active");
        } else if (saved === "dislike") {
            $box.find(".dislike-button").addClass("active");
        }
    });

    // 2. کلیک روی دکمه لایک / دیسلایک
    $(document).on("click", ".like-dislike button", function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $box = $btn.closest(".like-dislike");
        
        if ($btn.hasClass("processing")) return;
        $btn.addClass("processing");

        const action = $btn.data("action");
        const type = $box.data("type");
        const postId = $box.data("post-id");
        const cookieKey = `ld_${type}_${postId}`;
        const $numLike = $box.find(".num-like");
        const $numDislike = $box.find(".num-dislike");

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            dataType: "json",
            // نام اکشن مطابق با توابع PHP امدادکمرا
            data: { action: "emdadcamera_like_dislike", type: type, id: postId, do_action: action },
            success: (response) => {
                $btn.removeClass("processing");
                if (response.success) {
                    const result = response.data.set_cookie;
                    $box.find("button").removeClass("active");
                    
                    if (result === "like") {
                        $box.find(".like-button").addClass("active");
                        setCookie(cookieKey, "like", 365);
                    } else if (result === "dislike") {
                        $box.find(".dislike-button").addClass("active");
                        setCookie(cookieKey, "dislike", 365);
                    } else {
                        document.cookie = `${cookieKey}=; Max-Age=-99999999; path=/`;
                    }
                    
                    $numLike.text(response.data.new_like_count);
                    $numDislike.text(response.data.new_dislike_count);
                }
            },
            error: () => $btn.removeClass("processing")
        });
    });

    // 3. دریافت اعداد واقعی کامنت‌ها از سرور (جلوگیری از مشکلات کش)
    (function () {
        const items = [];
        $(".like-dislike").each(function () {
            const $box = $(this);
            // فقط آیتم‌ها را در صورت وجود اضافه کن
            if ($box.data("post-id")) {
                items.push({ id: $box.data("post-id"), type: $box.data("type") });
            }
        });
        
        if (items.length !== 0) {
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                dataType: "json",
                // نام اکشن مطابق با توابع PHP امدادکمرا
                data: { action: "emdadcamera_fetch_real_counts", items: items },
                success: (response) => {
                    if (response.success) {
                        const counts = response.data;
                        $(".like-dislike").each(function () {
                            const $box = $(this);
                            const key = `${$box.data("type")}_${$box.data("post-id")}`;
                            if (counts[key]) {
                                $box.find(".num-like").text(counts[key].likes);
                                $box.find(".num-dislike").text(counts[key].dislikes);
                            }
                        });
                    }
                }
            });
        }
    })();

});